<?php

return [
    'keyboard' => [
        ['Enter', 'In the hex field: applies the typed colour (#RGB or #RRGGBB, with or without #).'],
        ['Tab', 'Leaving the hex field applies it too; then moves to the next control.'],
        ['Enter / Space', 'On the swatch: opens the system colour picker.'],
    ],
    'a11y' => [
        'The hex field is named by the field label (or `label` outside `x-ui.field`); the swatch by `swatchLabel` ("Pick a colour").',
        'An invalid hex marks the field `aria-invalid` and a polite message says how to fix it, until a valid value is typed.',
        'The swatch has a 1px `control` edge, so even a white or very light colour keeps a visible boundary.',
    ],
    'events' => [
        'Bind `wire:model` or `x-model` on the component: an upper-case `#RRGGBB` string. Validate the format again on the server.',
    ],
    'do' => [
        'Check the contrast of a chosen brand colour where it is used for text, and warn when it falls below 4.5:1.',
    ],
    'dont' => [
        'Use it to let people theme the app\'s own UI: the kit\'s tokens carry the contrast rules.',
    ],
];
