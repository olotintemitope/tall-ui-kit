<?php

return [
    'a11y' => [
        'A badge is plain text: the label must say the status on its own, colour only repeats it.',
        'Every variant\'s text meets 4.5:1 on its fill in light and dark.',
        'It never wraps or shrinks, so the whole label is always read and shown.',
    ],
    'do' => [
        'Keep labels to one or two words in sentence case: "Paid", "Pending review".',
        'Use `lean` in table cells and long lists: the same look in about a third of the markup.',
        'Map statuses to variants in one place (an enum method), so "Failed" is always `danger`.',
    ],
    'dont' => [
        'Add a dot, an icon or a border to a status badge.',
        'Use a badge as a button, a link or a count on navigation.',
        'Show more than one status badge for the same record.',
    ],
];
