<?php

namespace TallUiKit\Support\DataTable;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * One filter of a WithDataTable table (docs/ui-kit-tables.md).
 *
 *   Filter::select('type', 'Type')->options(['funding' => 'Funding'])->column('type')
 *   Filter::status('status', 'Status')->options(['completed' => ['label' => 'Completed', 'variant' => 'success']])
 *   Filter::dateRange('date', 'Date')->column('created_at')
 *
 * Types:
 *  - select: one value from `options`, or none.
 *  - multi: any subset of `options` (whereIn).
 *  - status: a multi-select whose options carry a badge `variant`.
 *  - boolean: '1' or '0' (Any, Yes, No).
 *  - date-range: ['from' => 'Y-m-d', 'to' => 'Y-m-d'], either end optional, inclusive, in
 *    the app timezone (Africa/Lagos).
 *  - number-range: ['min' => n, 'max' => n], either end optional, inclusive.
 *
 * Every incoming value goes through normalize(): anything that is not an allowed
 * option, a real date or a finite number is dropped, so a hand-edited URL can never
 * reach the query. column() may be a dot path (`campaign.type`), applied through
 * whereHas; using() replaces the query entirely.
 */
final class Filter
{
    public const TYPES = ['select', 'multi', 'status', 'boolean', 'date-range', 'number-range'];

    public const BADGE_VARIANTS = ['neutral', 'success', 'warning', 'danger', 'info', 'accent'];

    /** @var list<array{value: string, label: string, variant: string|null}> */
    public array $options = [];

    public ?string $column = null;

    public ?Closure $using = null;

    public mixed $default = null;

    public ?string $ability = null;

    public ?string $placeholder = null;

    public ?float $min = null;

    public ?float $max = null;

    private function __construct(public readonly string $key, public readonly string $label, public readonly string $type) {}

    public static function make(string $key, string $label, string $type): self
    {
        if (! preg_match('/^[a-z][a-z0-9_]*$/', $key)) {
            throw new InvalidTableDefinition("Filter key [{$key}] must be lower snake case (a-z, 0-9, _), it is used in the URL.");
        }
        if (! in_array($type, self::TYPES, true)) {
            throw new InvalidTableDefinition("Filter [{$key}] has an unknown type [{$type}]. Use one of ".implode(', ', self::TYPES).'.');
        }

        return new self($key, $label, $type);
    }

    public static function select(string $key, string $label): self
    {
        return self::make($key, $label, 'select');
    }

    public static function multi(string $key, string $label): self
    {
        return self::make($key, $label, 'multi');
    }

    public static function status(string $key, string $label): self
    {
        return self::make($key, $label, 'status');
    }

    public static function boolean(string $key, string $label): self
    {
        return self::make($key, $label, 'boolean')->options(['1' => 'Yes', '0' => 'No']);
    }

    public static function dateRange(string $key, string $label): self
    {
        return self::make($key, $label, 'date-range');
    }

    public static function numberRange(string $key, string $label): self
    {
        return self::make($key, $label, 'number-range');
    }

    /** @param  array<string|int, string|array{label: string, variant?: string}>  $options */
    public function options(array $options): self
    {
        $this->options = [];
        foreach ($options as $value => $option) {
            $option = is_array($option) ? $option : ['label' => (string) $option];
            $variant = $option['variant'] ?? null;
            if ($variant !== null && ! in_array($variant, self::BADGE_VARIANTS, true)) {
                throw new InvalidTableDefinition("Filter [{$this->key}] option [{$value}] has an unknown badge variant [{$variant}].");
            }
            $this->options[] = ['value' => (string) $value, 'label' => (string) $option['label'], 'variant' => $variant];
        }

        return $this;
    }

    public function column(string $column): self
    {
        $this->column = $column;

        return $this;
    }

    /** @param  Closure(Builder, mixed): void  $apply */
    public function using(Closure $apply): self
    {
        $this->using = $apply;

        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;

        return $this;
    }

    public function ability(?string $capability): self
    {
        $this->ability = $capability;

        return $this;
    }

    public function placeholder(string $placeholder): self
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function between(?float $min, ?float $max): self
    {
        $this->min = $min;
        $this->max = $max;

        return $this;
    }

    public function isRange(): bool
    {
        return in_array($this->type, ['date-range', 'number-range'], true);
    }

    public function isMulti(): bool
    {
        return in_array($this->type, ['multi', 'status'], true);
    }

    /** The validated default, or null. */
    public function defaultValue(): mixed
    {
        return $this->default === null ? null : $this->normalize($this->default);
    }

