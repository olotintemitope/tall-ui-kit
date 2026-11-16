<?php

return [
    'keyboard' => [
        ['Tab', 'Focuses an avatar group (unless `focusable` is false) and shows the full list of names.'],
        ['Escape', 'Hides the list of names; focus stays on the group.'],
    ],
    'a11y' => [
        'An avatar without `src` is decorative (`aria-hidden`): always print the name beside it.',
        'A photo has the name as its alt text; pass `alt=""` when the name is printed beside it, so it is not read twice.',
        'An avatar group is one `role="img"` named after everyone ("Reviewers: Ada Obi, Bola Ade and 4 others" lists every name), and the avatars inside are hidden.',
        'The group\'s tooltip shows on hover and on focus, is never clipped by a scroll box, and is dismissed with Escape (WCAG 1.4.13).',
    ],
    'do' => [
        'Use `size="xs"` or `sm` in table rows, `lg` on a profile header.',
        'Set `focusable` to false when the group sits inside a link or button that already names the people.',
    ],
    'dont' => [
        'Use colourful, generated backgrounds per person: avatars stay neutral.',
        'Use an avatar as the only way to tell who someone is.',
    ],
];
