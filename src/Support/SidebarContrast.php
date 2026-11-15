<?php

namespace TallUiKit\Support;

/**
 * Measured contrast of every x-ui.sidebar state on the dark, light and inset
 * surfaces (docs/ui-kit.md "Sidebar"). Colours are layered as rendered: a
 * translucent fill over the sidebar surface, then translucent text over that.
 * Text needs 4.5:1; the active ring and icons 3:1.
 */
class SidebarContrast
{
    /**
     * Each variant's surface and the colours its tone classes use (SidebarTones::tones),
     * from the active theme's tokens (KitContrast::useTheme).
     *
     * @return array<string, array<string, mixed>>
     */
    private static function variants(): array
    {
        $t = fn (string $name): string => KitContrast::hex($name);
        $light = fn (string $surface): array => [
            'surface' => $t($surface),
            'item' => [$t('ink-muted'), 1.0], 'hover' => [$t('ink'), 1.0], 'hoverFill' => ['#000000', 0.04],
            'active' => [$t('ink'), 1.0], 'activeFill' => ['#000000', 0.06], 'ring' => [$t('control-hover'), 1.0],
            'label' => [$t('ink-muted'), 1.0], 'count' => [$t('ink-muted'), 1.0], 'icon' => [$t('ink-muted'), 1.0],
            'disabled' => [$t('ink-muted'), 1.0], 'tag' => [$t('ink'), 1.0], 'meta' => [$t('ink-muted'), 1.0],
        ];

        return [
            'dark' => [
                'surface' => $t('secondary'),
                'item' => ['#ffffff', 0.70], 'hover' => ['#ffffff', 1.0], 'hoverFill' => ['#ffffff', 0.06],
                'active' => ['#ffffff', 1.0], 'activeFill' => ['#ffffff', 0.10], 'ring' => ['#ffffff', 0.40],
                'label' => ['#ffffff', 0.65], 'count' => ['#ffffff', 0.65], 'icon' => ['#ffffff', 0.60],
                'disabled' => ['#ffffff', 0.65], 'tag' => ['#ffffff', 1.0], 'meta' => ['#ffffff', 0.65],
            ],
            'light' => $light('panel-footer'),
            'inset' => $light('page-muted'),
        ];
    }

    /**
     * [state, foreground key, fill key or null, minimum].
     */
    private const STATES = [
        ['Item text', 'item', null, 4.5],
        ['Item text, hover', 'hover', 'hoverFill', 4.5],
        ['Active item text', 'active', 'activeFill', 4.5],
        ['Active ring against the sidebar', 'ring', null, 3.0],
        ['Section label', 'label', null, 4.5],
        ['Count', 'count', null, 4.5],
        ['Icon', 'icon', null, 3.0],
        ['Disabled item text', 'disabled', null, 4.5],
        ['"New" tag text', 'tag', null, 4.5],
        ['Account role', 'meta', null, 4.5],
    ];

    /**
     * @return list<array{state: string, min: float, dark: float, light: float, inset: float}>
     */
    public static function rows(): array
    {
        $variants = self::variants();

        return array_map(function (array $state) use ($variants): array {
            [$label, $fg, $fill, $min] = $state;
            $row = ['state' => $label, 'min' => $min];
            foreach ($variants as $variant => $c) {
                $bg = self::over(self::rgb($c['surface']), $fill ? $c[$fill] : null);
                $row[$variant] = self::ratio(self::over($bg, $c[$fg]), $bg);
            }

            return $row;
        }, self::STATES);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $under
     * @param  array{0: string, 1: float}|null  $layer
     * @return array{0: float, 1: float, 2: float}
     */
    private static function over(array $under, ?array $layer): array
    {
        if ($layer === null) {
            return $under;
        }
        [$hex, $alpha] = $layer;
        $rgb = self::rgb($hex);

        return [0 => $rgb[0] * $alpha + $under[0] * (1 - $alpha), 1 => $rgb[1] * $alpha + $under[1] * (1 - $alpha), 2 => $rgb[2] * $alpha + $under[2] * (1 - $alpha)];
    }

    /**
     * @return array{0: int, 1: int, 2: int}
     */
    private static function rgb(string $hex): array
    {
        $pairs = str_split(ltrim($hex, '#'), 2);

        return [0 => (int) hexdec($pairs[0]), 1 => (int) hexdec($pairs[1]), 2 => (int) hexdec($pairs[2])];
    }

    /**
     * @param  array<int, float|int>  $a
     * @param  array<int, float|int>  $b
     */
    private static function ratio(array $a, array $b): float
    {
        $l1 = self::luminance($a);
        $l2 = self::luminance($b);

        return round((max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05), 2);
    }

    /**
     * @param  array<int, float|int>  $rgb
     */
    private static function luminance(array $rgb): float
    {
        [$r, $g, $b] = array_map(function (float|int $c): float {
            $c = round($c) / 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
