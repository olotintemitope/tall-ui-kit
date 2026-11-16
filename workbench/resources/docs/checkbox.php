<?php

return [
    'keyboard' => [
        ['Space', 'Toggles the checkbox.'],
        ['Tab', 'Moves to the next checkbox; each one is its own tab stop.'],
    ],
    'a11y' => [
        'A native `<input type="checkbox">` named by its label; the description is linked with `aria-describedby`, so it is read once, after the name.',
        'The whole row is the click target, not just the 16px box.',
        '`indeterminate` sets the native mixed state, announced as "mixed"; bind `x-effect="$el.indeterminate = ..."` for a live value.',
        'Disabled boxes keep a dashed border and `ink-muted` text, never faded. In forced colours the native control shows.',
        'Errors are found by `name` or `wire:model` and linked with `aria-invalid` and `aria-describedby`.',
    ],
    'events' => [
        'Attributes (`wire:model`, `x-model`, `value`, `checked`, `disabled`, `name`) go to the `<input>`. Bind several boxes with the same `wire:model` and different `value`s to an array.',
    ],
    'do' => [
        'Write the label as the thing that becomes true when checked ("Email me a weekly summary").',
        'Use `x-ui.fieldset` to name a group of checkboxes that answer one question.',
        'Use a checkbox for a choice that is saved with the form; use a switch for a setting that applies at once.',
    ],
    'dont' => [
        'Use a single checkbox for a choice between two named options: use a radio group.',
        'Put links inside the label row where a click would also toggle the box; put them in the description below instead.',
    ],
];
