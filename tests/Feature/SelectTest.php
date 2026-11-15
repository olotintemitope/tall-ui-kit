<?php

use Illuminate\Support\Facades\Blade;

// x-ui.select: the APG select-only combobox that replaced the native <select>
// (docs/ui-kit.md "Select"). Behaviour in the browser lives in resources/js/select.js.

it('renders a combobox button, a listbox and options wired to the field', function () {
    $html = Blade::render('<x-ui.field label="Payout day" name="payoutDay" hint="Weekly." required><x-ui.select wire:model.live="payoutDay" placeholder="Choose a day" :options="[\'mon\' => \'Monday\', \'fri\' => \'Friday\']" /></x-ui.field>');

    expect($html)
        ->toMatch('/<div class="group\/select[^"]*"[^>]*wire:model.live="payoutDay"[^>]*x-data="uiSelect\(\'\', false\)" x-modelable="value"/')
        ->toContain('<button type="button"')->toContain('id="field-payoutday" role="combobox" aria-haspopup="listbox" aria-expanded="false" aria-controls="field-payoutday-listbox"')
        ->toContain('aria-required="true"')->toContain('aria-describedby="field-payoutday-hint"')
        ->toContain('<div role="listbox" id="field-payoutday-listbox"  aria-labelledby="field-payoutday-label"')
        ->toContain('<div role="option" data-value="" aria-selected="true">Choose a day</div>')
        ->toContain('<div role="option" data-value="fri">Friday</div>')
        ->toContain('x-anchor.bottom-start.noflip.offset.6.fixed')
        ->not->toContain('<select')->not->toContain('<option');
});

it('marks invalid selects and passes attributes to the trigger and the binding to the root', function () {
    $html = Blade::render('<x-ui.field label="Bank" name="bank" error="Choose a bank."><x-ui.select wire:model="bank" aria-label="Payout bank" data-fk="bank" x-on:change="touched = true" :options="[\'gtb\' => \'GTBank\']" /></x-ui.field>');

    expect($html)->toContain('aria-invalid="true"')->toContain('aria-describedby="field-bank-error"')->toContain('border-red-600')
        ->toMatch('/<button[^>]*aria-label="Payout bank"[^>]*data-fk="bank"[^>]*x-on:change="touched = true"/')
        ->toContain('aria-label="Payout bank" ')->toMatch('/<div role="listbox"[^>]*aria-label="Payout bank"/')
        ->and(substr_count($html, 'wire:model="bank"'))->toBe(1);
});

it('renders groups, disabled options and options passed in the slot', function () {
    $groups = Blade::render('<x-ui.select name="g" selected="b" :options="[\'Group\' => [\'a\' => \'A\', [\'value\' => \'b\', \'label\' => \'B\']], [\'value\' => \'c\', \'label\' => \'C\', \'disabled\' => true]]" />');

    expect($groups)->toContain('<div role="group" aria-labelledby="field-g-g0"><div role="presentation" id="field-g-g0" class="type-label px-2 pb-1 pt-2">Group</div>')
        ->toContain('<div role="option" data-value="b" aria-selected="true">B</div>')
        ->toContain('<div role="option" data-value="c" aria-disabled="true">C</div>')
        ->toMatch('/data-select-label[^>]*>B<\/span>/');

    $slot = Blade::render('<x-ui.select name="s"><option value="">Pick</option><option value="x" selected>Ex &amp; Why</option><option value="y" disabled>Why</option></x-ui.select>');

    expect($slot)->toContain('<div role="option" data-value="x" aria-selected="true">Ex &amp; Why</div>')
        ->toContain('<div role="option" data-value="y" aria-disabled="true">Why</div>')
        ->toContain('<input type="hidden" name="s" value="x"');
});

it('shows the first option, as a native select did, when nothing matches and there is no placeholder', function () {
    $html = Blade::render('<x-ui.select name="currency" selected="eur" :options="[\'ngn\' => \'Naira\', \'usd\' => \'Dollar\']" />');

    expect($html)->toMatch('/data-select-label[^>]*>Naira<\/span>/')->toContain('<div role="option" data-value="ngn" aria-selected="true">Naira</div>')
        ->toContain('<input type="hidden" name="currency" value="ngn"');
});

it('posts plain forms through hidden inputs, one per value with multiple, and a required check', function () {
    $single = Blade::render('<x-ui.select name="role" selected="admin" :options="[\'admin\' => \'Admin\', \'finance\' => \'Finance\']" disabled />');
    expect($single)->toMatch('/<span hidden data-select-inputs data-name="role"><input type="hidden" name="role" value="admin"\s+disabled\s+data-select-input><\/span>/')
        ->toMatch('/<button type="button"[^>]*disabled/');

    $many = Blade::render('<x-ui.select name="genres[]" multiple :selected="[\'a\', \'c\']" placeholder="Any genre" :options="[\'a\' => \'A\', \'b\' => \'B\', \'c\' => \'C\']" />');
    expect($many)->toContain('<input type="hidden" name="genres[]" value="a"')->toContain('<input type="hidden" name="genres[]" value="c"')
        ->not->toContain('value="b"  ')->toContain('data-none="Any genre"')->toContain('x-data="uiSelect(JSON.parse(\'[\u0022a\u0022,\u0022c\u0022]\'), true)"');

    $required = Blade::render('<x-ui.select name="bank" required placeholder="Choose" :options="[\'gtb\' => \'GTBank\']" />');
    expect($required)->toContain('<input type="text" name="bank" value="" required tabindex="-1" aria-hidden="true"')->toContain('x-on:invalid.prevent="invalidated()"');
});

it('keeps each option small and starts no Alpine per option', function () {
    $options = collect(range(1, 200))->mapWithKeys(fn ($i) => ["v{$i}" => "Option {$i}"])->all();
    $html = Blade::render('<x-ui.select name="many" :options="$options" />', ['options' => $options]);

    preg_match('/<div role="listbox"[^>]*>(.*)<\/div>\s*<\/div>\s*<\/div>\s*$/s', $html, $list);
    expect($html)->toMatch('/<div role="listbox"[^>]*x-ignore/')
        ->and(substr_count($list[1], 'x-'))->toBe(0)
        ->and(strlen($list[1]) / 200)->toBeLessThan(55);
});

it('puts the select behaviour in its own module, loaded with the kit', function () {
    $js = file_get_contents(kit_path('resources/js/select.js'));

    expect(file_get_contents(kit_path('resources/js/ui.js')))->toContain("import './select';")
        ->and($js)->toContain("Alpine.data('uiSelect'")->toContain("case 'PageDown'")->toContain("case 'Tab'")->toContain("case 'Escape'")
        ->toContain('aria-activedescendant')->toContain("Livewire.hook('morphed'")->toContain('$revealBelow(popup, trigger)');
});
