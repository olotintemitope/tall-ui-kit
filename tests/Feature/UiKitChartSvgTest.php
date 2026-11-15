<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;
use TallUiKit\Support\Charts\ChartPlot;
use TallUiKit\Support\Charts\NiceScale;
use TallUiKit\Support\Charts\TickThinning;

/*
| x-ui.chart as server-rendered SVG (TallUiKit\Support\Charts): nice ticks, label thinning,
| crisp gridlines, the empty plot, privacy masking, stacked sums and the size of a
| Livewire round trip on /admin/analytics.
*/

beforeEach(function () {
    $this->withoutVite();
});

function svgPlot(string $type, array $series, array $labels, string $yFormat = 'number', bool $money = false, int $height = 160): ChartPlot
{
    $normalised = array_map(fn (array $s, int $i): array => [
        'label' => $s['name'] ?? 'S'.$i,
        'data' => $s['points'],
        'color' => $s['token'] ?? 'chart-1',
        'dashed' => $type === 'line' && $i === 2,
    ], $series, array_keys($series));

    return new ChartPlot($type, $normalised, $labels, $height, $yFormat, $money);
}

describe('nice ticks', function () {
    it('picks at most four 1, 2 or 5 steps from zero', function (array $values, array $ticks) {
        expect(NiceScale::for($values)['ticks'])->toBe($ticks);
    })->with([
        'small counts' => [[6, 4, 8], [0.0, 5.0, 10.0]],
        'revenue' => [[118000, 312000, 75000], [0.0, 200000.0, 400000.0]],
        'stack totals' => [[66, 64, 97], [0.0, 50.0, 100.0]],
        'exact top' => [[30], [0.0, 10.0, 20.0, 30.0]],
        'all zero keeps a 0 to 1 axis' => [[0, 0], [0.0, 1.0]],
        'negative' => [[-4, 6], [-5.0, 0.0, 5.0, 10.0]],
    ]);

    it('keeps whole numbers for counts and two decimals for percentages', function () {
        expect(NiceScale::for([2])['ticks'])->toBe([0.0, 1.0, 2.0])
            ->and(NiceScale::for([0, 0], 4, 0.01, 2)['ticks'])->toBe([0.0, 0.01])
            ->and(NiceScale::for([0.37], 4, 0.01, 2)['ticks'])->toBe([0.0, 0.2, 0.4]);
    });

    it('formats ticks like the old axis: compact from 10,000, money symbol, plain below it', function () {
        $plot = svgPlot('line', [['points' => [5000, 15000]]], ['a', 'b'], money: true);

        expect(array_column($plot->yTicks(), 'label'))->toBe(['₦15k', '₦10k', '₦5,000', '₦0']);
    });
});

describe('label thinning', function () {
    it('shows at most six labels and keeps more as the plot widens', function () {
        $days = array_map(fn (int $i): string => Carbon::parse('2026-10-17')->addDays($i)->format('j M'), range(0, 29));

        $narrow = TickThinning::visibleAt($days, false, 300);
        $wide = TickThinning::visibleAt($days, false, 1100);

        expect($wide)->toBe([0, 5, 10, 15, 20, 25])
            ->and(count($narrow))->toBeLessThan(count($wide))
            ->and($narrow[0])->toBe(0)
            // Nested strides: what shows when narrow also shows when wide.
            ->and(array_diff($narrow, $wide))->toBe([]);
    });

    it('never lets two visible labels overlap at any width', function (array $labels, bool $slots) {
        foreach ([160, 200, 256, 300, 320, 357, 390, 512, 700, 1024, 1400] as $width) {
            $visible = TickThinning::visibleAt($labels, $slots, $width);
            $spacing = TickThinning::spacing(count($labels), $width, $slots);
            $widest = max(array_map(TickThinning::width(...), $labels));

            foreach (array_slice($visible, 1) as $k => $index) {
                $gap = ($index - $visible[$k]) * $spacing;
                expect($gap)->toBeGreaterThanOrEqual($widest + TickThinning::GAP, "{$width}px, labels {$visible[$k]} and {$index}");
            }
        }
    })->with([
        '90 days' => [array_map(fn (int $i): string => Carbon::parse('2026-08-17')->addDays($i)->format('j M'), range(0, 89)), false],
        'platforms' => [['Spotify', 'Apple Music', 'YouTube Music', 'Audiomack', 'Boomplay', 'Deezer', 'Tidal'], true],
        'weeks' => [array_map(fn (int $i): string => 'Week '.$i, range(34, 45)), true],
    ]);

    it('hides thinned labels with container queries, not a script', function () {
        $labels = array_map(fn (int $i): string => Carbon::parse('2026-10-17')->addDays($i)->toDateString(), range(0, 29));
        $html = Blade::render('<x-ui.chart :labels="$labels" :series="[[\'points\' => array_fill(0, 30, 1)]]" />', ['labels' => $labels]);
        $ticks = str($html)->after('data-chart-xticks>')->before('</div>')->toString();

        expect($ticks)->toContain('17 Oct')
            ->toMatch('/hidden @min-\[\d+px\]:block[^>]*>22 Oct</')
            ->not->toContain('18 Oct')
            ->and($html)->toMatch('/class="@container relative col-start-2 \*:type-caption[^"]*"[^>]*data-chart-xticks/');
    });
});

