<?php

return [
    'keyboard' => [
        ['Typing', 'Filters the options by substring and opens the list.'],
        ['Down / Up', 'Moves the active option, opening the list first.'],
        ['Alt+Down', 'Opens the list without moving.'],
        ['Enter', 'Picks the active option.'],
        ['Escape', 'Closes the list; a second Escape clears the field.'],
        ['Tab', 'Leaves the field and keeps the choice (an active option that starts with the typed text is picked).'],
    ],
    'a11y' => [
        'An editable `role="combobox"` input with `aria-autocomplete="list"`, `aria-expanded` and `aria-controls`; the popup is a `role="listbox"` of options with `aria-selected`.',
        'Focus stays in the input; `aria-activedescendant` names the active option.',
        'A polite status says how many options match as you type.',
        'Outside `x-ui.field`, give it a name with `label`.',
        '`native` renders `<input list>` with a `<datalist>`: the browser\'s own popup, which cannot be styled and differs between browsers.',
    ],
    'events' => [
        'Bind `wire:model` or `x-model` on the component: the option value, or null. With `allowCustom`, typed text that matches no option is kept as the value.',
    ],
    'do' => [
        'Use it when a list is long enough that people want to type: customers, banks, cities.',
        'Add a `hint` per option when names alone are ambiguous.',
    ],
    'dont' => [
        'Use `native` when the value must be one of the options: the browser lets any text through.',
        'Load thousands of options into the page: search on the server and pass the first matches.',
    ],
];
