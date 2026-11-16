<?php

return [
    'keyboard' => [
        ['Enter / ,', 'Adds the typed text as a tag (the separator is `separator`).'],
        ['Backspace', 'In the empty input, removes the last tag. On a focused tag, removes it.'],
        ['Delete', 'On a focused tag, removes it.'],
        ['Left', 'At the start of the input, moves to the last tag; between tags, to the previous one.'],
        ['Right', 'Moves to the next tag; past the last tag, back to the input.'],
        ['Home / End', 'On a tag, moves to the first or last tag.'],
        ['Escape', 'On a tag, back to the input. With suggestions open, closes the list.'],
        ['Down / Up', 'With suggestions, moves through the list; Enter picks.'],
    ],
    'a11y' => [
        'Each tag has its own remove button, "Remove Afrobeats" (`removeLabel` sets the verb).',
        'Focus never falls to `<body>`: after a removal it moves to the previous tag, else the next, else the input.',
        'Every add, skip and removal is said in a polite message under the field ("Added 2 tags. 1 skipped: ..."), including why a tag was refused.',
        'With `suggestions` the input is an APG combobox with a listbox below it; tags already chosen are left out.',
        'With `max`, a caption says "3 of 5".',
    ],
    'events' => [
        'Bind an array with `wire:model` or `x-model` (x-modelable): plain `wire:model` sends nothing until the next action, `.live` one request per change. Pass `value` for the first paint.',
        'Plain forms post `name[]`.',
        'The browser checks (empty, `maxLength`, `pattern`, duplicates, `max`, `allowCustom`) are a convenience: validate the array again on the server.',
    ],
    'do' => [
        'Say how to add several at once in the hint ("Press Enter or a comma to add.").',
        'Give suggestions a `hint` when names alone are ambiguous: `[\'value\' => \'Tunde Bakare\', \'hint\' => \'Owner\']`.',
    ],
    'dont' => [
        'Use it for long text: tags are short values.',
        'Use it for a fixed set of a few options: use checkboxes or a multiple select.',
    ],
];
