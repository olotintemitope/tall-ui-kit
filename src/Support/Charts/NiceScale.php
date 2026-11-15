<?php

namespace TallUiKit\Support\Charts;

/**
 * A value axis with "nice" ticks (0, 5, 10 rather than 0, 3.3, 6.6), as Chart.js drew
 * it: from zero (or the lowest value when it is negative), at most four ticks, whole
 * numbers unless the axis is a percentage, and never flatter than `suggestedMax`, so
 * an all-zero period still has a 0 to 1 axis.
 */
final class NiceScale
{
    /**
     * @param  list<int|float>  $values
     * @return array{min: float, max: float, step: float, ticks: list<float>}
     */
    public static function for(array $values, int $maxTicks = 4, float $suggestedMax = 1.0, int $precision = 0): array
    {
        $low = min(0.0, ...array_map('floatval', $values ?: [0]));
        $high = max($suggestedMax, ...array_map('floatval', $values ?: [0]));

        $step = self::niceStep(($high - $low) / max(1, $maxTicks - 1), 10 ** -$precision);
        $min = floor(round($low / $step, 9)) * $step;
        $max = ceil(round($high / $step, 9)) * $step;

        // A rounded-up step can leave one tick too many; widen it until the axis fits.
        while (round(($max - $min) / $step) + 1 > $maxTicks) {
            $step = self::niceStep($step * 1.0001, 10 ** -$precision);
            $min = floor(round($low / $step, 9)) * $step;
            $max = ceil(round($high / $step, 9)) * $step;
        }

        $ticks = [];
        for ($i = 0, $count = (int) round(($max - $min) / $step); $i <= $count; $i++) {
            $ticks[] = round($min + $i * $step, $precision + 2);
        }

        return ['min' => (float) $min, 'max' => (float) $max, 'step' => (float) $step, 'ticks' => $ticks];
    }

    /**
     * The smallest of 1, 2 or 5 times a power of ten that is at least `$raw`, and never
     * below the axis precision (whole numbers for counts and money).
     */
    public static function niceStep(float $raw, float $minimum = 1.0): float
    {
        if ($raw <= $minimum) {
            return $minimum;
        }

        $magnitude = 10 ** floor(log10($raw));
        foreach ([1, 2, 5, 10] as $factor) {
            if ($factor * $magnitude >= $raw - 1e-12) {
                return round($factor * $magnitude, 12);
            }
        }

        return 10 * $magnitude;
    }
}
