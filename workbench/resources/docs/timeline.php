<?php

return [
    'intro' => 'An activity feed: who did what, when. Pass `items` as data (optionally grouped by day with a "Show more" reveal), or write `x-ui.timeline.item` rows in the slot for rich bodies.',
    'keyboard' => [
        ['Enter / Space', 'On "Show N more", reveals the hidden entries and moves focus to the first of them.'],
    ],
    'a11y' => [
        'Each day is an `<ol>` under a heading (`headingLevel`, 3); without day groups the list is named by `label`.',
        'Each entry reads as one sentence: "Amaka Obi approved the payout, 2h ago".',
        'The reveal button has `aria-expanded` and `aria-controls`; hidden entries are out of the accessibility tree until shown.',
        'Times are `<time datetime>` elements with the absolute time in `title`.',
    ],
    'do' => [
        'Give every item a stable `key`.',
        'Use `total` and `moreAction` to page from the server when the feed is long.',
        'Pass `now` in tests and docs so relative times are stable.',
    ],
    'dont' => [
        'Put entries in cards or icon tiles.',
        'Use a timeline for steps the user has to complete: use `x-ui.stepper` or `x-ui.checklist`.',
    ],
    'events' => [
        '`moreAction` names a Livewire method that loads the next page; the `x-ui.load-more` row shows "Showing 20 of 134".',
    ],
];
