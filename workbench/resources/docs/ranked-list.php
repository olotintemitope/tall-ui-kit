<?php

return [
    'intro' => 'An ordered list of rows with an optional rank, a label and sub line, and a value on the right. With `share` a row becomes a compact bar row against the largest value.',
    'keyboard' => [
        ['Tab / Enter', 'Reaches and follows a row\'s label when it has an `href`.'],
    ],
    'a11y' => [
        'The list is an `<ol>`, so the order is announced; rows with `href` keep their list semantics and only the label is a link.',
        'Share bars are decorative: the value is always printed as text beside them.',
        'An empty list shows one meta line (`empty`).',
    ],
    'do' => [
        'Compute `share` against the largest value (0 to 1).',
        'Use it in `x-ui.card.list as="ol"` for a "Top 5" card.',
    ],
    'dont' => [
        'Add medals, trophies or coloured rank badges.',
        'Show more than about 8 share rows: use a horizontal bar chart.',
    ],
];