    /**
     * The value to apply, or null when the input is empty or invalid.
     * Never throws: invalid input is ignored, like an unknown filter.
     */
    public function normalize(mixed $raw): mixed
    {
        $allowed = array_column($this->options, 'value');

        return match ($this->type) {
            'select' => is_scalar($raw) && in_array((string) $raw, $allowed, true) ? (string) $raw : null,
            'boolean' => match (true) {
                $raw === true, $raw === 1, $raw === '1', $raw === 'yes', $raw === 'true' => '1',
                $raw === false, $raw === 0, $raw === '0', $raw === 'no', $raw === 'false' => '0',
                default => null,
            },
            'multi', 'status' => (function () use ($raw, $allowed): ?array {
                $values = is_array($raw) ? $raw : (is_string($raw) && $raw !== '' ? explode(',', $raw) : []);
                $values = array_map('strval', array_filter($values, 'is_scalar'));
                // Keep the definition's order so the URL and tokens are stable.
                $picked = array_values(array_filter($allowed, fn (string $value): bool => in_array($value, $values, true)));

                return $picked === [] ? null : $picked;
            })(),
            'date-range' => $this->normalizeRange($raw, 'from', 'to', function (mixed $value): ?string {
                if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                    return null;
                }
                [$y, $m, $d] = array_map('intval', explode('-', $value));

                return checkdate($m, $d, $y) ? $value : null;
            }),
            'number-range' => $this->normalizeRange($raw, 'min', 'max', function (mixed $value): int|float|null {
                if (! is_numeric($value) || ! is_finite((float) $value)) {
                    return null;
                }
                $number = $value + 0;
                if (($this->min !== null && $number < $this->min) || ($this->max !== null && $number > $this->max)) {
                    return null;
                }

                return $number;
            }),
        };
    }

    /** @param  Closure(mixed): mixed  $clean */
    private function normalizeRange(mixed $raw, string $low, string $high, Closure $clean): ?array
    {
        if (! is_array($raw)) {
            return null;
        }
        $range = array_filter([
            $low => isset($raw[$low]) && $raw[$low] !== '' ? $clean($raw[$low]) : null,
            $high => isset($raw[$high]) && $raw[$high] !== '' ? $clean($raw[$high]) : null,
        ], fn ($value): bool => $value !== null);

        if (isset($range[$low], $range[$high]) && $range[$low] > $range[$high]) {
            return null;
        }

        return $range === [] ? null : $range;
    }

    /** Applies a normalized value to the query. */
    public function apply(Builder $query, mixed $value): void
    {
        if ($this->using) {
            ($this->using)($query, $value);

            return;
        }

        $path = $this->column ?? $this->key;
        $relation = str_contains($path, '.') ? substr($path, 0, strrpos($path, '.')) : null;
        $column = $relation ? substr($path, strrpos($path, '.') + 1) : $path;

        $constrain = function (Builder $q) use ($column, $value): void {
            $qualified = $q->qualifyColumn($column);
            $zone = config('app.timezone');

            match ($this->type) {
                'select' => $q->where($qualified, $value),
                'multi', 'status' => $q->whereIn($qualified, $value),
                'boolean' => $q->where($qualified, $value === '1'),
                'date-range' => $q
                    ->when($value['from'] ?? null, fn (Builder $q, string $from) => $q->where($qualified, '>=', Carbon::parse($from, $zone)->startOfDay()))
                    ->when($value['to'] ?? null, fn (Builder $q, string $to) => $q->where($qualified, '<=', Carbon::parse($to, $zone)->endOfDay())),
                'number-range' => $q
                    ->when(array_key_exists('min', $value), fn (Builder $q) => $q->where($qualified, '>=', $value['min']))
                    ->when(array_key_exists('max', $value), fn (Builder $q) => $q->where($qualified, '<=', $value['max'])),
            };
        };

        $relation ? $query->whereHas($relation, $constrain) : $constrain($query);
    }

    public function optionLabel(string $value): string
    {
        foreach ($this->options as $option) {
            if ($option['value'] === $value) {
                return $option['label'];
            }
        }

        return $value;
    }

    /**
     * Removable tokens for an active value: one per chosen option for multi and
     * status filters (removing one keeps the rest), one for every other type.
     *
     * @return list<array{label: string, value: string|null}>
     */
    public function tokens(mixed $value): array
    {
        $date = fn (string $day): string => Carbon::parse($day)->format('j M Y');
        $number = fn (int|float $n): string => number_format($n, is_float($n) && floor($n) !== $n ? 2 : 0);

        return match ($this->type) {
            'multi', 'status' => array_map(fn (string $v): array => ['label' => "{$this->label}: {$this->optionLabel($v)}", 'value' => $v], $value),
            'select', 'boolean' => [['label' => "{$this->label}: {$this->optionLabel($value)}", 'value' => null]],
            'date-range' => [['label' => $this->label.': '.match (true) {
                isset($value['from'], $value['to']) => $date($value['from']).' to '.$date($value['to']),
                isset($value['from']) => 'from '.$date($value['from']),
                default => 'until '.$date($value['to']),
            }, 'value' => null]],
            'number-range' => [['label' => $this->label.': '.match (true) {
                isset($value['min'], $value['max']) => $number($value['min']).' to '.$number($value['max']),
                isset($value['min']) => 'at least '.$number($value['min']),
                default => 'at most '.$number($value['max']),
            }, 'value' => null]],
        };
    }

    /** @return array<string, string> */
    public function describe(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'options' => implode(', ', array_map(fn (array $o): string => $o['value'].($o['variant'] ? " ({$o['variant']})" : ''), $this->options)),
            'applies' => $this->using ? 'closure' : ($this->column ?? $this->key),
            'default' => $this->default === null ? '' : json_encode($this->default),
            'ability' => $this->ability ?? '',
        ];
    }
}
