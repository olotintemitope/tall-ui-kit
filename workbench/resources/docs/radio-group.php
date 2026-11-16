<?php

return [
    'keyboard' => [
        ['Tab', 'Moves into the group, to the checked radio (or the first one), then out of it.'],
        ['Down / Right', 'Checks the next radio (native radio behaviour, wraps).'],
        ['Up / Left', 'Checks the previous radio.'],
        ['Space', 'Checks the focused radio if none is checked.'],
    ],
    'a11y' => [
        'Native radios in a `<fieldset>` whose `<legend>` is `label`; the hint and error are linked to the group.',
        'Each row is a `<label>`: the whole area, description included, is the click target.',
        'A checked radio is an ink fill with a white dot, and the cards variant adds a `primary-text` border and tint; the state never rests on colour alone.',
        'Disabled options keep readable text and say why in their description.',
    ],
    'events' => [
        '`wire:model` and `x-model` go on the component and are applied to every radio; `selected` sets the first value without a binding.',
    ],
    'do' => [
        'Use it for up to five choices that should all be visible; use `x-ui.select` for more.',
        'Use `variant="cards"` for plan-style choices with a price or a longer description.',
        'Preselect the safest or most common option when there is one.',
    ],
    'dont' => [
        'Use radios for independent options: use checkboxes.',
        'Use `orientation="horizontal"` for long labels: they wrap awkwardly on phones.',
    ],
];
