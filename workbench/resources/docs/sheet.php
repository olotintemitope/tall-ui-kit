<?php

return [
    'intro' => 'A side sheet that slides in from the right: 480px (`md`) or 640px (`lg`), full width on phones. Same events, focus and busy rules as a dialog.',
    'keyboard' => [
        ['Tab / Shift+Tab', 'Moves between the sheet\'s controls; the page behind is inert.'],
        ['Escape', 'Closes the sheet; focus returns to the trigger.'],
    ],
    'a11y' => [
        'A native modal `<dialog>` named by its `title` and described by its `description`.',
        'On open focus goes to the first field, else the first button (never ×).',
        'The header and footer stay put while the body scrolls.',
    ],
    'do' => [
        'Use a sheet for a record\'s details or filters people apply beside a list.',
        'Keep actions in the footer, the main one last.',
    ],
    'dont' => [
        'Use a sheet for a confirmation: use `x-ui.confirm`.',
        'Stack a dialog on top of a sheet.',
    ],
    'events' => [
        'Open and close with `open-dialog` / `close-dialog` and the sheet\'s name; the backdrop also closes it.',
    ],
];
