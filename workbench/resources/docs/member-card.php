<?php

return [
    'intro' => 'People: `x-ui.member-card` for directories and suggestions, `x-ui.profile-header` for the top of a profile page. Both show the avatar, name, role, stats in an `x-ui.stat-strip` (compact values) and Follow and Message. `x-ui.follow-button` is the shared optimistic toggle.',
    'keyboard' => [
        ['Enter / Space', 'Follow toggles between "Follow" and "Following".'],
    ],
    'a11y' => [
        'Follow is a toggle (`aria-pressed`) whose text says the state; its name includes the person ("Follow Ada Obi").',
        'The card is an `<article>` named by the person\'s name; the profile name is the page heading (`level`).',
        'Stats are a description list; the exact number is in `title` beside the compact one.',
    ],
    'do' => [
        'Return `false` from `followAction` when the follow fails: the button rolls back with a toast.',
        'Pass `messageHref` (a conversation page) or `messageAction`.',
    ],
    'dont' => [
        'Add cover images, glows or gradients to the profile header.',
        'Put member cards inside another card.',
    ],
    'events' => [
        '`followAction(key, on)` on the card or header; without it a `ui-follow` event, detail `{ key, on }`.',
    ],
];
