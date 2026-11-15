<?php

use Illuminate\Support\Facades\Blade;

beforeEach(function () {
    $this->withoutVite();
});

/*
| x-ui.copy-button, x-ui.copy-field and x-ui.copy-text (docs/ui-kit-copy-icons.md).
| The clipboard behaviour (uiCopy in resources/js/ui.js) is checked in the browser;
| these pin the markup it relies on.
*/

it('renders a copy button with both icons stacked, every label reserved and a polite status', function () {
    $html = Blade::render('<x-ui.copy-button value="NB-CMP-048217" />');

    expect($html)->toContain('x-data="uiCopy(')
        ->toContain('NB-CMP-048217')
        ->toContain('x-on:click="copy()"')
        ->toContain('type="button"')
        ->toContain('data-copy-state="idle"')
        // Icons: copy and check in one cell, an opacity cross-fade of 150ms, none under reduced motion.
        ->toMatch('/data-copy-icons/')
        ->toContain('[&>svg]:[grid-area:1/1]')
        ->toContain('transition-opacity duration-150 motion-reduce:transition-none')
        ->toContain('<rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>')
        ->toContain('<path d="M20 6 9 17l-5-5"/>')
        // No width shift: Copy and Copied share one grid cell sized for "Copied"; the failure hint is anchored out of the flow.
        ->toContain('class="inline-grid text-left [&>span]:[grid-area:1/1]" data-copy-labels')
        ->toContain('>Copy</span>')
        ->toContain('>Copied</span>')
        ->toContain('x-bind:class="{ invisible: ! copied }"')
        ->toMatch('/<span class="inline-grid[^"]*" data-copy-labels>(?:(?!<\/button>).)*<\/span>\s*<\/button>/s')
        ->not->toMatch('/data-copy-labels>(?:(?!<\/button>).)*Press/s')
        // Failure guidance: a fixed hint anchored under the button, shown only while failed, describing the button.
        ->toContain('data-copy-hint')
        ->toContain('x-show="failed"')
        ->toContain('x-anchor.bottom-start.offset.6.fixed')
        ->toContain('Press <span x-text="shortcut">Ctrl+C</span> to copy')
        ->toContain("setAttribute('aria-describedby', \$id('copy-hint'))")
        // Status and the copied colour.
        ->toMatch('/<span class="sr-only" aria-live="polite" x-text="message" data-copy-status><\/span>/')
        ->toContain('&#039;text-success-text&#039;: copied')
        ->not->toContain('opacity-50')
        ->not->toContain('toast');
});

it('carries the failure fallback: a hidden selectable span next to the button', function () {
    $html = Blade::render('<x-ui.copy-button value="NB-1" />');

    expect($html)->toMatch('/<span x-ref="selectable" class="sr-only" aria-hidden="true" data-copy-selectable><\/span>/');

    $field = Blade::render('<x-ui.copy-field text="https://newbanger.com/r/ada" label="Referral link" />');
    expect($field)->toContain('x-ref="field"')
        ->toContain('readonly')
        ->toContain('value="https://newbanger.com/r/ada"')
        ->toContain('aria-label="Referral link"')
        ->toContain('data-copy-selectable')
        ->toContain('aria-live="polite"');
});

it('names an icon-only button and keeps its tooltip in step with the state', function () {
    $html = Blade::render('<x-ui.copy-button value="NB-1" variant="icon" size="sm" label="Copy reference" />');

    expect($html)->toContain('aria-label="Copy reference"')
        ->toContain('size-8')
        ->toContain('role="tooltip"')
        ->toContain("x-text=\"copied ? 'Copied' : (failed ? 'Press ' + shortcut + ' to copy' : 'Copy reference')\"")
        ->not->toContain('data-copy-labels');
});

it('matches the button sizes', function () {
    expect(Blade::render('<x-ui.copy-button value="a" />'))->toContain('h-9 px-3 text-body-strong');
    expect(Blade::render('<x-ui.copy-button value="a" size="sm" />'))->toContain('h-8 px-2.5 text-meta font-medium');
    expect(Blade::render('<x-ui.copy-button value="a" variant="icon" />'))->toContain('size-9');
    expect(Blade::render('<x-ui.copy-button value="a" variant="link" />'))->toContain('text-primary-text')->toContain('state-hover:underline');
});

it('draws forced states for the gallery and never copies from them', function () {
    $copied = Blade::render('<x-ui.copy-button value="a" data-force="copied" />');
    expect($copied)->toContain('data-copy-state="copied"')
        ->toContain('text-success-text')
        ->toContain('\u0022force\u0022:\u0022copied\u0022')
        ->toContain('<span class="" x-bind:class="{ invisible: ! copied }">Copied</span>');

    $failedButton = Blade::render('<x-ui.copy-button value="a" data-force="failed" />');
    expect($failedButton)->toContain('data-copy-state="failed"')
        ->toContain('x-bind:class="{ invisible: copied }">Copy</span>')
        ->toMatch('/<span\s+x-show="failed"\s+x-anchor/')
        ->toContain('data-copy-hint');

    $failed = Blade::render('<x-ui.copy-text label="Copy reference" data-force="failed">NB-1</x-ui.copy-text>');
    expect($failed)->toContain('data-copy-state="failed"')
        ->toContain('Press Ctrl+C to copy')
        ->not->toMatch('/class="[^"]*\bopacity-0 group-hover/');
});

it('copies the visible text unless the page passes a value', function () {
    $masked = Blade::render('<x-ui.copy-text label="Copy account number">•••• 4821</x-ui.copy-text>');
    expect($masked)->toContain('x-ref="value"')
        ->toContain('•••• 4821')
        ->toContain('x-data="uiCopy({})"');

    $explicit = Blade::render('<x-ui.copy-field text="whsec_••••3f9a" value="whsec_demo_full" label="Secret" />');
    expect($explicit)->toContain('value="whsec_••••3f9a"')
        ->toContain('whsec_demo_full');
});

it('keeps the inline button in the tab order and its space reserved', function () {
    $html = Blade::render('<x-ui.copy-text label="Copy reference" mono>NB-PAY-000913</x-ui.copy-text>');

    expect($html)->toContain('<button')
        ->not->toContain('tabindex="-1"')
        ->not->toMatch('/<button[^>]*\shidden[\s>]/')
        // Lean: one class per part, styles in app.css (.ui-copy-inline is 24px and shows on hover, focus, a state or touch).
        ->toContain('class="ui-copy-inline focus-ring"')
        ->toContain('class="min-w-0 wrap-anywhere font-mono text-meta"')
        ->toContain('x-anchor.top.offset.6.fixed')
        ->not->toContain('truncate');
    expect(strlen($html))->toBeLessThan(1400);
    expect(file_get_contents(kit_path('resources/css/tall-ui-kit.css')))->toContain('.ui-copy-inline {')->toContain('width: 24px')->toContain('@media (hover: none)');
});

it('adds a toast only when asked', function () {
    expect(Blade::render('<x-ui.copy-button value="a" toast />'))->toContain('\u0022toast\u0022:true');
});

it('joins the field button inside the border with an inset focus ring', function () {
    $html = Blade::render('<x-ui.copy-field text="NB-1" label="Reference" />');

    expect($html)->toContain('data-copy-field')
        ->toContain('overflow-hidden rounded-md border border-control')
        ->toContain('border-l border-control focus-ring-inset')
        ->toContain('data-copy-labels');
});
