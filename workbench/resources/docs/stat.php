<?php

return [
    'intro' => 'One strip of metrics divided by 1px rules, with no cards. `x-ui.delta` states the change against the prior period in words, coloured only when the move is good or bad.',
    'a11y' => [
        'The strip is a `<dl>`: each stat is a label (`dt`) and its value and sub line (`dd`).',
        'A missing value shows a muted dash, not a zero.',
        'A delta reads as text ("+10.4% vs prior 30d"), so colour is never the only signal.',
        'Skeleton cells read "Loading" to screen readers and keep the strip\'s height.',
    ],
    'do' => [
        'Set `money` (or use `x-ui.money` in the slot) for amounts so privacy mode blurs them.',
        'Set `upIsGood` to false for metrics where a rise is bad: refunds, chargebacks, churn.',
        'Keep 3 to 6 cells; the strip wraps on small screens.',
    ],
    'dont' => [
        'Put each stat in its own card with an icon.',
        'Wrap a delta in a tinted pill or add an arrow icon.',
    ],
];
