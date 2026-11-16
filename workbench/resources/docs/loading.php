<?php

return [
    'intro' => 'Spinners, an inline loading line, an overlay for a region that refreshes, and the page progress bar under the top bar.',
    'a11y' => [
        'A spinner on its own is `role="status"` with a hidden `label`; inside something already busy (a button with `aria-busy`), pass `:announce="false"`.',
        '`x-ui.loading-inline` is one polite status; its spinner is silent.',
        '`x-ui.loading-overlay` sets `aria-busy="true"` on the region and announces its `label` once; content keeps its full contrast.',
        'The progress bar announces nothing: the region being loaded carries `aria-busy` and the result a toast.',
        'Under reduced motion spinners and the bar pulse slowly instead of moving.',
    ],
    'do' => [
        'Use a skeleton for a first load, the overlay for a refresh and the button\'s `loading` for an action.',
        'Place `x-ui.progress-bar` once in the shell, directly under the sticky top bar.',
    ],
    'dont' => [
        'Put two loaders on one region (a spinner on a skeleton, an overlay over a loading button\'s section).',
        'Fade, blur or veil content while it refreshes.',
    ],
    'events' => [
        '`x-ui.loading-overlay target="period"` follows `wire:loading` for that target and shows after 150ms; `busy` draws it statically.',
        'The progress bar is driven by the `progress` Alpine store, which listens to Livewire requests and `wire:navigate`, and shows only after 200ms.',
    ],
];
