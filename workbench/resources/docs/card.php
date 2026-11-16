<?php

return [
    'intro' => 'Flat cards for peers in a grid: a 1px `line` border, 8px radius and no shadow. The typed cards (summary, list, table, chart, detail, link, profile, selectable) are built on the base card.',
    'keyboard' => [
        ['Tab', 'Reaches header actions, a link card (one anchor) and each selectable card\'s input.'],
        ['Enter', 'Follows a link card.'],
        ['Arrow keys', 'Move between radio-type selectable cards in the same `name` group (native radios).'],
        ['Space', 'Checks the focused selectable card.'],
    ],
    'a11y' => [
        'A link card is a single `<a>`, named by its title and meta line; it shows the focus ring on keyboard focus.',
        'Selectable cards wrap a real radio or checkbox, so the whole card is the label; a disabled card links its `reason` to the input.',
        'Group selectable cards in a `<fieldset>` with a `<legend>`.',
        'A summary card with no value shows a muted dash; with `loading` it shows a skeleton, with `error` one message.',
    ],
    'do' => [
        'Use cards for peers in a grid: three summaries, a list beside a detail.',
        'Pass money through `money` or `x-ui.money` so privacy mode masks it.',
        'Use `x-ui.stat-strip` for three or more metrics in a row.',
    ],
    'dont' => [
        'Nest a card in a card or put one inside an `x-ui.panel`.',
        'Add a coloured header, an icon tile, a hover lift or a shadow.',
        'Build a KPI grid of summary cards with icons.',
    ],
];
