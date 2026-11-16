<?php

return [
    'intro' => 'A summary row of reactions (symbol and count, yours pressed) plus an "Add reaction" picker that opens below. Symbols are content here, each with a text label. One delegated listener for the page.',
    'keyboard' => [
        ['Enter / Space', 'Toggles a reaction in the row, or opens the picker.'],
        ['Arrow keys', 'Move between reactions in the picker.'],
        ['Home / End', 'First and last reaction in the picker.'],
        ['Escape', 'Closes the picker; focus returns to "Add reaction".'],
    ],
    'a11y' => [
        'The row is a named group; each reaction is a toggle button named "Like 12" with `aria-pressed`.',
        'The picker is a Popover API panel (top layer, light dismiss), labelled "Choose a reaction", placed below its button and never flipped.',
    ],
    'do' => [
        'Keep the set small (about six) and give every symbol a plain label.',
        'Pass `subject` so one `action` serves every post or message.',
    ],
    'dont' => [
        'Use reactions as decoration or as status.',
        'Show reactions with a count of 0.',
    ],
    'events' => [
        '`action(subject, reaction)`: optimistic for existing reactions, rolled back on `false`; without it a `ui-react` event, detail `{ subject, reaction, on }`.',
    ],
];
