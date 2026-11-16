<?php

return [
    'intro' => 'Steps for an onboarding, setup, verification or launch flow, with progress and the next step highlighted. Steps, state and actions are data the page passes in.',
    'keyboard' => [
        ['Tab', 'Moves through the collapse button, each open step\'s action and Dismiss.'],
        ['Enter / Space', 'On the collapse button, shows or hides the steps; focus stays on it.'],
        ['Enter', 'On a step\'s action, follows the link or runs the Livewire call.'],
    ],
    'a11y' => [
        'A `<section>` named by its title; the list is described by the "2 of 5 done" line.',
        'Each step starts with a hidden "Done" or "Not done"; the next step has `aria-current="step"`.',
        'After a Livewire update turns a step done, a polite status names it and focus stays in that row.',
        'After Dismiss, focus moves to the next control on the page, else the page heading.',
    ],
    'do' => [
        'Write steps as tasks ("Add a bank account") with a short action label ("Add account").',
        'Mark steps that never block completion as `optional`.',
        'Put the checklist in a small Livewire component so a step change re-renders only it.',
    ],
    'dont' => [
        'Celebrate completion with confetti or colour.',
        'Use it for a sequence people move through in order: use `x-ui.stepper`.',
    ],
    'events' => [
        '`persist="local"` (default with a `name`) keeps collapse and dismiss in localStorage; `persist="server"` uses the bound `ChecklistStore` through the `WithChecklist` trait on the surrounding Livewire component.',
        'A step\'s `action` is a Livewire call such as `complete(\'invite\')`.',
        'Every change fires a bubbling `ui-checklist` DOM event with `{ name, action, collapsed }`.',
    ],
];
