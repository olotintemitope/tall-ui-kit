<?php

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;

// Custom selects, menus and popovers open below their trigger and never flip above it
// (resources/js/placement.js scrolls to make room instead). There is no native <select>
// anywhere (its popup is the browser's and cannot be kept below or styled): every
// dropdown is x-ui.select, x-ui.combobox, x-ui.dropdown or x-ui.popover. An explicit top
// placement (the sidebar account menu) stays top.

// Files allowed to contain a native <select>, each with the reason. Keep it empty.
const NATIVE_SELECT_ALLOWLIST = [];

it('keeps a dropdown placed below from flipping above', function (string $props, string $anchor) {
    $html = Blade::render("<x-ui.dropdown {$props}><x-slot:trigger><button>Open</button></x-slot:trigger><x-ui.dropdown.item>One</x-ui.dropdown.item></x-ui.dropdown>");

    expect($html)->toContain($anchor)
        ->and($html)->toContain('$revealBelow($el');
})->with([
    'default (right)' => ['', 'x-anchor.bottom-end.noflip.offset.6'],
    'align left' => ['align="left"', 'x-anchor.bottom-start.noflip.offset.6'],
    'bottom' => ['placement="bottom"', 'x-anchor.bottom.noflip.offset.6'],
]);

it('lets an explicit top placement stay above', function () {
    $html = Blade::render('<x-ui.dropdown placement="top-start"><x-slot:trigger><button>Open</button></x-slot:trigger><x-ui.dropdown.item>One</x-ui.dropdown.item></x-ui.dropdown>');

    expect($html)->toContain('x-anchor.top-start.offset.6')->not->toContain('noflip');
});

it('keeps a popover placed below from flipping above', function () {
    $html = Blade::render('<x-ui.popover label="Details">Body</x-ui.popover>');

    expect($html)->toContain('x-anchor.bottom-start.noflip.offset.6')
        ->and($html)->toContain('$revealBelow($el');
});

it('reveals the combobox list below its input', function () {
    $html = Blade::render('<x-ui.combobox label="Genre" :options="[\'afro\' => \'Afrobeats\']" />');

    expect($html)->toContain('top-full')->and($html)->toContain('$revealBelow($el');
});

it('anchors the x-ui.select list below its trigger, fixed so nothing clips it', function () {
    $html = Blade::render('<x-ui.select aria-label="Status" :options="[\'a\' => \'Active\']" />');

    expect($html)->toContain('x-anchor.bottom-start.noflip.offset.6.fixed="$refs.trigger"')
        ->toContain('role="combobox"')->toContain('aria-haspopup="listbox"')->toContain('role="listbox"')
        ->not->toContain('<select');
    expect(file_get_contents(kit_path('resources/js/select.js')))->toContain('this.$revealBelow(popup, trigger)');
});

it('has no native <select> in any view', function () {
    $offenders = collect(File::allFiles(kit_path('resources/views')))
        ->filter(fn ($file) => str_ends_with($file->getFilename(), '.php'))
        ->reject(fn ($file) => in_array($file->getRelativePathname(), NATIVE_SELECT_ALLOWLIST, true))
        // Blade comments may name it ("no native <select>"); markup may not.
        ->filter(fn ($file) => preg_match('/<select[\s>\/]/i', preg_replace('/\{\{--.*?--\}\}/s', '', $file->getContents())))
        ->map(fn ($file) => $file->getRelativePathname())
        ->values()
        ->all();

    expect($offenders)->toBe([], 'Use x-ui.select (docs/ui-kit.md); a native <select> is not allowed.');
});

it('has no custom popup that can flip above unless placed on top on purpose', function () {
    // Tooltips and the "Copied" bubble are hints, not popups you choose from: they may flip.
    $hints = ['components/ui/tooltip.blade.php', 'components/ui/copy-button.blade.php', 'components/ui/copy-text.blade.php'];

    $offenders = collect(File::allFiles(kit_path('resources/views')))
        ->filter(fn ($file) => str_ends_with($file->getFilename(), '.blade.php'))
        ->reject(fn ($file) => in_array($file->getRelativePathname(), $hints, true))
        ->flatMap(function ($file) {
            preg_match_all('/x-anchor\.(bottom[a-z-]*)((?:\.[a-z0-9-]+)*)=/', $file->getContents(), $matches, PREG_SET_ORDER);

            return collect($matches)
                ->reject(fn ($m) => str_contains($m[2], 'noflip'))
                ->map(fn ($m) => $file->getRelativePathname().': '.$m[0]);
        })
        ->values()
        ->all();

    expect($offenders)->toBe([]);
});
