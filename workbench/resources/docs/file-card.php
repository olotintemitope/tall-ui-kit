<?php

return [
    'intro' => 'One file as a row, a card or a compact link: type label, the full name, size, an optional status pill and the actions its state allows. `x-ui.attachment-list` lists them from data. Chat and timeline attachments use the compact card; upload progress rows share its type label.',
    'keyboard' => [
        ['Tab', 'Reaches each action (Download, Cancel, Retry, Remove), or the compact link.'],
        ['Enter / Space', 'Runs the focused action.'],
    ],
    'a11y' => [
        'Statuses are words in a one-line pill (Uploading, Uploaded, Failed, Infected, Blocked), never colour alone; errors are printed under the name.',
        'Actions are named with the file ("Remove contract.pdf"). The type label is hidden from screen readers and said once after the name (", PDF file").',
        'Infected and blocked files never offer Download.',
        'Names wrap anywhere and are always escaped.',
    ],
    'events' => [
        '`downloadAction`, `cancelAction`, `retryAction`, `removeAction`: Livewire calls (on `x-ui.attachment-list` the method name; it is called with the item key).',
        'Without a Livewire call, name the action in `actions` and listen for the bubbling `ui-file` event: `{ action, key, name }`.',
    ],
    'do' => [
        'Use `variant="compact"` with `href` for attachments in messages and activity feeds.',
        'Say why a file failed or was blocked in `error`.',
    ],
    'dont' => [
        'Put a coloured tile behind the type label, or truncate the name.',
    ],
];
