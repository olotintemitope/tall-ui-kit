<?php

use TallUiKit\Support\KitContrast;
use TallUiKit\Support\SidebarContrast;

/*
| Every pair in TallUiKit\Support\KitContrast and SidebarContrast is measured against
| each shipped theme's tokens: text 4.5:1, boundaries and marks 3:1, no exemptions.
| The NewBanger theme must also match the ratios documented for it.
*/

afterEach(fn () => KitContrast::useTheme('neutral', 'light'));

$pairs = fn (): array => collect(KitContrast::pairs())->mapWithKeys(fn (array $pair): array => [$pair[0].' ('.$pair[1].' on '.$pair[2].')' => $pair])->all();

it('meets the required ratio in every theme and colour scheme', function (string $theme, string $mode) {
    KitContrast::useTheme($theme, $mode);

    foreach (KitContrast::pairs() as [$use, $fg, $bg, $kind, $min]) {
        expect(KitContrast::ratio($fg, $bg))->toBeGreaterThanOrEqual($min, "{$theme} {$mode}: {$use}");
    }

    foreach (SidebarContrast::rows() as $row) {
        foreach (['dark', 'light', 'inset'] as $variant) {
            expect($row[$variant])->toBeGreaterThanOrEqual($row['min'], "{$theme} {$mode}: sidebar {$variant} {$row['state']}");
        }
    }
})->with(KitContrast::THEMES)->with(KitContrast::MODES);

it('matches the documented NewBanger ratios', function (string $use, string $fg, string $bg, string $kind, float $min, float $documented) {
    KitContrast::useTheme('newbanger');

    expect(KitContrast::ratio($fg, $bg))->toEqualWithDelta($documented, 0.005);
})->with($pairs);

it('reads tokens from the theme files', function () {
    KitContrast::useTheme('newbanger');
    expect(KitContrast::hex('ink'))->toBe('#212322')
        ->and(KitContrast::hex('control'))->toBe('#858685')
        ->and(KitContrast::hex('primary'))->toBe('#ff614d');

    KitContrast::useTheme('neutral');
    expect(KitContrast::hex('primary'))->not->toBe('#ff614d')
        ->and(KitContrast::ratio('white', 'white'))->toBe(1.0)
        ->and(KitContrast::ratio('ink', 'white'))->toBe(KitContrast::ratio('white', 'ink'));
});

it('requires 4.5:1 for every text pair and 3:1 for every mark', function () {
    foreach (KitContrast::pairs() as [$use, $fg, $bg, $kind, $min]) {
        expect($min)->toBe($kind === 'text' ? 4.5 : 3.0, $use);
    }
});

it('defines the same colour tokens in every theme', function () {
    $tokens = fn (string $theme): array => (preg_match_all('/--color-([a-z0-9-]+):/', file_get_contents(KitContrast::themePath($theme)), $m) ? array_values(array_unique($m[1])) : []);

    expect($tokens('neutral'))->toEqualCanonicalizing($tokens('newbanger'));
});

it('gives every light colour token a dark value in every theme', function (string $theme) {
    $names = KitContrast::declaredNames($theme);

    expect($names['dark'])->not->toBe([])
        ->and(array_values(array_diff($names['light'], $names['dark'])))->toBe([], "{$theme}: light tokens with no dark value")
        ->and(array_values(array_diff($names['dark'], $names['light'])))->toBe([], "{$theme}: dark tokens with no light value");
})->with(KitContrast::THEMES);

it('reads dark tokens from the dark block and keeps the page near-black, never pure black', function (string $theme) {
    KitContrast::useTheme($theme, 'dark');

    expect(KitContrast::hex('surface'))->not->toBe('#000000')
        ->and(KitContrast::ratio('surface', 'black'))->toBeGreaterThan(1.05)
        ->and(KitContrast::ratio('ink', 'surface'))->toBeGreaterThan(12.0);

    // Panels and popovers sit one and two steps above the page.
    $lum = fn (string $t): float => KitContrast::ratio($t, 'black');
    expect($lum('card'))->toBeGreaterThan($lum('surface'))
        ->and($lum('popover'))->toBeGreaterThanOrEqual($lum('card'));
})->with(KitContrast::THEMES);

it('keeps the NewBanger accent as the dark fill and accent text', function () {
    KitContrast::useTheme('newbanger', 'dark');

    expect(KitContrast::hex('primary'))->toBe('#ff614d')
        ->and(KitContrast::hex('primary-text'))->toBe('#ff614d');
});
