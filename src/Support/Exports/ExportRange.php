<?php

namespace TallUiKit\Support\Exports;

use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * A date range chosen in the export menu, resolved to Lagos day bounds.
 * Presets: this_month, last_month, last_90_days, custom (from and to as
 * Y-m-d). Anything else is rejected.
 */
final class ExportRange
{
    public const PRESETS = [
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'last_90_days' => 'Last 90 days',
        'custom' => 'Custom',
    ];

    private function __construct(
        public readonly string $preset,
        public readonly Carbon $from,
        public readonly Carbon $to,
    ) {}

    /**
     * @param  array{preset?: mixed, from?: mixed, to?: mixed}|null  $input
     */
    public static function fromInput(?array $input): ?self
    {
        if ($input === null || ($input['preset'] ?? null) === null || $input['preset'] === '' || $input['preset'] === 'all') {
            return null;
        }

        $preset = $input['preset'];
        if (! is_string($preset) || ! array_key_exists($preset, self::PRESETS)) {
            throw ValidationException::withMessages(['range' => 'Choose a date range from the list.']);
        }

        $zone = config('tall-ui-kit.exports.timezone') ?? config('app.timezone');
        $now = Carbon::now($zone);

        if ($preset !== 'custom') {
            return match ($preset) {
                'this_month' => new self($preset, $now->copy()->startOfMonth(), $now->copy()->endOfDay()),
                'last_month' => new self($preset, $now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()),
                'last_90_days' => new self($preset, $now->copy()->subDays(89)->startOfDay(), $now->copy()->endOfDay()),
            };
        }

        $from = self::day($input['from'] ?? null, $zone);
        $to = self::day($input['to'] ?? null, $zone);

        if ($from === null || $to === null) {
            throw ValidationException::withMessages(['range' => 'Enter a start and end date.']);
        }

        if ($from->greaterThan($to)) {
            throw ValidationException::withMessages(['range' => 'The start date must be on or before the end date.']);
        }

        return new self($preset, $from->startOfDay(), $to->endOfDay());
    }

    public function label(): string
    {
        return $this->from->format('j M Y').' to '.$this->to->format('j M Y');
    }

    /**
     * @return array{preset: string, from: string, to: string}
     */
    public function toInput(): array
    {
        return ['preset' => 'custom', 'from' => $this->from->toDateString(), 'to' => $this->to->toDateString()];
    }

    private static function day(mixed $value, string $zone): ?Carbon
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        $date = Carbon::createFromFormat('!Y-m-d', $value, $zone);

        return $date && $date->toDateString() === $value ? $date : null;
    }
}
