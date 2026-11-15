<?php

/*
 * Measures every foreground/background pair the kit uses (TallUiKit\Support\KitContrast
 * and SidebarContrast) against a theme's tokens, in light and in dark. Text needs
 * 4.5:1, boundaries and marks 3:1, in every state and scheme. Exits 1 when any pair
 * fails, or when a light colour token has no dark value.
 *
 *   composer contrast                 # every shipped theme, both schemes (four sets)
 *   composer contrast neutral         # one theme, both schemes
 *   composer contrast neutral:dark    # one set
 *   php scripts/contrast.php /abs/path/to/my-theme.css
 */

require __DIR__.'/../vendor/autoload.php';

use TallUiKit\Support\KitContrast;
use TallUiKit\Support\SidebarContrast;

$args = array_slice($argv, 1) ?: KitContrast::THEMES;
$failed = 0;
$summary = [];

foreach ($args as $arg) {
    [$theme, $only] = array_pad(explode(':', $arg, 2), 2, null);
    $modes = $only ? [$only] : KitContrast::MODES;

    $names = KitContrast::declaredNames($theme);
    $missing = $names['dark'] === [] ? [] : array_diff($names['light'], $names['dark']);
    if ($names['dark'] === []) {
        $modes = ['light'];
        printf("\n%s: no `@variant dark` block, light only\n", $theme);
    } elseif ($missing !== []) {
        $failed += count($missing);
        printf("\n%s: FAIL light tokens with no dark value: %s\n", $theme, implode(', ', $missing));
    }

    foreach ($modes as $mode) {
        KitContrast::useTheme($theme, $mode);
        $rows = [];

        foreach (KitContrast::pairs() as [$use, $fg, $bg, $kind, $min]) {
            $rows[] = [$use, "{$fg} on {$bg}", $kind, $min, KitContrast::ratio($fg, $bg)];
        }

        foreach (SidebarContrast::rows() as $row) {
            foreach (['dark', 'light', 'inset'] as $variant) {
                $rows[] = ["Sidebar ({$variant}): {$row['state']}", $variant, $row['min'] >= 4.5 ? 'text' : 'ui', $row['min'], $row[$variant]];
            }
        }

        $fails = array_filter($rows, fn (array $r): bool => $r[4] < $r[3]);
        $failed += count($fails);
        $text = array_values(array_filter($rows, fn ($r) => $r[2] === 'text'));
        $ui = array_values(array_filter($rows, fn ($r) => $r[2] !== 'text'));
        usort($text, fn ($a, $b) => $a[4] <=> $b[4]);
        usort($ui, fn ($a, $b) => $a[4] <=> $b[4]);

        printf("\n%s %s: %d pairs, %d failing; lowest text %.2f:1 (%s, %s), lowest non-text %.2f:1 (%s, %s)\n", $theme, $mode, count($rows), count($fails), $text[0][4], $text[0][0], $text[0][1], $ui[0][4], $ui[0][0], $ui[0][1]);
        $summary[] = sprintf('%-10s %-5s %3d pairs %2d failing  text %.2f  non-text %.2f', $theme, $mode, count($rows), count($fails), $text[0][4], $ui[0][4]);

        foreach ($rows as [$use, $pair, $kind, $min, $ratio]) {
            printf("  %s %5.2f  (min %.1f)  %-52s %s\n", $ratio >= $min ? 'ok  ' : 'FAIL', $ratio, $min, $use, $pair);
        }
    }
}

echo "\n".implode("\n", $summary)."\n";

exit($failed > 0 ? 1 : 0);
