<?php

return [
    'intro' => 'A feed item: author header (avatar, name, optional Verified pill, handle, time), body with safe links, mentions and hashtags, a media slot and an action bar with Like, Comment, Share and a More menu. Posts are flat, divided by 1px rules in the feed.',
    'keyboard' => [
        ['Tab', 'Moves through the time link, the More menu, body links and the actions.'],
        ['Enter / Space', 'Like toggles (aria-pressed); Share opens the share sheet or copies the link.'],
    ],
    'a11y' => [
        'An `<article>` named by the author heading (`headingLevel`).',
        'Like is a toggle button named "Like, 1,240 likes"; the visible count is compact ("1.2k").',
        'The More button is named after the post ("More actions for the post by Ada Obi").',
    ],
    'do' => [
        'Make `likeAction` return `false` when the like cannot be saved: the button and count roll back with a toast.',
        'Pass `mentionUrl` and `hashtagUrl` (":name", ":tag") so mentions and tags link somewhere useful.',
    ],
    'dont' => [
        'Wrap posts in cards or add shadows.',
        'Pass HTML in `body`: it is always escaped.',
    ],
    'events' => [
        '`likeAction(key, on)`: optimistic, rolled back on `false` or a failed request; without it a `ui-like` event, detail `{ key, on }`.',
        '`commentAction(key)` or `commentHref`; `shareUrl` for Share.',
    ],
];
