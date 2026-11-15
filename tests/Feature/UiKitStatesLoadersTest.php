<?php

use Illuminate\Support\Facades\Blade;

/*
| Interaction states (buttons, tabs, segmented) and the loader family.
*/

function kit(string $template, array $data = []): string
{
    return Blade::render($template, $data);
}

describe('states', function () {
    it('gives every button variant hover, pressed, focus and disabled classes without opacity', function (string $variant) {
        $html = kit('<x-ui.button :variant="$v">Save</x-ui.button>', ['v' => $variant]);

        expect($html)->toContain('state-hover:')
            ->toContain('state-active:')
            ->toContain('focus-ring')
            ->toContain('state-disabled:cursor-not-allowed')
            ->toContain('state-disabled:text-ink-muted')
            ->not->toMatch('/\bopacity-\d/')
            ->not->toMatch('/(?<![\w-])(?:hover|focus):(?:bg|text|border|outline)/')
            ->not->toMatch('/(?:scale|translate)-/');
    })->with(['primary', 'secondary', 'outline', 'ghost', 'destructive', 'link']);

    it('defines the state variants once, on :focus-visible and data-force', function () {
        $css = file_get_contents(kit_path('resources/css/tall-ui-kit.css'));

        expect($css)->toContain("&:hover:not(:disabled, [aria-disabled='true'], [aria-busy='true'])")
            ->toContain("&:active:not(:disabled, [aria-disabled='true'], [aria-busy='true'])")
            ->toContain("&[data-force='hover']")
            ->toContain("&[data-force='active']")
            ->toMatch('/@utility focus-ring \{\s*&:focus-visible,\s*&\[data-force=\'focus\'\]/')
            ->toMatch('/@utility focus-ring-inset \{\s*&:focus-visible,/')
            ->not->toMatch('/@utility focus-ring[^{]*\{\s*&:focus[,\s{]/');
    });

    it('marks a busy button with aria-busy and keeps its label for the accessible name', function () {
        $static = kit('<x-ui.button busy>Save</x-ui.button>');
        expect($static)->toContain('aria-busy="true"')
            ->toContain('data-spinner')
            ->toMatch('/<span class="inline-flex items-center gap-1.5 opacity-0"\s*>Save/')
            ->not->toContain('invisible');

        expect(kit('<x-ui.button wire:click="save" loading="save">Save</x-ui.button>'))
            ->toContain('wire:loading.attr="aria-busy"')
            ->toContain('wire:target="save"');
    });

    it('renders toggles with aria-pressed and the on state classes', function () {
        expect(kit('<x-ui.button variant="ghost" size="icon-sm" :pressed="true" aria-label="Grid view"><x-ui.icon name="layout-grid" /></x-ui.button>'))
            ->toContain('aria-pressed="true"')
            ->toContain('aria-pressed:bg-surface-muted')
            ->toContain('aria-pressed:border-control');

        expect(kit('<x-ui.button :pressed="false">Focus mode</x-ui.button>'))->toContain('aria-pressed="false"');
    });

    it('disables a link button with aria-disabled and no href', function () {
        $html = kit('<x-ui.button href="/admin/payouts" disabled>Payouts</x-ui.button>');

        expect($html)->toContain('aria-disabled="true"')
            ->toContain('role="link"')
            ->not->toContain('href=');
    });

    it('gives tabs an inset focus-visible ring, hover state and aria-disabled', function () {
        $html = kit('<x-ui.tabs label="Queue" :tabs="[\'a\' => \'All\', \'b\' => [\'label\' => \'Late\', \'disabled\' => true]]" />');

        expect($html)->toContain('focus-ring-inset')
            ->toContain('state-hover:text-ink')
            ->toContain('aria-selected:border-secondary')
            ->toMatch('/disabled aria-disabled="true"/')
            ->toContain('disabled:decoration-dashed')
            ->not->toContain('focus-visible:outline-2')
            ->not->toMatch('/\bopacity-\d/');

        $links = kit('<x-ui.tabs :tabs="[\'a\' => [\'label\' => \'All\', \'href\' => \'/a\'], \'b\' => [\'label\' => \'Late\', \'href\' => \'/b\', \'disabled\' => true]]" active="a" />');
        expect($links)->toContain('aria-disabled="true"')->toContain('aria-current="page"');
    });

    it('lets the gallery force a tab or segment state', function () {
        expect(kit('<x-ui.tabs :tabs="[\'a\' => [\'label\' => \'All\', \'force\' => \'focus\']]" />'))->toContain('data-force="focus"');
        expect(kit('<x-ui.segmented :options="[\'a\' => [\'label\' => \'Day\', \'force\' => \'hover\'], \'b\' => [\'label\' => \'Week\', \'disabled\' => true]]" />'))
            ->toContain('data-force="hover"')
            ->toContain('aria-disabled="true"')
            ->toContain('disabled:decoration-dashed')
            ->not->toMatch('/\bopacity-\d/');
    });
});

