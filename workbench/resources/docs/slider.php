<?php

return [
    'intro' => '`x-ui.slider` is a custom slider with one thumb or two, formatted values and a click-anywhere track. `x-ui.range` is the native `<input type="range">`, single or dual, with uneven `steps`.',
    'keyboard' => [
        ['Right / Up', 'Moves the focused thumb up by `step`.'],
        ['Left / Down', 'Moves it down by `step`.'],
        ['Page Up / Page Down', 'Moves it by `pageStep` (a tenth of the range by default; `x-ui.range` uses the browser\'s own jump).'],
        ['Home / End', 'Moves it to the lowest or highest value it may take; with two thumbs each stops at the other.'],
        ['Tab', 'Moves between the thumbs; each one is a tab stop.'],
    ],
    'a11y' => [
        'Each thumb is `role="slider"` with `aria-valuemin`, `aria-valuemax`, `aria-valuenow` and the formatted value as `aria-valuetext` ("₦25,000").',
        'With two thumbs each has its own name ("Minimum follower range", or `minLabel` and `maxLabel`) and its range stops at the other\'s value, so they never cross.',
        'The value prints beside the label; `bounds` prints min and max under the track.',
        'Disabled: a dashed track and thumbs on `surface-subtle`, no opacity; the thumbs leave the tab order.',
        'Pointer: press anywhere on the track to move the nearest thumb, then drag. The track has `touch-action: none`, so a finger drag never scrolls the page.',
    ],
    'events' => [
        'Bind `wire:model` or `x-model` on the component: a number, or `[low, high]` with `dual`. Pass `value` too for the first paint.',
        'The value commits on release and on each key press, so `wire:model.live` sends one request per drag, not one per pixel.',
        '`x-ui.range` can also split a dual value into two Livewire properties with `minModel` and `maxModel` (sent once on release, null at an open end).',
        'The slider behaviour (`slider-core.js`) loads the first time a slider is on the page.',
    ],
    'do' => [
        'Use a slider where the exact value matters less than its rough size; pair it with a number input when people need an exact figure.',
        'Use `x-ui.range` with `steps` for uneven scales (1k, 5k, 10k, 50k) and `openEnded` for "500,000+".',
    ],
    'dont' => [
        'Use a slider for a choice between a few named options: use a radio group or segmented control.',
        'Hide the label: use `hideLabel` only when a heading right above names it.',
    ],
];
