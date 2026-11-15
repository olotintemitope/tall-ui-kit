<?php

use Illuminate\Support\Facades\Blade;
use TallUiKit\Support\FormControl;

/*
| Fixes from the 2026-11 consistency and accessibility audit (docs/audit-2026-11.md):
| one size scale, backward-compatible prop aliases, and the keyboard and naming fixes
| that can be checked in the rendered markup.
*/

describe('one size scale', function () {
    it('maps every size name to 32, 36 or 40px', function () {
        expect(FormControl::height('sm'))->toBe('h-8')
            ->and(FormControl::height('default'))->toBe('h-9')
            ->and(FormControl::height('md'))->toBe('h-9')
            ->and(FormControl::height('lg'))->toBe('h-10')
            ->and(FormControl::height('nonsense'))->toBe('h-9')
            ->and(FormControl::height('lg', true))->toBe('min-h-10');
    });

    it('gives inputs, selects, pickers and buttons the same large height', function (string $blade, string $class) {
        expect(Blade::render($blade))->toContain($class);
    })->with([
        'input' => ['<x-ui.input size="lg" aria-label="Name" />', 'h-10'],
        'select' => ['<x-ui.select size="lg" aria-label="Day" :options="[\'mon\' => \'Monday\']" />', 'min-h-10'],
        'combobox' => ['<x-ui.combobox size="lg" label="Bank" :options="[\'gtb\' => \'GTBank\']" />', 'h-10'],
        'number' => ['<x-ui.number-input size="lg" label="Seats" />', 'h-10'],
        'tags' => ['<x-ui.tag-input size="lg" label="Tags" />', 'min-h-10'],
        'colour' => ['<x-ui.color size="lg" label="Brand colour" />', 'h-10'],
        'date' => ['<x-ui.date size="lg" aria-label="Due" />', 'ui-date-lg'],
        'button' => ['<x-ui.button size="lg">Save</x-ui.button>', 'min-h-10'],
        'copy' => ['<x-ui.copy-button size="lg" value="TXN-1" />', 'min-h-10'],
    ]);

    it('reads md as default on buttons', function () {
        expect(Blade::render('<x-ui.button size="md">Save</x-ui.button>'))->toContain('min-h-9');
    });
});

describe('prop aliases', function () {
    it('accepts tone on badges and variant on alerts, and error as danger', function () {
        expect(Blade::render('<x-ui.badge tone="success">Paid</x-ui.badge>'))->toContain('bg-success-tint')
            ->and(Blade::render('<x-ui.badge variant="error">Failed</x-ui.badge>'))->toContain('bg-danger-tint')
            ->and(Blade::render('<x-ui.alert variant="warning">Check</x-ui.alert>'))->toContain('data-alert="warning"')
            ->and(Blade::render('<x-ui.alert tone="info">Note</x-ui.alert>'))->toContain('bg-info-tint')
            ->and(Blade::render('<x-ui.toast tone="danger" title="Failed" />'))->toContain('bg-danger-tint');
    });

    it('accepts hint and error on a checkbox and error on a radio group', function () {
        $box = Blade::render('<x-ui.checkbox name="terms" label="Accept" hint="Required to continue" error="Accept the terms." />');
        $radios = Blade::render('<x-ui.radio-group name="plan" :options="[\'a\' => \'A\']" error="Pick a plan." />');

        expect($box)->toContain('Required to continue')->toContain('Accept the terms.')->toContain('aria-invalid="true"')
            ->and($radios)->toContain('Pick a plan.');
    });

    it('accepts placement on tooltips, headingLevel on notification groups and label on fieldsets', function () {
        expect(Blade::render('<x-ui.tooltip text="Hi" placement="bottom">x</x-ui.tooltip>'))->toContain('x-anchor.bottom')
            ->and(Blade::render('<x-ui.notification-group label="Today" :heading-level="2">x</x-ui.notification-group>'))->toContain('<h2')
            ->and(Blade::render('<x-ui.fieldset label="Payout" hide-label>x</x-ui.fieldset>'))->toContain('sr-only')->toContain('Payout');
    });
});

describe('accessibility fixes', function () {
    it('names a checkbox by its label alone when it has a description', function () {
        $html = Blade::render('<x-ui.checkbox id="news" label="Newsletter" description="Once a week." />');

        expect($html)->toContain('aria-labelledby="news-label"')->toContain('aria-describedby="news-description"');
    });

    it('keeps Escape inside an open combobox and search from closing a dialog', function () {
        expect(file_get_contents(dirname(__DIR__, 2).'/resources/js/forms.js'))->toContain("event.preventDefault();\n                event.stopPropagation();\n                this.close();")
            ->and(Blade::render('<x-ui.search />'))->toContain('$event.preventDefault(); $event.stopPropagation(); clear()');
    });

    it('describes the control, not the wrapper, with a tooltip and lets it be hovered', function () {
        $html = Blade::render('<x-ui.tooltip text="Exports as CSV"><button type="button">Export</button></x-ui.tooltip>');

        expect($html)->toContain('describe()')->not->toContain('pointer-events-none')
            ->and($html)->toContain('if (open) { $event.preventDefault()');
    });

    it('gives sortable rows Move up and Move down buttons', function () {
        $html = Blade::render('<x-ui.sortable :items="[[\'key\' => \'a\', \'label\' => \'Bank\'], [\'key\' => \'b\', \'label\' => \'Card\']]" />');

        expect($html)->toContain('aria-label="Move Bank up"')->toContain('data-sortable-move="down"');
    });

    it('lets page headers wrap their actions and take a heading level', function () {
        $html = Blade::render('<x-ui.page-header title="Payouts" :level="2"><x-slot:actions><x-ui.button>Export</x-ui.button></x-slot:actions></x-ui.page-header>');

        expect($html)->toContain('<h2')->toContain('flex-wrap');
    });

    it('hides inactive tab panels from the first paint', function () {
        $html = Blade::render('<x-ui.tabs :tabs="[\'a\' => \'One\', \'b\' => \'Two\']"><x-ui.tabs.panel name="a">A</x-ui.tabs.panel><x-ui.tabs.panel name="b">B</x-ui.tabs.panel></x-ui.tabs>');

        expect(substr_count($html, 'style="display: none"'))->toBe(1);
    });

    it('never disables pagination buttons while a page loads', function () {
        expect(file_get_contents(dirname(__DIR__, 2).'/resources/views/components/ui/pagination.blade.php'))->not->toContain('wire:loading.attr="disabled"');
    });

    it('gives a second one-time code its own id', function () {
        $html = Blade::render('<x-ui.otp /><x-ui.otp />');

        expect($html)->toContain('id="field-code"')->toContain('field-code-2');
    });
});
