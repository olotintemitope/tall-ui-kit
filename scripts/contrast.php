<?php

/*
 * Measures every foreground/background pair the kit uses (TallUiKit\Support\KitContrast
 * and SidebarContrast) against a theme's tokens. Text needs 4.5:1, boundaries and
 * marks 3:1, in every state. Exits 1 when any pair fails.
 *
 *   composer contrast                 # every shipped theme
 *   composer contrast neutral         # one theme
 *   php scripts/contrast.php /abs/path/to/my-theme.css
 */

require __DIR__.'/../vendor/autoload.php';

use TallUiKit\Support\KitContrast;
use TallUiKit\Support\SidebarContrast;

$themes = array_slice($argv, 1) ?: KitContrast::THEMES;
$failed = 0;

foreach ($themes as $theme) {
    KitContrast::useTheme($theme);
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
    $lowestText = min(array_map(fn ($r) => $r[4], array_filter($rows, fn ($r) => $r[2] === 'text')));
    $lowestUi = min(array_map(fn ($r) => $r[4], array_filter($rows, fn ($r) => $r[2] !== 'text')));

    printf("\n%s theme: %d pairs, %d failing; lowest text %.2f:1, lowest non-text %.2f:1\n", $theme, count($rows), count($fails), $lowestText, $lowestUi);

    foreach ($rows as [$use, $pair, $kind, $min, $ratio]) {
        printf("  %s %5.2f  (min %.1f)  %-48s %s\n", $ratio >= $min ? 'ok  ' : 'FAIL', $ratio, $min, $use, $pair);
    }
}

exit($failed > 0 ? 1 : 0);
