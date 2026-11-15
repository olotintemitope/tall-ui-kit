<?php

namespace TallUiKit\Support;

use InvalidArgumentException;

/**
 * Every foreground and background pair the UI kit uses, with its WCAG contrast ratio
 * computed from the token values of a theme file (resources/css/themes/<theme>.css).
 * The gallery's contrast table, tests/Feature/ContrastTest.php and
 * scripts/contrast.php all read this list, so the ratios cannot drift from the tokens.
 * The documented ratio (sixth column) is the one measured for the NewBanger theme;
 * every theme must meet the required ratio (fifth column).
 *
 * Colours are theme tokens, Tailwind palette colours the kit uses
 * (their sRGB rendering of Tailwind 4's oklch values, measured in the browser), or
 * `name/NN` for a colour at NN% opacity over the pair's background.
 */
class KitContrast
{
    /** Themes shipped with the kit. */
    public const THEMES = ['neutral', 'newbanger'];

    private static string $theme = 'neutral';

    /** @var array<string, array<string, string>> */
    private static array $tokens = [];

    /** Measure against another theme: a shipped name or an absolute path to a theme file. */
    public static function useTheme(string $theme): void
    {
        self::$theme = $theme;
    }

    public static function theme(): string
    {
        return self::$theme;
    }

    public static function themePath(string $theme): string
    {
        return str_contains($theme, '/') ? $theme : dirname(__DIR__, 2).'/resources/css/themes/'.$theme.'.css';
    }

    /** Tailwind 4 palette colours in use, as rendered in sRGB. */
    private const PALETTE = [
        'white' => '#ffffff',
        'red-50' => '#fef2f2',
        'red-200' => '#ffc9c9',
        'red-600' => '#e7000b',
        'red-700' => '#c10007',
        'red-800' => '#9f0712',
        'emerald-50' => '#ecfdf5',
        'emerald-700' => '#007a55',
        'emerald-800' => '#006045',
        'amber-50' => '#fffbeb',
        'amber-800' => '#973c00',
        'amber-900' => '#7b3306',
        'blue-50' => '#eff6ff',
        'blue-700' => '#1447e6',
    ];