describe('plot geometry', function () {
    it('puts gridlines on half pixels so they draw 1px thick, as real svg with real text ticks', function () {
        $html = Blade::render('<x-ui.chart :labels="[\'a\', \'b\']" :series="[[\'points\' => [3, 8]]]" />');
        preg_match('/<path d="([^"]+)"[^>]*data-chart-grid/', $html, $grid);
        preg_match_all('/M0 ([\d.]+)H1000/', $grid[1], $ys);

        expect($ys[1])->not->toBeEmpty()
            ->and(collect($ys[1])->every(fn (string $y): bool => fmod((float) $y, 1.0) === 0.5))->toBeTrue()
            ->and($html)->toContain('height="140" viewBox="0 0 1000 140" preserveAspectRatio="none"')
            // Ticks are page text in the caption role (Inter through the font variables).
            ->toMatch('/class="[^"]*\*:type-caption[^"]*"[^>]*data-chart-yticks><span[^>]*>10<\/span>/')
            ->not->toMatch('/<text\b/');
    });

    it('stacks segments whose heights add up to the column total, rounding only the top one', function () {
        $plot = svgPlot('stacked-bar', [
            ['points' => [41, 37, 0]],
            ['points' => [19, 22, 0], 'token' => 'chart-2'],
            ['points' => [6, 5, 7], 'token' => 'chart-3'],
        ], ['W1', 'W2', 'W3']);

        foreach ($plot->columns() as $i => $segments) {
            expect(array_sum(array_column($segments, 'height')))->toBe($plot->length($plot->stackTotal($i)))
                ->and(array_column(array_filter($segments, fn (array $s): bool => $s['rounded']), 'color'))->toBe(['chart-3'])
                ->and(end($segments)['gap'])->toBeFalse();
        }

        expect($plot->stackTotal(0))->toBe(66.0)
            ->and($plot->columns()[0][0]['gap'])->toBeTrue()
            ->and($plot->columns()[2][2]['gap'])->toBeFalse();
    });

    it('keeps the empty plot with a 0 to 1 axis, a flat line and the caption over it', function () {
        $html = Blade::render('<x-ui.chart :labels="[\'2026-11-01\', \'2026-11-02\']" :series="[[\'points\' => [0, 0]]]" empty="Nothing yet." />');
        preg_match('/<path d="([^"]+)"[^>]*data-chart-line/', $html, $line);

        expect($html)->toContain('data-chart-svg')
            ->toContain('data-chart-empty>Nothing yet.</p>')
            ->toMatch('/>1<\/span>/')
            ->toMatch('/>0<\/span>/')
            ->and($line[1])->toBe('M0 139L1000 139');

        $bars = Blade::render('<x-ui.chart type="bar" :labels="[\'a\']" :series="[[\'points\' => [0]]]" />');
        expect($bars)->toContain('data-chart-bars')->toContain('style="height:0px;');
    });

    it('masks money in the axis, the tooltip and the accessible name under privacy mode', function () {
        $html = Blade::render('<x-ui.chart :labels="[\'a\']" :series="[[\'points\' => [1500]]]" title="Revenue" money />');
        $plain = Blade::render('<x-ui.chart :labels="[\'a\']" :series="[[\'points\' => [1500]]]" title="Plays" />');
        $charts = file_get_contents(kit_path('resources/js/charts.js'));

        // Axis: hidden by the html[data-privacy] flag set before first paint, keeping its width.
        expect($html)->toMatch('/class="[^"]*\[html\[data-privacy=hidden\]_&\]:invisible[^"]*"[^>]*data-chart-yticks/')
            ->toContain('"money":true')
            ->toContain('x-bind:aria-label="$store.privacy.hidden ? \'Revenue, total hidden\'')
            // Tooltip and keyboard read-out: the script masks when the payload says money.
            ->and($charts)->toContain("const MASK = '•••••'")
            ->toContain('masked ? MASK : s.values[index]')
            ->and($plain)->not->toContain('data-privacy=hidden')->toContain('"money":false');
    });

    it('makes the plot focusable with a live region for the keyboard read-out', function () {
        $html = Blade::render('<x-ui.chart :labels="[\'a\', \'b\']" :series="[[\'points\' => [1, 2]]]" />');
        $charts = file_get_contents(kit_path('resources/js/charts.js'));

        expect($html)->toContain('x-on:keydown="key($event)"')
            ->toContain('aria-live="polite" x-ref="live"')
            ->and($charts)->toContain('ArrowRight')->toContain('ArrowLeft')->toContain("event.key === 'Escape'");
    });
});
