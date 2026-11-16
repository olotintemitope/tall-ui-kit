<?php

return [
    'keyboard' => [
        ['Enter', 'On a listed field, moves to that field (an in-page link to its id).'],
    ],
    'a11y' => [
        'It shows only when more than `min` (3) of the given fields have errors; below that, focus goes to the first invalid field instead.',
        'After such a submit `x-ui.form` moves focus to the summary (`tabindex="-1"`, the kit ring on keyboard use), and it is `role="alert"` named by its title ("4 fields need attention").',
        'Each item links to its field\'s id (`#field-<name>`) and repeats the field\'s own message, which stays under the field too.',
    ],
    'events' => [
        'Messages come from `$errors` by field name (dot or bracket notation), so Livewire validation fills it with no extra code.',
        'In `x-ui.form-layout.wizard`, pass the current step\'s fields as `errors` and the wizard prints the summary.',
    ],
    'do' => [
        'List the fields in form order, with the same labels as the fields.',
        'Put it at the top of the form, inside `x-ui.form`.',
    ],
    'dont' => [
        'Use `x-ui.alert` or a toast for validation errors.',
        'Lower `min` to 0 for short forms: one error is clearer at the field.',
    ],
];
