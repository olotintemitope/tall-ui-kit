<?php

return [
    'intro' => 'Groups and channels in the sidebar section look: collapsible sections, unread dots, mention counts, private channels and the open channel.',
    'keyboard' => [
        ['Enter / Space', 'On a section heading, collapses or expands it.'],
        ['Tab', 'Moves through the headings and channels.'],
    ],
    'a11y' => [
        'A `<nav>` named by `label`; each section is a list named by its heading; headings are disclosure buttons with `aria-expanded`.',
        'Unread is bold text plus a dot, with ", unread" spoken; mentions are a count spoken as "3 mentions"; private channels say "Private:".',
        'The open channel has `aria-current="page"`.',
    ],
    'do' => [
        'Let a collapsed section say how many channels are unread (built in).',
    ],
    'dont' => [
        'Use colour alone for unread.',
        'Nest sections.',
    ],
    'events' => [
        '`action(key)` for channels without `href`; otherwise a `ui-channel-open` event, detail `{ key }`.',
    ],
];
