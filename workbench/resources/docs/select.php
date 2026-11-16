<?php

return [
    'keyboard' => [
        ['Enter / Space / Down / Up / Alt+Down', 'Closed: opens the list on the chosen option.'],
        ['Home / End', 'Closed: opens on the first or last option. Open: moves to the first or last.'],
        ['Down / Up / Page Down / Page Up', 'Open: moves the active option (Page keys by 10); disabled options are skipped.'],
        ['Enter / Space', 'Open: chooses the active option and closes. With `multiple`, toggles it and stays open.'],
        ['Alt+Up', 'Chooses the active option and closes.'],
        ['Escape', 'Closes without a change; a dialog around it stays open.'],
        ['Tab', 'Chooses the active option and moves on.'],
        ['Letters', 'Type-ahead to the first match (a repeated letter cycles); when closed, opens on it.'],
    ],
    'a11y' => [
        'The WAI-ARIA APG select-only combobox: a `<button role="combobox">` named by the field label, a `role="listbox"` popup and `role="option"` items with `aria-selected`.',
        'Focus stays on the trigger; `aria-activedescendant` names the active option, which is kept scrolled into view.',
        'Groups are `role="group"` labelled by their heading; `multiple` sets `aria-multiselectable`.',
        'A click on the field label focuses the trigger without opening it. The chosen label wraps, never cut.',
        'The list opens below the trigger and is never clipped by a table, dialog or sheet.',
    ],
    'events' => [
        'Bind `wire:model` (any modifier) or `x-model` on the component; `.live` sends one request per choice, never while arrowing.',
        'Inside a Livewire view the bound property is read on the server, so the first paint and every morph show the right label.',
        'A choice fires `input` and `change` on the trigger, so `x-ui.form` marks itself dirty. Plain forms post a hidden input per value.',
        'With `x-model`, bind a parent property other than `value`, `open` or `text` (the select\'s own Alpine scope uses them).',
    ],
    'do' => [
        'Use `x-ui.combobox` instead when the list is long enough that people want to type to filter.',
        'Use `x-ui.radio-group` for five or fewer choices that should all be visible.',
        'Keep the default `width="auto"`: the trigger is as wide as its longest option.',
    ],
    'dont' => [
        'Use a native `<select>`, anywhere: `PopupPlacementTest` fails on one.',
        'Use a select for yes or no: use a switch or checkbox.',
    ],
];
