<?php

return [
    'intro' => 'Headings, running text, section labels, lists, quotations and long authored content, all in the kit\'s `type-*` roles.',
    'a11y' => [
        '`level` sets the heading tag for the document outline; `size` only changes the look, so keep levels in order.',
        'Text tones meet 4.5:1 in light and dark; never signal state by colour alone.',
        'Text wraps and long strings break: there is no one-line or clamp option.',
    ],
    'do' => [
        'Build hierarchy from size, weight and colour (the type roles), not boxes or icons.',
        'Sanitise user HTML on the server before printing it inside `x-ui.prose` with `{!! !!}`.',
        'Use `variant="plain"` lists for stacked lines that are not a sentence list.',
    ],
    'dont' => [
        'Use filler copy ("Insights", "at a glance") or em dashes.',
        'Pick a heading level for its size.',
        'Add a side bar or fill to a quotation.',
    ],
];
