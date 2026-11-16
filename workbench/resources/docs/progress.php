<?php

return [
    'intro' => 'Progress of a known task as a bar, a small ring, numbered segments, and a meter for a measurement in a range.',
    'a11y' => [
        '`x-ui.progress` is a native `<progress>` named by `label`, with `aria-valuetext`; without `value` it is indeterminate and read as busy.',
        '`x-ui.progress-ring` and `x-ui.progress-steps` are `role="progressbar"` with min, max, now and value text ("Step 3 of 5").',
        '`x-ui.meter` is a native `<meter>`; the value and its word (Good, Fair, Poor) are printed, so colour is never the only cue.',
        '`labelPosition="hidden"` keeps the label and value for assistive tech only.',
    ],
    'do' => [
        'Use the ring only where a bar does not fit: a file tile, a table cell, a button.',
        'Use `x-ui.stepper` instead of progress steps when people need the step names or can go back.',
        'Use `valueText` or `format` (`count`, `bytes`) when a percentage says less than the numbers.',
    ],
    'dont' => [
        'Use a ring as a KPI or a chart: figures go in `x-ui.stat`.',
        'Use these for page loading: that is `x-ui.progress-bar`.',
    ],
    'events' => [
        'When a Livewire morph keeps the element (a stable `id`), the fill animates to the new value.',
    ],
];
