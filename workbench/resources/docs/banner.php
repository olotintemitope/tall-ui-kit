<?php

return [
    'intro' => 'A page-level announcement at the top of the main region: maintenance, a new policy, a billing problem. It stays until it is dismissed and never takes focus.',
    'keyboard' => [
        ['Tab', 'Reaches the action link and the Dismiss button.'],
        ['Enter / Space', 'On Dismiss, hides the banner; focus moves to the next control on the page, else the page heading.'],
    ],
    'a11y' => [
        'With a `title` (or `label`) the banner is a named region; without either it is a plain block, so untitled banners never repeat a landmark name.',
        'The dismiss button is named "Dismiss announcement" (`dismissLabel`).',
        'It never takes focus when it appears and never stacks.',
    ],
    'do' => [
        'Show one banner at a time, and say what happens and when.',
        'Put a version in the `name` ("maintenance-2026-11") so a new announcement shows again.',
    ],
    'dont' => [
        'Use it for a form error (`x-ui.alert`, `x-ui.error-summary`) or a save confirmation (a toast).',
        'Add an icon or a coloured edge.',
    ],
    'events' => [
        '`persist="local"` (default with a `name`) keeps the dismissal in localStorage, applied before first paint.',
        '`persist="server"` stores it through the bound `DismissStore`: add `TallUiKit\\Livewire\\Concerns\\WithDismissals` to the Livewire component; override `dismissibleKeys()` and `canDismiss($key)` to restrict it.',
        'Every dismissal fires a bubbling `ui-dismiss` DOM event with `{ key }`.',
    ],
];
