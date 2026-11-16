<?php

return [
    'intro' => 'A short hint on hover and focus, mainly to show the name of an icon-only button. It is positioned `fixed`, so scrolling and overflow never clip it.',
    'keyboard' => [
        ['Tab', 'Focusing the trigger shows the tooltip; moving focus away hides it.'],
        ['Escape', 'Hides the tooltip.'],
    ],
    'a11y' => [
        'The tooltip is `role="tooltip"` and is only read while shown; the control must still carry its own name (`aria-label` on an icon button).',
        'It holds no links or buttons: it disappears when the pointer leaves.',
    ],
    'do' => [
        'Repeat the icon button\'s `aria-label` as the tooltip text, and add a shortcut with `x-ui.kbd tone="dark"`.',
    ],
    'dont' => [
        'Put essential information only in a tooltip: touch users cannot hover.',
        'Put interactive content inside: use `x-ui.popover`.',
    ],
];
