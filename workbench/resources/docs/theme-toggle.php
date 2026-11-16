<?php

return [
    'keyboard' => [
        ['Tab', 'Moves to the chosen segment; the others are out of the tab order.'],
        ['Right / Down', 'Chooses the next scheme and moves focus to it (wraps from System to Light).'],
        ['Left / Up', 'Chooses the previous scheme (wraps from Light to System).'],
    ],
    'a11y' => [
        'A `radiogroup` named by `label`; each segment is a `radio` with `aria-checked`. With `iconOnly` the scheme name is each segment\'s accessible name.',
        'Every change is announced once in a polite live region: "Colour scheme: Dark", and for System the scheme it resolves to.',
        'Focus stays on the segment that was chosen; the scheme changes at once with no reload.',
        'Needs `x-ui.head` in the layout, so the first paint already shows the stored choice.',
    ],
    'events' => [
        'Persistence follows `config(\'tall-ui-kit.color_scheme.persist\')`: localStorage, plus `$wire.setColorScheme()` when `server` and the toggle sits inside a Livewire component that uses the `WithColorScheme` trait.',
        'Every toggle on the page stays in step through the `ui-color-scheme` window event.',
        'Renders nothing when `config(\'tall-ui-kit.color_scheme.schemes\')` offers one scheme only.',
    ],
    'do' => [
        'Put one toggle in the account menu or settings, with a short `label` such as "Mode" in headers.',
        'Use `iconOnly` only where the row is tight; each icon keeps its name for screen readers.',
    ],
    'dont' => [
        'Build your own light and dark switch or a single sun and moon button: System must stay a choice.',
        'Style pages with `dark:` classes to make the toggle work: tokens already have dark values.',
    ],
];
