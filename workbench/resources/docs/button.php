<?php

return [
    'keyboard' => [
        ['Enter / Space', 'Activates the button (Enter only on a link button).'],
        ['Tab', 'A disabled or busy button stays in the tab order; activation does nothing.'],
    ],
    'a11y' => [
        'Unavailable buttons use `aria-disabled`, never `disabled`, so a button that becomes unavailable while focused keeps focus.',
        'While `loading` runs, the label stays in the accessibility tree and `aria-busy="true"` is set; the spinner is silent.',
        'Icon-only buttons (`size="icon"`, `icon-sm`) need an `aria-label`.',
        'A toggle (`pressed`) exposes `aria-pressed` and differs from off by a border as well as the fill.',
    ],
    'do' => [
        'Use one primary button per region, for the main action.',
        'Name actions with verbs: "Save changes", "Delete payout".',
        'Use `loading="method"` for a Livewire action so double submits are blocked.',
    ],
    'dont' => [
        'Fade a disabled button with opacity.',
        'Put two primary buttons side by side.',
        'Use `variant="link"` for navigation inside running text: use `x-ui.link`.',
    ],
];
