<?php

namespace TallUiKit\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;
use Throwable;

/**
 * Server side of the custom date and time pickers (x-ui.date-picker and the components
 * built on it). The browser owns parsing and the popup (resources/js/date-core.js and
 * datepicker.js); PHP only normalises the bound value into the format a native input
 * posted, prints the same label the script prints, so the first paint and every morph
 * show it, and works out the range presets.
 *
 * Value formats: date Y-m-d, time H:i, month Y-m, week o-\WW (2026-W47), datetime Y-m-d\TH:i.
 */
class DateInput
{
    public const MODES = ['date', 'datetime', 'time', 'month', 'week', 'range'];

    /** The presets x-ui.date-range knows by key. */
    public const PRESETS = [
        'today' => 'Today',
        'last7' => 'Last 7 days',
        'last30' => 'Last 30 days',
        'last90' => 'Last 90 days',
        'thisMonth' => 'This month',
        'lastMonth' => 'Last month',
        'thisYear' => 'This year',
        'custom' => 'Custom',
    ];

    /**
     * A bound value (Carbon, DateTimeInterface, a string in the mode's format or a longer
     * ISO string) in the mode's format, or '' when it is empty or not a value.
     */
    public static function normalize(string $mode, mixed $value): string
    {
        if ($value === null || $value === '' || is_array($value)) {
            return '';
        }

        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->format(self::format($mode));
        }

        $value = trim((string) $value);
        $patterns = [
            'date' => '/^(\d{4}-\d{2}-\d{2})/',
            'datetime' => '/^(\d{4}-\d{2}-\d{2})[T ](\d{2}:\d{2})/',
            'time' => '/^(\d{2}:\d{2})/',
            'month' => '/^(\d{4}-\d{2})/',
            'week' => '/^(\d{4}-W\d{2})$/',
        ];

        if (! preg_match($patterns[$mode] ?? $patterns['date'], $value, $m)) {
            return '';
        }

        $normal = $mode === 'datetime' ? $m[1].'T'.$m[2] : $m[1];

        return self::valid($mode, $normal) ? $normal : '';
    }

    /** True when the string is a real value in the mode's format (no 31 February, no week 53 in a 52-week year). */
    public static function valid(string $mode, string $value): bool
    {
        try {
            return match ($mode) {
                'time' => (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value),
                'month' => (bool) preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $value),
                'week' => (bool) preg_match('/^(\d{4})-W(\d{2})$/', $value, $w) && (int) $w[2] >= 1 && (int) $w[2] <= self::weeksInYear((int) $w[1]),
                'datetime' => self::valid('date', substr($value, 0, 10)) && strlen($value) === 16 && $value[10] === 'T' && self::valid('time', substr($value, 11)),
                default => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && checkdate((int) substr($value, 5, 2), (int) substr($value, 8, 2), (int) substr($value, 0, 4)),
            };
        } catch (Throwable) {
            return false;
        }
    }

    public static function weeksInYear(int $year): int
    {
        return (int) Carbon::create($year, 12, 28)->format('W');
    }

    /** The label the field shows: 15 Nov 2026, 18:05 (or 6:05 pm), November 2026, Week 47, 2026, 15 Nov 2026, 18:05. */
    public static function display(string $mode, string $value, int $hours = 24): string
    {
        if ($value === '' || ! self::valid($mode === 'range' ? 'date' : $mode, $value)) {
            return '';
        }

        $time = fn (string $t): string => $hours === 12 ? Carbon::createFromFormat('H:i', $t)->format('g:i a') : $t;

        return match ($mode) {
            'time' => $time($value),
            'month' => Carbon::createFromFormat('!Y-m', $value)->format('F Y'),
            'week' => 'Week '.(int) substr($value, 6).', '.(int) substr($value, 0, 4),
            'datetime' => Carbon::createFromFormat('!Y-m-d', substr($value, 0, 10))->format('j M Y').', '.$time(substr($value, 11)),
            default => Carbon::createFromFormat('!Y-m-d', $value)->format('j M Y'),
        };
    }

    /** "1 Nov 2026 – 15 Nov 2026", or the start alone. */
    public static function displayRange(string $start, string $end): string
    {
        $from = self::display('date', $start);
        $to = self::display('date', $end);

        return $from !== '' && $to !== '' ? $from.' – '.$to : $from;
    }

    /**
     * Range presets as [['label' => , 'start' => , 'end' => ], ...]. Accepts keys of
     * PRESETS (a list, or key => label to rename) and explicit ['label', 'start', 'end']
     * entries; `custom` has no dates (it moves focus to the calendar). Dates are in the
     * given timezone (the app's by default).
     *
     * @param  array<int|string, mixed>|bool|null  $presets
     * @return list<array{label: string, start: string, end: string}>
     */
    public static function presets(array|bool|null $presets, ?string $tz = null): array
    {
        if ($presets === false || $presets === null) {
            return [];
        }

        if ($presets === true) {
            $presets = ['last7', 'last30', 'thisMonth', 'custom'];
        }

        $today = Carbon::now($tz ?? config('app.timezone'))->startOfDay();
        $ranges = [
            'today' => [$today, $today],
            'last7' => [$today->copy()->subDays(6), $today],
            'last30' => [$today->copy()->subDays(29), $today],
            'last90' => [$today->copy()->subDays(89), $today],
            'thisMonth' => [$today->copy()->startOfMonth(), $today],
            'lastMonth' => [$today->copy()->subMonthNoOverflow()->startOfMonth(), $today->copy()->subMonthNoOverflow()->endOfMonth()],
            'thisYear' => [$today->copy()->startOfYear(), $today],
        ];

        $out = [];
        foreach ($presets as $key => $preset) {
            if (is_array($preset)) {
                $start = self::normalize('date', $preset['start'] ?? '');
                $end = self::normalize('date', $preset['end'] ?? '');
                $out[] = ['label' => (string) ($preset['label'] ?? ''), 'start' => $start, 'end' => $end];

                continue;
            }

            [$name, $label] = is_int($key) ? [(string) $preset, self::PRESETS[$preset] ?? null] : [(string) $key, (string) $preset];

            if (! array_key_exists($name, self::PRESETS) || $label === null) {
                continue;
            }

            [$start, $end] = $ranges[$name] ?? [null, null];
            $out[] = ['label' => $label, 'start' => $start?->format('Y-m-d') ?? '', 'end' => $end?->format('Y-m-d') ?? ''];
        }

        return $out;
    }

    private static function format(string $mode): string
    {
        return match ($mode) {
            'time' => 'H:i',
            'month' => 'Y-m',
            'week' => 'o-\WW',
            'datetime' => 'Y-m-d\TH:i',
            default => 'Y-m-d',
        };
    }
}
