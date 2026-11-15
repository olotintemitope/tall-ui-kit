<?php

use Illuminate\Support\Facades\Blade;
use Livewire\Component;
use Livewire\Livewire;

/*
| Page cross-fade (wire:transition.navigate on the app's <main>) and the chart entrance
| (data-motion-enter on x-ui.chart's plot frame), docs/motion.md.
*/

function motionChart(string $attributes = '', array $points = [3, 5, 2]): string
{
    return Blade::render('<x-ui.chart '.$attributes.' :labels="$labels" :series="$series" />', [
        'labels' => ['2026-11-01', '2026-11-02', '2026-11-03'],
        'series' => [['name' => 'Orders', 'points' => $points]],
    ]);
}

class MotionChartComponent extends Component
{
    public int $bump = 0;

    public function render(): string
    {
        return <<<'BLADE'
            <div>
                <x-ui.chart type="bar" :labels="['a', 'b', 'c']" :series="[['name' => 'Orders', 'points' => [3 + $bump, 5, 2]]]" />
            </div>
        BLADE;
    }
}

describe('page cross-fade', function () {
    it('cross-fades the navigate region with opacity only and holds the root and toaster still', function () {
        $css = file_get_contents(kit_path('resources/css/tall-ui-kit.css'));

        expect($css)
            ->toMatch('/::view-transition-group\(root\),\s*::view-transition-group\(livewire-navigate\) \{\s*animation-name: none;/')
            ->toMatch('/::view-transition-new\(livewire-navigate\) \{\s*animation-duration: var\(--motion-sm\);\s*animation-timing-function: var\(--motion-ease-out\);/')
            ->toContain("[data-toast-region] {\n    view-transition-name: ui-toaster;")
            ->toMatch('/::view-transition \{\s*pointer-events: none;/')
            ->toMatch('/@media \(prefers-reduced-motion: reduce\) \{\s*::view-transition-group\(\*\),\s*::view-transition-old\(\*\),\s*::view-transition-new\(\*\) \{\s*animation: none !important;/')
            ->toContain("html[data-motion-preview='reduced']::view-transition-group(*)")
            ->not->toContain('@view-transition')
            ->not->toContain('will-change');
    });

    it('keeps the toaster region the element that carries the toaster name', function () {
        expect(Blade::render('<x-ui.toaster />'))->toContain('data-toast-region');
    });
});

describe('chart entrance', function () {
    it('puts the bare hook on line and bar plots', function (string $attributes) {
        $html = motionChart($attributes);

        expect($html)->toMatch('/data-chart-frame\s+data-motion-enter\s*>/')
            ->not->toContain('--motion-length')->not->toContain('--motion-delay')
            ->and(substr_count($html, 'data-motion-enter'))->toBe(1);
    })->with(['line' => 'type="line"', 'bar' => 'type="bar"', 'stacked' => 'type="bar" stacked']);

    it('leaves out the hook when opted out, empty, loading or not a plot', function (string $attributes, array $points) {
        expect(motionChart($attributes, $points))->not->toContain('data-motion-enter');
    })->with([
        'motion none' => ['type="line" motion="none"', [3, 5, 2]],
        'data-motion-static' => ['type="bar" data-motion-static', [3, 5, 2]],
        'empty plot' => ['type="line"', [0, 0, 0]],
        'loading' => ['type="line" loading', [3, 5, 2]],
        'horizontal bars' => ['type="bar-horizontal"', [3, 5, 2]],
        'sparkline' => ['type="sparkline"', [3, 5, 2]],
    ]);

    it('keeps the tooltip, crosshair, keyboard and sr-only table beside the hook', function () {
        expect(motionChart('type="line" money'))->toContain('data-chart-tooltip')->toContain('data-chart-crosshair')
            ->toContain('x-on:keydown="key($event)"')->toContain('data-chart-table')
            ->toContain('[html[data-privacy=hidden]_&]:invisible');
    });

    it('hides marks only while waiting on screen with motion allowed', function () {
        $css = file_get_contents(kit_path('resources/css/tall-ui-kit.css'));
        preg_match('/@media screen and \(prefers-reduced-motion: no-preference\) \{(.*?)\n\}\n/s', $css, $block);

        expect($block[1] ?? '')->toContain("[data-motion-enter='wait'] [data-chart-line]")
            ->toContain("[data-motion-enter='run'] [data-chart-bars] > * > *")
            ->toContain('transform-origin: 50% 100%')
            ->toContain(":not([data-motion-preview='reduced'])")
            ->and($css)->toContain('@keyframes motion-draw {')->toContain('from { transform: scaleY(0); }');
    });

    it('uses one shared observer and carries the state across morphs', function () {
        $js = file_get_contents(kit_path('resources/js/motion.js'));

        expect(substr_count($js, 'new IntersectionObserver('))->toBe(1)
            ->and($js)->toContain('entranceObserver ??= new IntersectionObserver(')
            ->toContain("toEl.setAttribute(ENTER, el.hasAttribute(ENTER) ? el.getAttribute(ENTER) : 'done');")
            ->toContain("hook('morph.added'")
            ->not->toContain("from 'gsap'");
    });

    it('sends the same bare hook on a morph, with no animation state or styles', function () {
        $test = Livewire::test(MotionChartComponent::class);
        $first = $test->html();
        $morph = $test->set('bump', 4)->html();

        foreach ([$first, $morph] as $html) {
            expect($html)->toMatch('/data-chart-frame\s+data-motion-enter\s*>/')
                ->not->toMatch('/data-motion-enter="/')
                ->not->toContain('--motion-length')->not->toContain('--motion-delay');
        }
        expect($morph)->not->toBe($first);
    });
});
