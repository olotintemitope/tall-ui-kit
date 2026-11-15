<?php

namespace TallUiKit\Support\Charts;

/**
 * Category-axis label thinning that holds at any width without measuring in the
 * browser. Each label gets the narrowest plot width (one of BANDS) from which it
 * shows; strides are nested (s, 2s, 4s...) so a label that shows
 * at one width also shows at every wider one, and labels never overlap at the
 * narrowest width of a band. Widths are estimated from the label length at the caption
 * size (12px Inter), on the generous side.
 */
final class TickThinning
{
    /**
     * Plot widths (px) from which a thinned label can show: every 32px up to 512, then
     * wider steps. x-ui.chart maps each to a container query class on the axis row
     * (`@min-[320px]:block`); the classes are literal in the Blade view so Tailwind
     * generates them.
     */
    public const BANDS = [192, 224, 256, 288, 320, 352, 384, 416, 448, 480, 512, 576, 640, 704, 768, 896, 1024, 1280];

    /** The narrowest plot the always-visible labels are fitted to. */
    public const FLOOR = 160;

    public const GAP = 6.0;

    /** How far the first label may hang left of the plot, into the y-axis column (at least "0" plus its 6px padding). */
    public const HANG = 12;

    /**
     * Estimated rendered width of a label at the caption size (Inter 12px), from rough
     * per-character advances checked against Chromium ("17 Oct" 35px, "Audiomack"
     * 63.5px, "YouTube Music" 86.6px), plus 5% for safety.
     */
    public static function width(string $label): float
    {
        $width = 0.0;
        foreach (mb_str_split($label) as $char) {
            $width += match (true) {
                $char === ' ' => 3.4,
                str_contains('.,:;\'|!', $char) => 3.0,
                str_contains('iljtfrI', $char) => 3.8,
                str_contains('mwMW', $char) => 10.5,
                ctype_digit($char) => 6.9,
                ctype_upper($char) => 8.6,
                ctype_lower($char) => 6.6,
                default => 8.0,
            };
        }

        return $width * 1.05;
    }

    /**
     * Gap between two neighbouring positions on a plot `$width` px wide: points spread
     * edge to edge (line), or slots of equal width (bars).
     */
    public static function spacing(int $count, float $width, bool $slots): float
    {
        return $slots ? $width / max(1, $count) : $width / max(1, $count - 1);
    }

    /**
     * For each label index, the container width in px from which it shows: 0 means
     * always, null never. At most `$maxTicks` labels show at any width.
     *
     * @param  list<string>  $labels
     * @return list<int|null>
     */
    public static function minWidths(array $labels, bool $slots, int $maxTicks = 6): array
    {
        $count = count($labels);
        if ($count === 0) {
            return [];
        }

        $label = max(array_map(self::width(...), $labels));
        $firstHalf = self::width($labels[0]) / 2;
        $lastHalf = self::width($labels[$count - 1]) / 2;
        $base = max(1, (int) ceil($count / $maxTicks));

        $strideAt = function (float $width) use ($count, $slots, $label, $firstHalf, $lastHalf, $base): int {
            $spacing = self::spacing($count, $width, $slots);
            $first = $slots ? $spacing / 2 : 0.0;
            // An edge label is clamped inside the plot (the first may hang into the y-axis
            // column), which moves it towards its neighbour by what would have hung over.
            $left = max(0.0, $firstHalf - self::HANG - $first);
            $right = max(0.0, $lastHalf - $first);
            $stride = $base;
            while ($stride < $count) {
                $edge = max($left, ($count - 1) % $stride === 0 ? $right : 0.0);
                if ($stride * $spacing >= $label + self::GAP + $edge) {
                    break;
                }
                $stride *= 2;
            }

            return $stride;
        };

        $widths = [0 => $strideAt(self::FLOOR)];
        foreach (self::BANDS as $band) {
            $widths[$band] = $strideAt($band);
        }

        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $out[$i] = null;
            foreach ($widths as $band => $stride) {
                // Index 0 always shows; any other label shows once the stride divides it.
                if ($i === 0 || ($stride < $count && $i % $stride === 0)) {
                    $out[$i] = $band;
                    break;
                }
            }
        }

        return $out;
    }

    /**
     * The visible labels at a given plot width (for tests and the gallery notes).
     *
     * @param  list<string>  $labels
     * @return list<int>
     */
    public static function visibleAt(array $labels, bool $slots, float $width, int $maxTicks = 6): array
    {
        $band = 0;
        foreach (self::BANDS as $b) {
            if ($width >= $b) {
                $band = $b;
            }
        }

        return array_keys(array_filter(self::minWidths($labels, $slots, $maxTicks), fn (?int $min): bool => $min !== null && $min <= $band));
    }
}
