<?php

use Illuminate\Support\Facades\Blade;

/* Form layouts (docs/ui-kit-forms.md) on /admin/ui/forms. Keyboard, focus and the guard are checked in the browser. */

it('builds the layout components', function () {
    $single = Blade::render('<x-ui.form-layout.single title="Request a payout">x<x-slot:footer>f</x-slot:footer></x-ui.form-layout.single>');
    expect($single)->toContain('max-w-[640px]')->toContain('data-panel')->toContain('data-form-layout="single"');

    expect(Blade::render('<x-ui.form-layout.split variant="preview">f<x-slot:aside>p</x-slot:aside></x-ui.form-layout.split>'))
        ->toContain('lg:grid-cols-[minmax(0,1fr)_400px]')->toContain('lg:sticky')->toContain('aria-label="Preview"');

    expect(Blade::render('<x-ui.form-layout.section-nav :sections="[\'a\' => \'About\']">x</x-ui.form-layout.section-nav>'))
        ->toContain('x-data="uiSectionNav"')->toContain('href="#a"')->toContain('aria-current')->toContain('lg:sticky');

    expect(Blade::render('<x-ui.field inline label="Channel" name="channel" hint="How"><x-ui.input /></x-ui.field>'))
        ->toContain('sm:grid-cols-[220px_minmax(0,1fr)]')->toContain('for="field-channel"')->toContain('id="field-channel"')->toContain('aria-describedby="field-channel-hint"');

    $form = Blade::render('<x-ui.form guard dirty>x</x-ui.form>');
    expect($form)->toContain('data-unsaved-guard')->toContain('data-keep-dirty')->toContain('data-dirty="true"')
        ->toContain('Leave without saving?')->toContain('Leave page');
});
