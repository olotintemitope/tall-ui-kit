<?php

return [
    'intro' => 'A non-modal panel anchored below a trigger for small content or controls, built on the Popover API: light dismiss, Escape and the top layer come from the browser.',
    'keyboard' => [
        ['Enter / Space', 'On the trigger, opens or closes the popover.'],
        ['Tab', 'Moves into the popover\'s content after the trigger.'],
        ['Escape', 'Closes the popover and returns focus to the trigger.'],
    ],
    'a11y' => [
        'The trigger gets `aria-expanded` and `aria-controls`.',
        'With a `title` the panel is a `dialog` named by it; without, a `group` named by `label`.',
        'A custom trigger must be a single `<button>`; an icon button needs an `aria-label`.',
    ],
    'do' => [
        'Use it for extra detail on demand: how a fee works, a column\'s definition, a legend.',
        'Keep the content short; link to a fuller page.',
    ],
    'dont' => [
        'Use it for menus (use `x-ui.dropdown`) or hover hints (use `x-ui.tooltip`).',
        'Put a form that needs saving inside: use a dialog.',
    ],
];
