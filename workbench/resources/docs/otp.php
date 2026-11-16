<?php

return [
    'keyboard' => [
        ['0 to 9', 'Fills the box and moves to the next one.'],
        ['Backspace', 'In an empty box, goes back and clears the previous box.'],
        ['Left / Right', 'Moves between boxes.'],
        ['Paste', 'A pasted code (or the phone\'s one-time-code autofill in the first box) spreads across the boxes.'],
    ],
    'a11y' => [
        'The boxes sit in a `<fieldset>` whose `<legend>` is `label`; each box has its own name ("Digit 2 of 6").',
        'The first box has `autocomplete="one-time-code"`, so iOS and Android offer the code from a text message.',
        'An `error` marks every box `aria-invalid` and is linked to the group.',
        'Boxes are 48px, an easy target on phones.',
    ],
    'events' => [
        'Bind one string with `wire:model` or `x-model` (x-modelable): `<x-ui.otp wire:model="code" />`.',
        'When every box is filled it dispatches a bubbling `otp-complete` event; listen with `x-on:otp-complete` to submit at once.',
        'Setting the bound value to `\'\'` from the server (after a wrong code) clears the boxes and focuses the first.',
    ],
    'do' => [
        'Say where the code was sent in the label ("Code sent to +234 803 ••• 4567").',
        'Give each code on a page its own `id`.',
        'Offer a way to resend the code next to it, with a wait time.',
    ],
    'dont' => [
        'Clear the code on a server error without saying why.',
        'Use it for passwords or account numbers.',
    ],
];
