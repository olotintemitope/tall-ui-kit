<?php

return [
    'intro' => '`x-ui.kv-list` lines up labels and right-aligned values on 1px rules, for money positions. `x-ui.dl` is a description list in three layouts: stacked, inline (a receipt) and grid (two columns from sm).',
    'a11y' => [
        'Both render a real `<dl>` with `dt` and `dd` pairs, so screen readers announce label and value together.',
        'Values in the numeric role use tabular figures so columns of amounts align.',
    ],
    'do' => [
        'Pass amounts through `x-ui.money` in the slot so privacy mode masks them.',
        'Use `numeric` for amounts, counts and references.',
        'Use the `inline` layout for a receipt or spec sheet, `grid` for a record\'s fields.',
    ],
    'dont' => [
        'Use a table for a handful of label and value pairs.',
        'Put each pair in its own box.',
    ],
];
