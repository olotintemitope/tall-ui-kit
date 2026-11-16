<?php

return [
    'intro' => 'Single-line text fields: `x-ui.input` for any text type, `x-ui.tel` for phone numbers, `x-ui.password` with a show and hide toggle, and `x-ui.search` with a clear button.',
    'keyboard' => [
        ['Escape', 'In `x-ui.search` with text, clears it (and fires `input`, so bindings update).'],
        ['Enter / Space', 'On the password\'s "Show password" button, shows or hides the password; focus stays on the button.'],
    ],
    'a11y' => [
        'Each `type` gets the right `inputmode`, `autocomplete`, `autocapitalize` and `spellcheck` by default; override any of them as an attribute.',
        'Prefix and suffix text sit inside the border and are not part of the value; say the unit in the label or hint too when it matters.',
        '`x-ui.tel` with `country` adds the dialling code to the field\'s description, so it is read with the number.',
        'The password toggle is a real button with `aria-pressed` and a fixed name; the `strength` hint is a polite caption that never blocks submit.',
        '`x-ui.search` on its own is named by `aria-label` (or its placeholder); inside `x-ui.field` the label names it.',
    ],
    'events' => [
        'Attributes such as `wire:model`, `x-model`, `placeholder` and `disabled` go to the `<input>`. On its own, an input looks up `$errors` by `name` or `wire:model`.',
        'Use `wire:model.live.debounce.300ms` on search boxes whose results update live.',
    ],
    'do' => [
        'Pick a `width` that fits the expected value (`xs` for a short code, `full` for an email); inputs default to `md`.',
        'Use `size="sm"` only in dense toolbars and filter rows.',
        'Give `x-ui.search` a `short-placeholder` so it never cuts off at 320px.',
    ],
    'dont' => [
        'Use `type="date"`, `time`, `month`, `week` or `datetime-local`: use the kit\'s date and time pickers.',
        'Use `type="number"` for money or quantities: use `x-ui.number-input`.',
        'Block paste in password fields.',
    ],
];
