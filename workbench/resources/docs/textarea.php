<?php

return [
    'a11y' => [
        'Inside `x-ui.field` it takes the label, hint and error wiring (`aria-describedby`, `aria-invalid`) like `x-ui.input`.',
        'It grows vertically when dragged; the text never scrolls sideways.',
        'Disabled textareas keep readable `ink-muted` text and a dashed border, never faded.',
    ],
    'do' => [
        'Say the limit in the hint ("Up to 500 characters.") and check it on the server.',
        'Set `rows` to the length you expect, so most answers fit without scrolling.',
    ],
    'dont' => [
        'Use a textarea for several short values: use `x-ui.tag-input`.',
        'Make it narrower than its content: textareas default to the full width.',
    ],
];
