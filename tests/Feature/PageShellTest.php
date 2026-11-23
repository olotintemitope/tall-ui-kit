<?php

use Illuminate\Support\Facades\Blade;

describe('x-ui.page', function () {
    it('draws one tinted page with one centred column', function () {
        $html = Blade::render('<x-ui.page><p>Body</p></x-ui.page>');

        expect($html)
            ->toContain('data-page')
            ->toContain('bg-page-muted')
            ->toContain('data-page-shell="full"')
            ->toContain('max-w-7xl')
            ->toContain('gap-4')
            ->not->toContain('max-w-3xl');
    });

    it('narrows to the form column', function () {
        $html = Blade::render('<x-ui.page width="form"><p>Body</p></x-ui.page>');

        expect($html)->toContain('data-page-shell="form"')->toContain('max-w-3xl')->not->toContain('max-w-7xl');
    });

    it('passes attributes to the column', function () {
        $html = Blade::render('<x-ui.page wire:poll.15s="refresh" class="extra"><p>Body</p></x-ui.page>');

        expect($html)->toMatch('/<main[^>]*wire:poll\.15s="refresh"/')->toMatch('/<main[^>]*class="[^"]*extra/');
    });

    it('renders a single root element, so it can be a Livewire root', function () {
        $html = trim(Blade::render('<x-ui.page><p>Body</p></x-ui.page>'));
        $dom = new DOMDocument;
        @$dom->loadHTML('<body>'.$html.'</body>');
        $roots = array_filter(
            iterator_to_array($dom->getElementsByTagName('body')->item(0)->childNodes),
            fn ($node) => $node->nodeType === XML_ELEMENT_NODE,
        );

        expect($roots)->toHaveCount(1);
    });
});

describe('x-ui.page-header portal additions', function () {
    it('draws breadcrumbs above the header, 12px apart', function () {
        $html = Blade::render('<x-ui.page-header title="Payouts" :breadcrumbs="[[\'label\' => \'Dashboard\', \'href\' => \'/\'], [\'label\' => \'Payouts\']]" />');

        expect($html)->toContain('grid min-w-0 gap-3')->toContain('Dashboard')->toContain('<h1');
        expect(strpos($html, 'Dashboard'))->toBeLessThan(strpos($html, '<h1'));
    });

    it('boxes the title on a raised surface without its own bottom padding', function () {
        $html = Blade::render('<x-ui.page-header title="Payouts" boxed />');

        expect($html)->toContain('rounded-lg bg-card p-4 shadow-panel sm:p-5')->not->toMatch('/class="[^"]*\bpb-4\b/');
    });

    it('keeps the plain header as it was', function () {
        $html = Blade::render('<x-ui.page-header title="Payouts" />');

        expect($html)->toContain('pb-4')->not->toContain('bg-card')->not->toContain('data-page-header-actions');
    });

    it('puts overflow actions in an ellipsis menu before the actions', function () {
        $html = Blade::render('<x-ui.page-header title="Courses"><x-slot:more><x-ui.dropdown.item>Export</x-ui.dropdown.item></x-slot:more><x-slot:actions><x-ui.button>Create</x-ui.button></x-slot:actions></x-ui.page-header>');

        expect($html)->toContain('aria-label="More actions"')->toContain('Export')->toContain('Create');
        expect(strpos($html, 'More actions'))->toBeLessThan(strpos($html, 'Create'));
    });

    it('keeps actions at their natural width from sm so they do not wrap on tablets', function () {
        $html = Blade::render('<x-ui.page-header title="A very long title" description="A long description"><x-slot:actions><x-ui.button>One</x-ui.button><x-ui.button>Two</x-ui.button></x-slot:actions></x-ui.page-header>');

        expect($html)->toContain('flex-wrap')->toContain('sm:shrink-0')->toContain('min-w-0 sm:flex-1');
    });
});
