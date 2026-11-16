<?php

return [
    'intro' => 'A segmented control switches a view or period of the same data (7, 30 or 90 days; table or chart). It is a radio group with one selected segment.',
    'keyboard' => [
        ['Tab', 'Moves to the selected segment; only it is in the tab order.'],
        ['Right / Down', 'Selects the next segment, wrapping. Disabled segments are skipped.'],
        ['Left / Up', 'Selects the previous segment, wrapping.'],
        ['Enter / Space', 'Selects the focused segment.'],
    ],
    'a11y' => [
        '`role="radiogroup"` named by `label`; each segment is `role="radio"` with `aria-checked`.',
        'Icon-only segments are named by their `label` and show it as a tooltip on hover and focus.',
        'Disabled segments keep their contrast (no opacity) and get a dashed underline as well as `aria-disabled`.',
    ],
    'do' => [
        'Keep it to two to five short options.',
        'Use `responsive` when the options may not fit on phones: below `sm` an `x-ui.select` with the same binding replaces it.',
    ],
    'dont' => [
        'Use it to navigate between pages or subjects: use `x-ui.tabs`.',
        'Mix segments with and without icons (it throws in local and testing).',
        'Let it wrap onto two lines.',
    ],
    'events' => [
        'Bind with `wire:model.live` (to reload data on change) or `x-model`; the component is `x-modelable`. Without a binding, `value` sets the initial segment.',
    ],
];
