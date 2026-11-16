<?php

return [
    'intro' => 'Inline code for keys, headers and values in running text, and code blocks for payloads and snippets with an optional label and copy button.',
    'keyboard' => [
        ['Tab', 'Focuses the copy button, then the code block\'s scroll area when it overflows.'],
        ['Left / Right', 'Scroll the focused code block sideways.'],
    ],
    'a11y' => [
        'A code block that scrolls is focusable so keyboard users can read it all.',
        'The copy button keeps focus and announces "Copied to clipboard" politely.',
    ],
    'do' => [
        'Print code in the slot with `{{ }}` so Blade escapes it.',
        'Label a block with what it is ("Webhook payload: payout.paid").',
    ],
    'dont' => [
        'Print secrets or live keys in examples.',
        'Wrap long lines by hand: the block scrolls.',
    ],
];
