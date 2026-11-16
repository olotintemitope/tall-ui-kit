<?php

return [
    'intro' => 'Ready layouts for the common form shapes: one column, dense inline rows, a form with a summary or live preview beside it, a long form with a section list, and a wizard.',
    'keyboard' => [
        ['Enter', 'In the wizard, Continue submits the step (the form\'s submit button).'],
        ['Tab', 'In the section nav, the section links come before the form; following one moves focus to that section\'s heading.'],
    ],
    'a11y' => [
        'Wizard: the step list is an `<ol>` with `aria-current="step"`; after a step change focus moves to the step heading and a polite region says "Step 2 of 5: Targets".',
        'Wizard: Continue submits, so the server validates only that step and focus goes to the error summary or the first invalid field.',
        'Section nav: a `<nav>` named by `label`; the link for the section in view has `aria-current="true"`.',
        'Split preview: the companion column is a region named by `previewLabel`.',
        'Inline rows: from `sm` the label sits in a 220px column; the `for`, `id` and `aria-describedby` wiring is the same as a stacked field.',
    ],
    'events' => [
        'Wizard: calls the Livewire methods `next` (submit), `back` and `goTo(step)`; rename them with `nextAction`, `backAction` and `gotoAction`. `model` names the step property.',
        'Wizard: with `flow`, the `draftModel` array is kept in sessionStorage and offered back once through `restoreDraft(array)`. Validate every restored value again.',
        'Split preview: bind fields with deferred `wire:model` and read `$wire.title` in the preview, so it updates with no request.',
    ],
    'do' => [
        'Put every layout on a tinted surface: `x-ui.form-page` or the layout\'s `surface="muted"`.',
        'Wrap `x-ui.form-layout.single` in `x-ui.form` yourself.',
        'Use the wizard for a long task with natural steps, one step per screen.',
    ],
    'dont' => [
        'Clear values on Back in a wizard: they stay in Livewire state.',
        'Use inline rows for long text or textareas: they suit switches, selects and steppers.',
    ],
];
