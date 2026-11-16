<?php

return [
    'intro' => 'The wiring around a control: `x-ui.field` (label, hint or error), `x-ui.field-row` (fields side by side), `x-ui.fieldset` (a named group), `x-ui.label` and `x-ui.output` (a calculated value).',
    'a11y' => [
        'The label\'s `for` points at the control, and the control gets `aria-describedby` (the hint or the error), `aria-invalid` and `aria-required` through `@aware`: no ids to wire by hand.',
        'The error replaces the hint, never both, and is a polite live region, so a new message is read once.',
        '`required` shows a muted " *" hidden from screen readers and sets `aria-required` on the control; there is no native validation popup.',
        '`x-ui.fieldset` is a real `<fieldset>` and `<legend>`; its description is linked with `aria-describedby`. `disabled` disables every control inside natively.',
        '`x-ui.output` is an `<output>` (role status), announced politely when its value changes; `for` names the inputs it is computed from.',
    ],
    'do' => [
        'Give every field a `name`: it sets the id and finds the error in `$errors` (dot or bracket notation).',
        'Write hints that help fill the field ("As on your bank account."), and errors that say how to fix it ("Enter at least ₦5,000.").',
        'Use a fieldset for groups of checkboxes or switches that answer one question.',
    ],
    'dont' => [
        'Use the placeholder as the label, or leave a control without a label or `aria-label`.',
        'Put more than three fields in one row; rows stack below `md` anyway.',
    ],
];
