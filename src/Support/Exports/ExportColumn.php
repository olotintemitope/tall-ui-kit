<?php

namespace TallUiKit\Support\Exports;

use Closure;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * One exportable column: what it is called, how a row becomes its value and
 * how that value is written to CSV and PDF.
 *
 * Types: text (default), number, money (a plain number; pair it with a
 * currency column), date (ISO 8601 in config('tall-ui-kit.exports.timezone') ?? config('app.timezone') in CSV,
 * "14 Nov 2026, 09:30" in PDF). `required` columns are always exported;
 * `defaultVisible` ones make up the default selection; `ability` restricts a
 * column to users with that capability (anyone else cannot request it);
 * `group` and `total` drive the PDF summary report.
 */
final class ExportColumn
{
    private ?Closure $formatter = null;

    public string $type = 'text';

    public ?string $description = null;

    public bool $required = false;

    public bool $defaultVisible = true;

    /** Relative PDF width, in em-ish units (a 14-character column is about 10). */
    public float $pdfWidth = 10;

    public ?string $ability = null;

    public bool $group = false;

    public bool $total = false;

    private function __construct(public readonly string $key, public readonly string $heading) {}

    public static function make(string $key, string $heading): self
    {
        return new self($key, $heading);
    }

    /**
     * @param  Closure(Model): mixed  $formatter
     */
    public function value(Closure $formatter): self
    {
        $this->formatter = $formatter;

        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function money(): self
    {
        $this->type = 'money';

        return $this;
    }

    public function number(): self
    {
        $this->type = 'number';

        return $this;
    }

    public function date(): self
    {
        $this->type = 'date';

        return $this;
    }

    public function required(bool $required = true): self
    {
        $this->required = $required;

        return $this;
    }

    public function hidden(): self
    {
        $this->defaultVisible = false;

        return $this;
    }

    public function pdfWidth(float $width): self
    {
        $this->pdfWidth = $width;

        return $this;
    }

    public function ability(?string $capability): self
    {
        $this->ability = $capability;

        return $this;
    }

    /** Break the PDF summary down by this column's values. */
    public function group(): self
    {
        $this->group = true;

        return $this;
    }

    /** Sum this column in the PDF summary. */
    public function total(): self
    {
        $this->total = true;

        return $this;
    }

    public function isNumeric(): bool
    {
        return in_array($this->type, ['money', 'number'], true);
    }

    /** The raw value for a row: the formatter's result, or the attribute named by the key. */
    public function resolve(Model $row): mixed
    {
        return $this->formatter ? ($this->formatter)($row) : $row->getAttribute($this->key);
    }

    /**
     * A CSV cell. Money and numbers stay plain numbers (no grouping, no
     * symbol) so spreadsheets read them as numbers; dates are ISO 8601.
     */
    public function csv(mixed $value): string|int|float
    {
        if ($value === null) {
            return '';
        }

        return match ($this->type) {
            'money', 'number' => is_int($value) || is_float($value) ? self::plainNumber($value) : self::guard((string) $value),
            'date' => self::guard(self::toDate($value)?->toIso8601String() ?? (string) $value),
            default => self::guard(is_bool($value) ? ($value ? 'Yes' : 'No') : (string) $value),
        };
    }

    /** A PDF cell: grouped numbers, readable dates. */
    public function display(mixed $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        return match ($this->type) {
            'money' => is_numeric($value) ? self::groupNumber((float) $value, is_float($value) ? 2 : 0) : (string) $value,
            'number' => is_numeric($value) ? self::groupNumber((float) $value, is_float($value) ? 2 : 0) : (string) $value,
            'date' => self::toDate($value)?->format('j M Y, H:i') ?? (string) $value,
            default => is_bool($value) ? ($value ? 'Yes' : 'No') : (string) $value,
        };
    }

    /**
     * Formula-injection guard (OWASP CSV injection): a text cell that starts
     * with =, +, -, @, a tab or a carriage return is prefixed with a single
     * quote so spreadsheet apps show it as text instead of running it.
     * Genuine numbers from money and number columns are not text and are
     * written as they are, so a negative amount stays a number.
     */
    public static function guard(string $cell): string
    {
        return $cell !== '' && in_array($cell[0], ['=', '+', '-', '@', "\t", "\r"], true) ? "'".$cell : $cell;
    }

    public static function toDate(mixed $value): ?Carbon
    {
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->setTimezone(config('tall-ui-kit.exports.timezone') ?? config('app.timezone'));
        }

        if (is_string($value) && $value !== '') {
            try {
                return Carbon::parse($value)->setTimezone(config('tall-ui-kit.exports.timezone') ?? config('app.timezone'));
            } catch (\Throwable) {
                return null;
            }
        }

        return null;
    }

    private static function plainNumber(int|float $value): int|string
    {
        if (is_int($value)) {
            return $value;
        }

        $formatted = number_format($value, 2, '.', '');

        return str_ends_with($formatted, '.00') ? (int) $value : $formatted;
    }

    private static function groupNumber(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', ',');
    }
}
