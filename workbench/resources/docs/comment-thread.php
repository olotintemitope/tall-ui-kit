<?php

return [
    'intro' => 'Nested comments in the timeline look, with an inline reply box, a depth limit ("Continue thread" beyond it), "Show N more replies" and collapsible threads. One Alpine component per thread.',
    'keyboard' => [
        ['Enter / Space', 'On Reply, opens the reply box and moves focus into it.'],
        ['Escape', 'In the reply box, closes it and returns focus to Reply.'],
        ['Enter', 'In the reply box, sends (Shift+Enter adds a line).'],
    ],
    'a11y' => [
        'Each level is an `<ol>` (the thread named by `label`, replies "Replies to Ada Obi").',
        'Reply and "Hide replies" are buttons with `aria-expanded` and `aria-controls`.',
        'Reply is named with the author ("Reply to Ada Obi").',
    ],
    'do' => [
        'Load deep threads on their own page with `continueHref`.',
        'Make `replyAction` idempotent with the key it receives.',
    ],
    'dont' => [
        'Nest more than three or four levels on one page.',
        'Put each comment in a card.',
    ],
    'events' => [
        '`replyAction(parentKey | null, text, key)`; `moreAction(key)` or a `ui-thread-more` event, detail `{ key }`.',
    ],
];
