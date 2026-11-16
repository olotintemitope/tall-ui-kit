<?php

return [
    'intro' => 'A ranking built on `x-ui.ranked-list`: rank, name, value and the change since last period as an arrow plus words ("Up 2", "Down 1", "Same", "New"), with the viewer\'s row highlighted.',
    'keyboard' => [
        ['Tab / Enter', 'Reaches and follows a row\'s name when it has an `href`.'],
    ],
    'a11y' => [
        'An ordered list named by `label`; your row has `aria-current="true"` and "You" in its sub line.',
        'Rank change is text, never colour alone; `valueLabel` is spoken after each value ("48,210 streams").',
    ],
    'do' => [
        'Pass `change` as places moved (positive is up) and `new` for first-time entries.',
    ],
    'dont' => [
        'Add medals, trophies, podiums or avatars.',
    ],
    'events' => [],
];
