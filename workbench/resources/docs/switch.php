<?php

return [
    'keyboard' => [
        ['Space / Enter', 'Toggles the switch; focus stays on it.'],
    ],
    'a11y' => [
        'A `<button role="switch">` with `aria-checked`, named by its label; the description is linked with `aria-describedby`.',
        'The whole row toggles it. On and off differ by position and fill, not colour alone.',
        'Disabled switches keep a dashed border, never faded. In forced colours the thumb uses `CanvasText`.',
    ],
    'events' => [
        'Bind with `wire:model` or `x-model` (x-modelable); `checked` sets the first state without a binding. In plain forms `name` posts a hidden 1 or 0.',
        'Inside `x-ui.field` it takes the field\'s id and describedby wiring.',
    ],
    'do' => [
        'Use a switch for a setting that takes effect at once (`wire:model.live`), and say so in the description if it costs money.',
        'Write the label as the thing being turned on: "Payout alerts", not "Turn on payout alerts".',
    ],
    'dont' => [
        'Use a switch inside a form that needs Save: use a checkbox.',
        'Change the label between on and off states.',
    ],
];
