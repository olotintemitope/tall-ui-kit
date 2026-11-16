<?php

return [
    'intro' => 'The notification bell, the dropdown panel and its rows. The kit ships the components; loading, polling and marking read are app code that passes data in.',
    'keyboard' => [
        ['Enter / Space', 'On the bell, opens the panel.'],
        ['Right / Left', 'In the panel, switches between the All and Unread tabs.'],
        ['Enter', 'On a row\'s title, opens its target and marks it read.'],
        ['Enter / Space / Down', 'On a row\'s menu button, opens Mark as read and Delete.'],
        ['Escape', 'Closes the panel and returns focus to the bell.'],
    ],
    'a11y' => [
        'The bell\'s name carries the count ("Notifications, 3 unread"); the pill is for sight only.',
        'Unread rows show by weight and a dot, and start with "Unread:" for screen readers.',
        'Rows sit in lists under day group headings (Today, Yesterday, Earlier); the full timestamp is in the `<time>` element.',
        'Before the first open the panel shows a skeleton with one status.',
    ],
    'do' => [
        'Poll the count no more often than every 30s, and only while the tab is visible.',
        'Load the list on first open, not with the page.',
    ],
    'dont' => [
        'Show a count in the bell for read items.',
        'Use toasts for every notification.',
    ],
    'events' => [
        'Rows call Livewire methods by name: `open` (default `openItem`), `toggle` (`toggleRead`) and `delete` (`delete`), each with the row `id`; pass "" to hide one.',
        'The panel\'s tabs bind to `model` (default `tab`) with `wire:model.live`; Mark all as read calls `markAll` (default `markAllRead`). Pass `model=""` for a static preview.',
        '`selectable` adds a row checkbox bound to a Livewire array property, for bulk actions.',
    ],
];
