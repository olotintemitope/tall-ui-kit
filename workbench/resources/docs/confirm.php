<?php

return [
    'intro' => 'Confirmations ask one question with one consequence and a verb button: plain, destructive (optionally typed), with a required reason, or an alert to acknowledge.',
    'keyboard' => [
        ['Tab / Shift+Tab', 'Moves between the fields and buttons; the page behind is inert.'],
        ['Escape', 'Cancels (ignored while the action runs); for an alert dialog, acknowledges it.'],
        ['Enter / Space', 'Activates the focused button.'],
    ],
    'a11y' => [
        'Destructive confirmations open with focus on Cancel; others on the first field or button.',
        'With `confirmText`, the confirm button is `aria-disabled` until the text matches, with the reason linked.',
        'An alert dialog is `role="alertdialog"` with focus on its one button.',
        'Errors from the action are shown inline and focused; reason errors show on the field and move focus to it.',
    ],
    'do' => [
        'Write the title as a question ("Delete this campaign?") and the description as the consequence.',
        'Label the button with the verb and object: "Delete campaign", "Send payout".',
        'Re-check permissions in the server action.',
    ],
    'dont' => [
        'Use Yes, No or OK as button labels.',
        'Confirm actions that are easy to undo: use a toast with Undo.',
        'Let the backdrop close a confirmation (it does not).',
    ],
    'events' => [
        'Open with `$dispatch(\'open-dialog\', \'name\')` (the older `open-confirm` also works).',
        '`action` (with `params`) calls a Livewire method with the dialog busy; it closes on success and shows a returned string as the error. `.reason` passes the reason as the last argument.',
        'Without `action`, `x-ui.confirm` dispatches `confirmed` with the name as the detail; `x-ui.confirm.reason` dispatches `confirmed` with `{ name, reason }`; the alert dialog dispatches `acknowledged`.',
    ],
];
