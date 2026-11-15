<?php

namespace TallUiKit\Support;

use Illuminate\Support\Carbon;

/**
 * Number display rules for the UI kit (x-ui.number and plain Blade). Money
 * delegates to TallUiKit\Support\Money so it always agrees with x-ui.money.
 */
class Format
{
    /**
     * Placeholder for a missing figure: an en dash, as the kit's tables use.
     */
    public const EMPTY = "\u{2013}";

    private const UNITS = ['', 'k', 'M', 'B', 'T'];

    /**
     * Grouped thousands: 1204 is "1,204", 1204.5 with one decimal is "1,204.5".
     */
    public static function number(int|float|string|null $value, int $decimals = 0): string
    {
        if (! is_numeric($value)) {
            return self::EMPTY;
        }

        return self::sign((float) $value).number_format(abs((float) $value), $decimals);
    }

    /**
     * Compact counts: 950, 1.2k, 12k, 3.4M, 2.1B. A trailing ".0" is dropped and
     * a value that rounds up to the next unit moves to it (999,950 is "1M").
     */
    public static function compact(int|float|string|null $value, int $precision = 1): string
    {
        if (! is_numeric($value)) {
            return self::EMPTY;
        }

        $value = (float) $value;
        $abs = abs($value);

        if ($abs < 1000) {
            return self::sign($value).self::trim(number_format($abs, $abs == floor($abs) ? 0 : $precision, '.', ''));
        }

        $tier = min((int) floor(log10($abs) / 3), count(self::UNITS) - 1);
        $scaled = round($abs / 1000 ** $tier, $precision);

        if ($scaled >= 1000 && $tier < count(self::UNITS) - 1) {
            $tier++;
            $scaled = round($abs / 1000 ** $tier, $precision);
        }

        return self::sign($value).self::trim(number_format($scaled, $precision, '.', '')).self::UNITS[$tier];
    }

    /**
     * A ratio as a percentage: 0.1234 is "12.3%", 0.5 is "50%", 1 is "100%".
     */
    public static function percent(int|float|string|null $ratio, int $precision = 1): string
    {
        if (! is_numeric($ratio)) {
            return self::EMPTY;
        }

        $percent = round((float) $ratio * 100, $precision);

        return self::sign($percent).self::trim(number_format(abs($percent), $precision)).'%';
    }

    /**
     * NGN or USD exactly as x-ui.money prints it (TallUiKit\Support\Money).
     */
    public static function money(int|float|string|null $value, string $currency = 'NGN'): string
    {
        return Money::format($value, $currency);
    }

    /**
     * A minus sign (U+2212) for negatives, matching x-ui.delta.
     */
    private static function sign(float $value): string
    {
        return $value < 0 ? "\u{2212}" : '';
    }

    private static function trim(string $formatted): string
    {
        return str_contains($formatted, '.') ? rtrim(rtrim($formatted, '0'), '.') : $formatted;
    }

    /**
     * A short relative time for x-ui.time: "Just now", "5m ago", "2h ago", "Yesterday",
     * "3 days ago", "in 2h", "Tomorrow", "in 4 days"; past 30 days the date ("14 Nov 2026").
     */
    public static function relative(\DateTimeInterface $at, ?\DateTimeInterface $now = null): string
    {
        $at = Carbon::instance($at);
        $now = $now ? Carbon::instance($now)->setTimezone($at->getTimezone()) : Carbon::now($at->getTimezone());
        $seconds = $now->getTimestamp() - $at->getTimestamp();
        $future = $seconds < 0;
        $abs = abs($seconds);
        $days = (int) $at->copy()->startOfDay()->diffInDays($now->copy()->startOfDay(), true);

        return match (true) {
            $abs < 60 => 'Just now',
            $abs < 3600 => $future ? 'in '.intdiv($abs, 60).'m' : intdiv($abs, 60).'m ago',
            $abs < 86400 && $days === 0 => $future ? 'in '.intdiv($abs, 3600).'h' : intdiv($abs, 3600).'h ago',
            $days === 1 => $future ? 'Tomorrow' : 'Yesterday',
            $days <= 30 => $future ? "in {$days} days" : "{$days} days ago",
            default => $at->format('j M Y'),
        };
    }
}
