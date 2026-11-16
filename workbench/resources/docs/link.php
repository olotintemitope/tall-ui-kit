<?php

return [
    'a11y' => [
        'Links are always underlined, so they never rely on colour alone.',
        '`target="_blank"` adds `rel="noopener"`, an arrow and "(opens in new tab)" for screen readers.',
        'Link text must make sense on its own: "payout schedule", not "click here".',
    ],
    'do' => [
        'Use `x-ui.link` inside running text and `x-ui.link-arrow` for a "View all" link to a fuller page.',
    ],
    'dont' => [
        'Use a link for an action that changes data: use a button.',
        'Use `x-ui.button variant="link"` inside running text.',
    ],
];
