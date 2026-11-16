<?php

return [
    'intro' => 'A list of named steps and where the user is in them, for onboarding, verification flows and wizard headers.',
    'keyboard' => [
        ['Tab', 'Moves through the clickable steps only (with `clickable`); other steps are text.'],
        ['Enter', 'On a clickable step, follows its link or runs its action.'],
    ],
    'a11y' => [
        'A `<nav>` named by `label` around an ordered list; the current step has `aria-current="step"`.',
        'Every step starts with a hidden "Completed", "Current", "Upcoming" or "Error", so state never rests on colour or the marker.',
        'Horizontal steppers stack vertically when their own box is under 42rem wide.',
    ],
    'do' => [
        'Keep labels short and say what failed in the error step\'s description.',
        'Use `variant="inline"` for a wizard header; it shows "Step 2 of 5" on phones.',
    ],
    'dont' => [
        'Make upcoming steps clickable when they depend on earlier ones.',
        'Use it for unordered tasks: use `x-ui.checklist`.',
    ],
    'events' => [
        '`action` calls a Livewire method with the 1-based step number (`goTo(2)`); a step\'s own `href` or `action` wins.',
        'Without either, a clickable step dispatches a bubbling `ui-step` event with `{ step, key }`.',
    ],
];
