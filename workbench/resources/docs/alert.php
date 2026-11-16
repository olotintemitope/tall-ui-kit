<?php

return [
    'intro' => 'A flat inline notice for a page or section: a 1px tinted border, a light fill and AA text, with no icon and no coloured edge.',
    'a11y' => [
        'Danger alerts are `role="alert"` (announced at once); the other tones are `role="status"`.',
        'The tone is said in the words, never by colour alone.',
    ],
    'do' => [
        'Say what happened and what to do next in one or two sentences.',
        'Add one action as `x-ui.button variant="link"` in the message.',
    ],
    'dont' => [
        'Use an alert for the result of the user\'s own action: use a toast.',
        'Use it for a site-wide announcement: use `x-ui.banner`.',
        'Add an icon tile or a coloured edge bar.',
    ],
];
