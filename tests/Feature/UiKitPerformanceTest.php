<?php

/*
| Performance rules from docs/ui-kit.md "Performance" that can be checked statically.
*/

it('keeps no chart instance to update, resize or destroy', function () {
    $charts = file_get_contents(kit_path('resources/js/charts.js'));
    $html = Blade::render('<x-ui.chart :labels="[\'2026-11-01\', \'2026-11-02\']" :series="[[\'points\' => [1, 2]]]" />');

    // Nothing observes the page: a Livewire morph sends the new svg, a resize stretches it.
    expect($charts)->not->toContain('ResizeObserver')
        ->not->toContain('MutationObserver')
        ->not->toContain('addEventListener')
        ->and($html)->toContain('preserveAspectRatio="none"')
        ->toContain('@container')
        ->not->toMatch('/wire:ignore[^>]*>\s*<svg/');
});

it('draws sparklines as server-rendered svg, never a canvas', function () {
    $html = Blade::render('<x-ui.chart type="sparkline" :series="[[\'points\' => [1, 2, 3]]]" />');

    expect($html)->toContain('<polyline')->not->toContain('<canvas')->not->toContain('x-data');
});

it('delays loader visuals so fast requests never flash them', function () {
    expect(Blade::render('<x-ui.button loading="save">Save</x-ui.button>'))->toContain('wire:loading.delay.short.flex');
    expect(Blade::render('<x-ui.loading-overlay target="refresh">x</x-ui.loading-overlay>'))->toContain('wire:loading.delay.short.attr="aria-busy"');
});

it('animates loaders with transform and opacity only', function () {
    $css = file_get_contents(kit_path('resources/css/tall-ui-kit.css'));
    preg_match_all('/@keyframes (loader-fade|progress-slide) \{(.*?)\n    \}/s', $css, $frames);

    expect($frames[1])->toHaveCount(2);
    foreach ($frames[2] as $body) {
        expect($body)->not->toMatch('/\b(width|height|top|left|right|bottom|margin)\s*:/');
    }
    expect($css)->not->toContain('will-change');
});

it('positions x-anchor elements only while they are shown', function () {
    $ui = file_get_contents(kit_path('resources/js/ui.js'));
    $anchor = file_get_contents(kit_path('resources/js/anchor.js'));

    // Wrapped before Livewire registers Alpine's plugins, so every x-anchor (tooltips,
    // dropdowns, popovers) starts Floating UI only when its element gets a box.
    expect($ui)->toContain("import './anchor';")
        ->and($anchor)->toContain("addEventListener('livewire:init'")
        ->toContain("name !== 'anchor'")
        ->toContain('new ResizeObserver')
        ->toContain('Alpine.skipDuringClone(lazy, handler)');
});

it('never reads layout while Alpine initialises tables and tabs', function () {
    $table = Blade::render('<x-ui.table caption="Rows"><tr><td>1</td></tr></x-ui.table>');
    $tabs = file_get_contents(kit_path('resources/views/components/ui/tabs/index.blade.php'));

    expect($table)->toContain('new ResizeObserver(')->not->toContain('check();')
        ->and($tabs)->toContain('x-init="requestAnimationFrame(() => reveal());');
});

it('does not slide tab and segmented indicators for Alpine\'s first bindings', function () {
    expect(file_get_contents(kit_path('resources/js/motion.js')))
        ->toContain("document.addEventListener('alpine:initialized', settleAfterInit);")
        ->toContain('if (!settled) return;');
});
