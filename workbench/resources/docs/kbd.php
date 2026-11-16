<?php

return [
    'a11y' => [
        'Prints a `<kbd>` element, so the key is marked up as keyboard input.',
        'Screen readers read symbols such as ⌘ inconsistently: where it matters, also write the key in words ("Command K").',
    ],
    'do' => [
        'Name the key as it is printed on the keyboard: Enter, Esc, ⌘K.',
        'Use `tone="dark"` only on the dark sidebar.',
    ],
    'dont' => [
        'Use it for code, references or values: use `x-ui.code`.',
        'Show a shortcut that the page does not actually handle.',
    ],
];
