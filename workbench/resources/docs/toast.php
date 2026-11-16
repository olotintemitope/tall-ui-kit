<?php

return [
    'intro' => 'Short messages about the result of an action, stacked in a corner. Place `x-ui.toaster` once in the layout and push toasts from Livewire or Alpine.',
    'keyboard' => [
        ['Alt+T', 'Moves focus to the newest toast\'s dismiss button; focus returns where it was when that toast closes.'],
        ['Tab', 'From there, moves through the toast\'s action and dismiss buttons.'],
        ['Enter / Space', 'Runs the action or dismisses the toast.'],
    ],
    'a11y' => [
        'Success, info, warning, neutral and loading toasts sit in a polite live region, errors in an assertive one.',
        'Toasts never take focus; timers pause while a toast is hovered or focused.',
        'Errors and loading toasts are sticky until dismissed or resolved.',
        'While a modal is open the toaster moves into it, so toasts stay above the backdrop, clickable and announced.',
    ],
    'do' => [
        'Write the title as a short past-tense fact: "Payout sent."',
        'Offer Undo as the action instead of a confirmation for easy-to-reverse changes.',
        'Use `$toast.promise` (or a shared `id`) so a loading toast turns into the result.',
    ],
    'dont' => [
        'Place a second `x-ui.toaster`.',
        'Use a toast for a page-wide announcement (`x-ui.banner`) or a form error (inline errors).',
        'Put essential information only in a toast that times out.',
    ],
    'events' => [
        'Livewire: `$this->dispatch(\'toast\', variant: \'success\', title: \'Saved.\', action: [\'label\' => \'Undo\', \'method\' => \'undo\'])`; the action method is called on the dispatching component.',
        'Alpine: `$toast(\'Saved.\')`, `$toast({ variant, title, description, action, duration })` or `$toast.promise(promise, { loading, success, error })`.',
        'A `toast` session flash shows on the next page.',
    ],
];
