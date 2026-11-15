<?php

use Illuminate\Support\Facades\Blade;

/*
| Batch B carry-overs: the overview's trends island and loading overlay, the
| lean chart data table, the export column picker's single-pointer reorder and
| the marketplace gallery's responsive segmented control.
*/

beforeEach(function () {
    $this->withoutVite();
});

it('keeps the sr-only chart table to bare cells when there is no table toggle', function () {
    $html = Blade::render('<x-ui.chart :labels="[\'2026-11-01\', \'2026-11-02\']" :series="[[\'name\' => \'Revenue\', \'points\' => [1500, 0]]]" title="Revenue" money />');

    expect($html)->toContain('<tr><td>1 Nov 2026<td>₦1,500<tr><td>2 Nov 2026<td>₦0</tr>')
        ->toContain('<caption class="sr-only">Revenue data</caption>')
        ->toContain("x-bind:aria-hidden=\"\$store.privacy.hidden ? 'true' : null\"")
        ->toContain("data-plot='{\"type\":\"line\"");

    // With the toggle the table can be shown, so it keeps the kit table rows.
    $toggled = Blade::render('<x-ui.chart :labels="[\'2026-11-01\']" :series="[[\'name\' => \'Revenue\', \'points\' => [3]]]" title="Revenue" toggle />');
    expect($toggled)->not->toContain('<tr><td>1 Nov 2026<td>')->toContain('1 Nov 2026');
});

it('escapes chart labels inside the single-quoted plot payload attribute', function () {
    $html = Blade::render('<x-ui.chart type="bar" :labels="$labels" :series="$series" />', [
        'labels' => ["Ada's <b>&"],
        'series' => [['name' => 'X', 'points' => [1]]],
    ]);
    $attribute = str($html)->after("data-plot='")->before("'")->toString();

    expect($attribute)->toContain('Ada\\u0027s \\u003Cb\\u003E\\u0026')
        ->not->toContain("'")
        ->not->toContain('<')
        ->and(json_decode($attribute, true)['labels'])->toBe(["Ada's <b>&"]);
});