    /**
     * [use, foreground, background, kind (text | ui), required ratio, documented ratio].
     *
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: float, 5: float}>
     */
    public static function pairs(): array
    {
        return [
            // Text roles
            ['Titles, strong text', 'ink', 'white', 'text', 4.5, 15.81],
            ['Body text', 'ink-body', 'white', 'text', 4.5, 11.64],
            ['Meta, captions, labels, placeholders', 'ink-muted', 'white', 'text', 4.5, 5.85],
            ['Meta on a tinted page', 'ink-muted', 'page-muted', 'text', 4.5, 5.47],
            ['Table header, panel footer text', 'ink-muted', 'panel-footer', 'text', 4.5, 5.61],
            ['Links and accent text', 'primary-text', 'white', 'text', 4.5, 5.21],
            ['Rising delta, success text', 'emerald-700', 'white', 'text', 4.5, 5.36],
            ['Falling delta, error text', 'red-700', 'white', 'text', 4.5, 6.42],
            ['Chart ticks and tooltip dates', 'chart-tick', 'white', 'text', 4.5, 5.85],

            // Buttons, every state
            ['Primary button', 'ink', 'primary', 'text', 4.5, 5.32],
            ['Primary button, hover', 'ink', 'primary-fill-hover', 'text', 4.5, 5.97],
            ['Primary button, pressed', 'ink', 'primary-fill-active', 'text', 4.5, 4.62],
            ['Secondary button', 'white', 'secondary', 'text', 4.5, 15.81],
            ['Secondary button, hover', 'white', 'secondary-light', 'text', 4.5, 13.14],
            ['Secondary button, pressed', 'white', 'neutral-900', 'text', 4.5, 18.78],
            ['Destructive button', 'white', 'red-600', 'text', 4.5, 4.77],
            ['Destructive button, hover', 'white', 'red-700', 'text', 4.5, 6.42],
            ['Destructive button, pressed', 'white', 'red-800', 'text', 4.5, 8.36],
            ['Outline and ghost, hover', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Outline and ghost, pressed; toggle on', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Disabled control text', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],
            ['Disabled link', 'ink-muted', 'white', 'text', 4.5, 5.85],
            ['Selected tab, hovered tab', 'ink', 'white', 'text', 4.5, 15.81],
            ['Segment, hover', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Unselected and disabled segment', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],

            // Notices and badges, on their own fills
            ['Danger alert and toast', 'red-800', 'red-50', 'text', 4.5, 7.64],
            ['Warning alert and toast', 'amber-900', 'amber-50', 'text', 4.5, 8.73],
            ['Success alert and toast', 'emerald-800', 'emerald-50', 'text', 4.5, 7.23],
            ['Neutral alert', 'ink', 'panel-footer', 'text', 4.5, 15.15],
            ['Success badge', 'emerald-800', 'emerald-50', 'text', 4.5, 7.23],
            ['Warning badge', 'amber-800', 'amber-50', 'text', 4.5, 6.84],
            ['Danger badge', 'red-700', 'red-50', 'text', 4.5, 5.87],
            ['Info badge', 'blue-700', 'blue-50', 'text', 4.5, 6.28],
            ['Neutral badge', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Accent badge', 'primary-text', 'primary/10', 'text', 4.5, 4.67],

            // Dark admin sidebar (#212322)
            ['Sidebar item', 'white/70', 'secondary', 'text', 4.5, 8.40],
            ['Sidebar section label and count', 'white/65', 'secondary', 'text', 4.5, 7.44],
            ['Sidebar icon', 'white/60', 'secondary', 'ui', 3.0, 6.56],
            ['Sidebar active item', 'white', 'secondary', 'text', 4.5, 15.81],
            ['Focus outline on the sidebar', 'focus', 'secondary', 'ui', 3.0, 3.84],

            // Boundaries and marks (non-text, 3:1)
            ['Control border', 'control', 'white', 'ui', 3.0, 3.65],
            ['Disabled control border (dashed)', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Selected segment edge on its track', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Control border, hover', 'control-hover', 'white', 'ui', 3.0, 4.97],
            ['Primary button boundary', 'primary-hover', 'white', 'ui', 3.0, 3.70],
            ['Destructive button boundary', 'red-600', 'white', 'ui', 3.0, 4.77],
            ['Focus outline', 'focus', 'white', 'ui', 3.0, 4.12],
            ['Selected tab underline, checked control', 'secondary', 'white', 'ui', 3.0, 15.81],
            ['Disabled checked control', 'ink-muted', 'white', 'ui', 3.0, 5.85],
            ['Spinner arc', 'ink', 'white', 'ui', 3.0, 15.81],
            ['Progress bar', 'chart-1', 'white', 'ui', 3.0, 15.81],
            ['Money series', 'chart-accent', 'white', 'ui', 3.0, 5.21],
            ['Series 1', 'chart-1', 'white', 'ui', 3.0, 15.81],
            ['Series 2, sparklines', 'chart-2', 'white', 'ui', 3.0, 5.85],
            ['Series 3 (dashed on lines)', 'chart-3', 'white', 'ui', 3.0, 3.16],
            ['Horizontal bar on its track', 'chart-2', 'surface-muted', 'ui', 3.0, 5.05],

            // Checklist (x-ui.checklist): the next step sits on a surface-subtle tint
            ['Checklist done step label', 'ink-muted', 'white', 'text', 4.5, 5.85],
            ['Checklist next step label', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Checklist next step hint', 'ink-muted', 'surface-subtle', 'text', 4.5, 5.42],
            ['Checklist next step action', 'primary-text', 'surface-subtle', 'text', 4.5, 4.82],
            ['Checklist done check', 'ink', 'white', 'ui', 3.0, 15.81],
            ['Checklist step not done (circle)', 'control', 'white', 'ui', 3.0, 3.65],
            ['Checklist next step not done (circle on the tint)', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Checklist progress fill on its track', 'chart-1', 'surface-muted', 'ui', 3.0, 13.63],
            ['Checklist focus outline on the next-step tint', 'focus', 'surface-subtle', 'ui', 3.0, 3.81],

            // Workflow: stepper, timeline, tag input, avatar group, sortable, slider, tree, banner
            ['Stepper current marker number', 'white', 'secondary', 'text', 4.5, 15.81],
            ['Stepper complete marker ring and check', 'secondary', 'white', 'ui', 3.0, 15.81],
            ['Stepper upcoming marker ring', 'control', 'white', 'ui', 3.0, 3.65],
            ['Stepper upcoming label and number', 'ink-muted', 'white', 'text', 4.5, 5.85],
            ['Stepper error label, ring and description', 'red-700', 'white', 'text', 4.5, 6.42],
            ['Timeline marker ring (no actor)', 'control', 'white', 'ui', 3.0, 3.65],
            ['Tag chip text', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Tag chip remove icon', 'ink-muted', 'surface-muted', 'ui', 3.0, 5.05],
            ['Tag chip remove icon, hover', 'ink', 'line-strong', 'ui', 3.0, 10.53],
            ['Focus outline on a tag chip', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['Avatar group "+N"', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Avatar group tooltip', 'white', 'secondary', 'text', 4.5, 15.81],
            ['Sortable grip', 'ink-muted', 'white', 'ui', 3.0, 5.85],
            ['Sortable lifted row label', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Sortable grip on the lifted row', 'ink-muted', 'surface-subtle', 'ui', 3.0, 5.42],
            ['Focus outline on the lifted row', 'focus', 'surface-subtle', 'ui', 3.0, 3.81],
            ['Slider track', 'control', 'white', 'ui', 3.0, 3.65],
            ['Slider chosen range', 'secondary', 'white', 'ui', 3.0, 15.81],
            ['Slider thumb edge', 'control', 'white', 'ui', 3.0, 3.65],
            ['Tree selected row', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Tree hovered row', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Tree check box', 'control', 'white', 'ui', 3.0, 3.65],
            ['Tree check box on a hovered row', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Tree checked box', 'secondary', 'white', 'ui', 3.0, 15.81],
            ['Tree chevron', 'ink-muted', 'white', 'ui', 3.0, 5.85],
            ['Tree focus outline on the selected row', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['Banner info', 'blue-700', 'blue-50', 'text', 4.5, 6.28],
            ['Banner success', 'emerald-800', 'emerald-50', 'text', 4.5, 7.23],
            ['Banner warning', 'amber-900', 'amber-50', 'text', 4.5, 8.73],
            ['Banner danger', 'red-800', 'red-50', 'text', 4.5, 7.64],
            ['Focus outline on the info banner', 'focus', 'blue-50', 'ui', 3.0, 3.78],
            ['Focus outline on the success banner', 'focus', 'emerald-50', 'ui', 3.0, 3.91],
            ['Focus outline on the warning banner', 'focus', 'amber-50', 'ui', 3.0, 3.97],
            ['Focus outline on the danger banner', 'focus', 'red-50', 'ui', 3.0, 3.76],

            // Progress: x-ui.progress tones and buffer, x-ui.progress-ring, x-ui.progress-steps, x-ui.upload-progress
            ['Progress fill on its track', 'chart-1', 'surface-muted', 'ui', 3.0, 13.63],
            ['Progress success fill on its track', 'emerald-700', 'surface-muted', 'ui', 3.0, 4.62],
            ['Progress success fill on white', 'emerald-700', 'white', 'ui', 3.0, 5.36],
            ['Progress warning fill on its track', 'amber-800', 'surface-muted', 'ui', 3.0, 6.11],
            ['Progress warning fill on white', 'amber-800', 'white', 'ui', 3.0, 7.09],
            ['Progress danger fill on its track', 'red-700', 'surface-muted', 'ui', 3.0, 5.53],
            ['Progress danger fill on white', 'red-700', 'white', 'ui', 3.0, 6.42],
            ['Progress fill against its buffer', 'chart-1', 'line-strong', 'ui', 3.0, 10.53],
            ['Progress success fill against its buffer', 'emerald-700', 'line-strong', 'ui', 3.0, 3.57],
            ['Progress warning fill against its buffer', 'amber-800', 'line-strong', 'ui', 3.0, 4.72],
            ['Progress danger fill against its buffer', 'red-700', 'line-strong', 'ui', 3.0, 4.28],
            ['Upload failed status and error', 'red-700', 'white', 'text', 4.5, 6.42],

            // Date and time pickers (x-ui.date, x-ui.date-range, x-ui.time-input ...)
            ['Calendar day, weekday header, time option', 'ink', 'white', 'text', 4.5, 15.81],
            ['Calendar day or option, hover and active', 'ink', 'surface-subtle', 'text', 4.5, 14.63],
            ['Calendar active option outline', 'control', 'surface-subtle', 'ui', 3.0, 3.38],
            ['Calendar chosen day, week, month, time and pressed preset', 'white', 'secondary', 'text', 4.5, 15.81],
            ['Calendar days inside a range', 'ink', 'surface-muted', 'text', 4.5, 13.63],
            ['Calendar disabled day inside a range', 'ink-muted', 'surface-muted', 'text', 4.5, 5.05],
            ['Calendar disabled day, week number, outside day', 'ink-muted', 'white', 'text', 4.5, 5.85],
            ['Calendar Today and Done', 'primary-text', 'white', 'text', 4.5, 5.21],
            ['Calendar Today and Done, hover', 'primary-text', 'surface-subtle', 'text', 4.5, 4.82],
            ['Calendar preset edge', 'control', 'white', 'ui', 3.0, 3.65],
            ['Calendar focus outline on a range tint', 'focus', 'surface-muted', 'ui', 3.0, 3.55],
            ['Calendar focus outline beside a chosen day', 'focus', 'secondary', 'ui', 3.0, 3.84],
        ];
    }

    public static function ratio(string $foreground, string $background): float
    {
        $bg = self::rgb($background, [255, 255, 255]);
        $fg = self::rgb($foreground, $bg);

        $l1 = self::luminance($fg);
        $l2 = self::luminance($bg);

        return round((max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05), 2);
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $under  what a translucent colour sits on
     * @return array{0: int, 1: int, 2: int}
     */
    public static function rgb(string $color, array $under): array
    {
        [$name, $alpha] = str_contains($color, '/') ? explode('/', $color, 2) : [$color, '100'];
        $hex = ltrim(self::hex($name), '#');
        $rgb = array_map(fn (string $pair): int => hexdec($pair), str_split($hex, 2));
        $a = (int) $alpha / 100;

        return array_map(fn (int $c, int $u): int => (int) round($c * $a + $u * (1 - $a)), $rgb, $under);
    }

    public static function hex(string $name): string
    {
        if (isset(self::PALETTE[$name])) {
            return self::PALETTE[$name];
        }

        self::$tokens[self::$theme] ??= self::tokens(self::$theme);

        return self::$tokens[self::$theme][$name] ?? throw new InvalidArgumentException("Unknown colour token [{$name}].");
    }

    /**
     * @return array<string, string>
     */
    private static function tokens(string $theme): array
    {
        $path = self::themePath($theme);

        if (! is_file($path)) {
            throw new InvalidArgumentException("Unknown theme [{$theme}] ({$path}).");
        }

        preg_match_all('/--color-([a-z0-9-]+):\s*(#[0-9A-Fa-f]{6})\s*;/', (string) file_get_contents($path), $matches, PREG_SET_ORDER);

        return collect($matches)->mapWithKeys(fn (array $m): array => [$m[1] => strtolower($m[2])])->all();
    }

    /**
     * @param  array{0: int, 1: int, 2: int}  $rgb
     */
    private static function luminance(array $rgb): float
    {
        [$r, $g, $b] = array_map(function (int $c): float {
            $c /= 255;

            return $c <= 0.03928 ? $c / 12.92 : (($c + 0.055) / 1.055) ** 2.4;
        }, $rgb);

        return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
    }
}
