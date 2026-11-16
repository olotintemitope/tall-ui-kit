<?php

return [
    'keyboard' => [
        ['Up / Down', 'Steps the value by `step`.'],
        ['Page Up / Page Down', 'Steps by ten times `step`.'],
        ['Home / End', 'Jumps to `min` or `max`, when set.'],
        ['Tab', 'Leaving the field clamps the typed value to the bounds and rounds it to `step`.'],
    ],
    'a11y' => [
        'The field is `role="spinbutton"` with the current value, min and max; money and percent values are read as formatted text.',
        'The minus and plus buttons are named "Decrease" and "Increase" (with `noun`: "Decrease seats"), step without taking focus from a keyboard user, and turn `aria-disabled` at a bound.',
        'The browser\'s own spin buttons never show; the buttons are 32px wide and the control\'s full height.',
        'Outside `x-ui.field`, name it with `label`.',
    ],
    'events' => [
        'Bind `wire:model` or `x-model` on the component: a number or null. `.live.debounce` works as on an input.',
    ],
    'do' => [
        'Use `format="money"` for amounts: grouped with the sign on blur ("₦150,000"), plain while editing.',
        'Set `min`, `max` and `step` to the rules the server checks, and still validate on the server.',
    ],
    'dont' => [
        'Use it for numbers that are really identifiers (account numbers, phone numbers, PINs): use `x-ui.input` or `x-ui.otp`.',
        'Use `<input type="number">`.',
    ],
];
