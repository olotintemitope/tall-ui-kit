<?php

return [
    'intro' => 'A menu of actions anchored to a trigger, with labels, separators, hints and submenus. It always opens below the trigger.',
    'keyboard' => [
        ['Enter / Space / Down', 'On the trigger, opens the menu and focuses the first item.'],
        ['Up', 'On the trigger, opens the menu and focuses the last item.'],
        ['Down / Up', 'Moves between items in the current menu, wrapping.'],
        ['Home / End', 'Jumps to the first or last item.'],
        ['Right / Enter / Space', 'On a submenu item, opens it and focuses its first item.'],
        ['Left', 'Closes the submenu and returns focus to its item.'],
        ['Escape', 'Closes every level and returns focus to the trigger.'],
        ['Tab', 'Closes the menu and moves on.'],
    ],
    'a11y' => [
        'The panel is `role="menu"` with `menuitem` rows; focus is roving (the focused item has `tabindex="0"`).',
        'A submenu item has `aria-haspopup` and `aria-expanded`; below 640px it drills down in the same panel with a Back row.',
        'For a choice inside a menu use `role="menuitemradio"` or `menuitemcheckbox` with `aria-checked`, and `data-keep-open` so the menu stays open.',
        'An icon-only trigger needs an `aria-label` that names the record, e.g. "Actions for Chiamaka Okafor".',
    ],
    'do' => [
        'Name items with verbs and put the destructive one last, after a separator, with `variant="danger"`.',
        'Use `hint` for a short fact about the item, such as a row count.',
        'Keep submenus one level deep where you can.',
    ],
    'dont' => [
        'Use a dropdown to pick a form value: use `x-ui.select` or `x-ui.combobox`.',
        'Place it above the trigger, except deliberately (an account menu at the foot of a sidebar).',
        'Put form fields or long text inside: use `x-ui.popover`.',
    ],
    'events' => [
        'Items take `wire:click` (or `x-on:click`) like any button; `href` renders a link.',
    ],
];
