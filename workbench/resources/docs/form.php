<?php

return [
    'intro' => 'The form wrapper and the parts of a settings page: a tinted `x-ui.form-page`, `x-ui.form-section`s with a raised `x-ui.panel` each, and `x-ui.form-actions`.',
    'keyboard' => [
        ['Enter', 'In a single-line field, submits the form (native behaviour).'],
    ],
    'a11y' => [
        'After a submit that comes back with errors, focus moves to `x-ui.error-summary` when more than 3 fields fail, otherwise to the first `aria-invalid` field, scrolled clear of the top bar and the sticky actions.',
        'After a successful submit focus stays on the submit button; confirm with a toast, which never takes focus.',
        'Each `x-ui.form-section` title is an `h2`, so a settings page has a usable heading outline.',
        'The form sets `novalidate`: the server decides, and its messages reach the fields through `x-ui.field`.',
    ],
    'events' => [
        'Bind as usual: `<x-ui.form wire:submit="save">`. Any `input` or `change` inside marks it dirty; `$dispatch(\'form-reset\')` (Discard) and a successful Livewire submit clear it.',
        '`guard`: while dirty, closing or reloading the tab asks the browser\'s own question, and a `wire:navigate` link opens the kit confirm "Leave without saving?".',
        'The state is mirrored as `data-dirty` on the `<form>` for the guard and for tests. `dirty` keeps it dirty from the start, for values that live only in the page.',
    ],
    'do' => [
        'Use `x-ui.form-page` (or the layout\'s `surface="muted"`) so panels never sit on plain white.',
        'Reset the values yourself on Discard (`$this->reset()` or a reload): `form-reset` only clears the dirty flag.',
        'Use `x-ui.form-actions sticky` on long settings pages, so Save is always one key away.',
    ],
    'dont' => [
        'Put an `x-ui.panel` inside a dialog or sheet, or nest panels.',
        'Put an `id` on `x-ui.form-section` or `x-ui.panel`: the controls inside pick it up. Put anchors on a wrapping element.',
        'Disable Save until the form is valid: let people submit and show what to fix.',
    ],
];
