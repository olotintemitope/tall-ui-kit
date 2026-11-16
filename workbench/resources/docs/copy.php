<?php

return [
    'intro' => 'Copy to clipboard as a button, a read-only field with a joined button, or an inline value in a table cell. Every form shows the same copied and failed states.',
    'keyboard' => [
        ['Enter / Space', 'Copies the value; focus stays on the button.'],
        ['Tab', 'An inline copy button is always in the Tab order and shows on focus.'],
    ],
    'a11y' => [
        'On success the label changes to "Copied" for 2 seconds and a polite status says "Copied to clipboard".',
        'If the clipboard is blocked, the value is selected and a hint says "Press ⌘C to copy" (Ctrl+C off a Mac).',
        'Icon-only buttons are named by `label`: say what is copied ("Copy reference").',
        'A copy field\'s input is read-only and focusable and selects its text on focus; inside `x-ui.field` the field\'s label names it.',
    ],
    'do' => [
        'Pass `value` when the shown text is masked, so the real value is copied.',
        'Use `mono` for keys, references and ids.',
        'Use `toast` for copies made from a menu, where the button disappears.',
    ],
    'dont' => [
        'Copy a secret that was not meant to be shown again.',
        'Truncate long URLs or keys: they wrap.',
    ],
];
