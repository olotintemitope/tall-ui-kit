<?php

return [
    'intro' => 'Underline tabs that switch between views of one subject, either in place (a tablist with panels) or between sibling pages (a nav of links).',
    'keyboard' => [
        ['Tab', 'Moves into the tablist on the selected tab, then on to its panel.'],
        ['Right / Left', 'Moves to the next or previous tab, wrapping, and selects it (automatic activation). Disabled tabs are skipped.'],
        ['Home / End', 'Moves to the first or last tab and selects it.'],
        ['Enter', 'Follows a link tab (link form only).'],
    ],
    'a11y' => [
        'Without `href` the row is `role="tablist"` with `tab` and `tabpanel` roles, `aria-selected`, `aria-controls` and `aria-labelledby`; give it a `label`.',
        'Link tabs render a `<nav>` named by `label`; the active link has `aria-current="page"`.',
        'Panels are focusable (`tabindex="0"`) so text-only content can be reached; pass `:focusable="false"` on a panel full of controls.',
        'A count is plain text after the label, so it is read with the tab name.',
    ],
    'do' => [
        'Use tabs for views of one subject or sibling pages; use a segmented control for periods or table and chart views of the same data.',
        'Keep labels to one or two words.',
        'Add `\'badge\' => \'danger\'` only when a count needs attention, such as late items.',
    ],
    'dont' => [
        'Make icon-only tabs, or mix tabs with and without icons (it throws in local and testing).',
        'Mix link tabs and panel tabs in one row.',
        'Box, fill or colour the active tab.',
    ],
    'events' => [
        'Bind the active key with `wire:model` (or `wire:model.live` to load on change) or `x-model`; the component is `x-modelable`.',
        'Link tabs take `\'navigate\' => true` to add `wire:navigate`.',
    ],
];