describe('loaders', function () {
    it('renders a spinner as a labelled status, silent when told', function () {
        $html = kit('<x-ui.spinner size="lg" label="Loading payouts" />');
        expect($html)->toContain('role="status"')
            ->toContain('<span class="sr-only">Loading payouts</span>')
            ->toContain('width="20"')
            ->toContain('stroke-width="1.5"')
            ->toContain('animate-spinner')
            ->toContain('motion-reduce:animate-spinner-reduced');

        expect(kit('<x-ui.spinner :announce="false" />'))->not->toContain('role="status"')->not->toContain('sr-only');
        expect(kit('<x-ui.loading-inline label="Loading transactions" />'))
            ->toContain('role="status"')
            ->toContain('type-meta')
            ->toContain('Loading transactions');
    });

    it('marks a refreshing region busy after a delay without fading it', function () {
        $html = kit('<x-ui.loading-overlay target="period">Content</x-ui.loading-overlay>');

        expect($html)->toContain('wire:loading.delay.short.attr="aria-busy"')
            ->toContain('wire:loading.delay.short.flex')
            ->toContain('wire:target="period"')
            ->toContain('Content')
            ->not->toMatch('/\bopacity-\d/')
            ->not->toContain('backdrop-blur');

        expect(kit('<x-ui.loading-overlay busy>Content</x-ui.loading-overlay>'))->toContain('aria-busy="true"');
    });

    it('registers the progress bar on Livewire requests and navigation', function () {
        $js = file_get_contents(kit_path('resources/js/ui.js'));

        expect($js)->toContain("window.Alpine.store('progress'")
            ->toContain("window.Livewire.hook('request'")
            ->toContain("'livewire:navigating'")
            ->toContain('PROGRESS_DELAY = 200');

        expect(kit('<x-ui.progress-bar />'))->toContain('data-progress-bar')
            ->toContain('$store.progress.visible')
            ->toContain('bg-chart-1')
            ->toContain('h-0.5')
            ->not->toContain('gradient');

        expect(config('livewire.navigate.show_progress_bar'))->toBeFalse();
    });

    it('renders every skeleton preset as one loading status', function (string $preset) {
        $html = kit("<x-ui.skeleton.{$preset} />");

        expect($html)->toContain('animate-pulse')
            ->toContain('motion-reduce:animate-none')
            ->not->toContain('gradient');

        if ($preset !== 'stats') {
            expect(substr_count($html, 'role="status"'))->toBe(1);
        }
    })->with(['text', 'avatar', 'table', 'stats', 'card', 'chart', 'grid', 'list']);

    it('renders load more with the count and a loading button', function () {
        $html = kit('<x-ui.load-more :shown="20" :total="134" noun="transactions" />');
        expect($html)->toContain('Showing 20 of 134 transactions')
            ->toContain('wire:click="loadMore"')
            ->toContain('wire:target="loadMore"')
            ->toContain('Load more')
            ->not->toContain('x-intersect');

        expect(kit('<x-ui.load-more :shown="20" :total="134" auto />'))->toContain('x-intersect.margin.200px="$wire.loadMore()"');
        expect(kit('<x-ui.load-more :shown="134" :total="134" />'))->not->toContain('Load more');
    });
});

describe('segmented', function () {
    it('matches button and input heights and never wraps', function () {
        $default = kit('<x-ui.segmented label="Period" :options="[\'7\' => \'7d\', \'30\' => \'30d\']" />');
        expect($default)->toMatch('/data-segmented="text"\s+class="[^"]*\bh-9\b/')
            ->toContain('bg-surface-subtle')
            ->toContain('border-line')
            ->toContain('h-7.5')
            ->not->toContain('flex-wrap');

        $small = kit('<x-ui.segmented size="sm" label="View" :options="[\'a\' => [\'label\' => \'Chart\', \'icon\' => \'chart-line\', \'iconOnly\' => true]]" />');
        expect($small)->toMatch('/class="[^"]*\bh-8\b/')->toContain('size-6.5')->not->toContain('flex-wrap');
    });

    it('selects with a white, control-edged segment and no hover box', function () {
        expect(kit('<x-ui.segmented :options="[\'a\' => \'A\']" />'))
            ->toContain('aria-checked:border-control aria-checked:bg-surface')
            ->toContain('state-hover:text-ink')
            ->not->toContain('state-hover:not-aria-checked:bg')
            ->toContain('x-bind:data-segmented-indicator');
    });

    it('marks disabled segments without opacity', function () {
        $html = kit('<x-ui.segmented :options="[\'a\' => \'A\', \'b\' => [\'label\' => \'B\', \'disabled\' => true]]" />');

        expect($html)->toContain('disabled aria-disabled="true"')
            ->toContain('disabled:decoration-dashed')
            ->toContain('disabled:cursor-not-allowed')
            ->not->toMatch('/\bopacity-\d/');
    });

    it('falls back to a select with the same binding below sm', function () {
        $html = kit('<x-ui.segmented responsive label="Platform" wire:model.live="platform" :options="[\'spotify\' => \'Spotify\', \'boomplay\' => \'Boomplay\']" />');

        expect($html)->toContain('data-segmented-select')
            ->toContain('sm:hidden')
            ->toContain('max-sm:hidden')
            ->toContain('<div role="option" data-value="boomplay">Boomplay</div>')
            ->and(substr_count($html, 'wire:model.live="platform"'))->toBe(2);
    });
});
