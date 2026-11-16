<?php

return [
    'intro' => 'Loading placeholders that match the real component\'s height exactly, so nothing jumps when content arrives. Use the preset for the component that is loading.',
    'a11y' => [
        'Each preset is one `role="status"` named by its `label` ("Loading payouts"); its blocks are hidden from screen readers.',
        'When you compose your own lines with `x-ui.skeleton.line`, wrap them in one `role="status"` with a hidden label.',
        'The pulse stops under reduced motion.',
    ],
    'do' => [
        'Name each skeleton for what is loading.',
        'Match counts to the real content: rows, columns, cards.',
    ],
    'dont' => [
        'Add a spinner on top of a skeleton.',
        'Use a skeleton for a refresh of content already on screen: use `x-ui.loading-overlay`.',
    ],
];
