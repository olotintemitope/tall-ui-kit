<?php

return [
    'intro' => 'Modal dialogs on the native `<dialog>`: a plain dialog, a form dialog, a short multi-step dialog and one that fills the screen on phones.',
    'keyboard' => [
        ['Tab / Shift+Tab', 'Moves between the dialog\'s controls; the page behind is inert.'],
        ['Escape', 'Closes the dialog (asks "Discard changes?" first when a form has edits; ignored while an action runs).'],
        ['Enter', 'In a form dialog, submits the form.'],
    ],
    'a11y' => [
        'On open, focus goes to the first field, else the first button (never ×); on close it returns to the trigger, or the page heading if the trigger is gone.',
        'The dialog is named by its `title` and described by its `description`.',
        'While an action runs the footer button has `aria-busy` and a spinner; the label stays as its name.',
        'A form dialog\'s error string is shown inline in an alert and focused.',
    ],
    'do' => [
        'Give every dialog a unique `name` and open it with `$dispatch(\'open-dialog\', \'name\')`.',
        'Use `x-ui.dialog.form` with `action` for a form: it handles busy, errors and unsaved edits.',
        'Use a sheet for record details people read beside the list.',
    ],
    'dont' => [
        'Open a dialog from a dialog (the discard confirm is the one exception).',
        'Put an `x-ui.panel` inside a dialog.',
        'Use a dialog for a message that needs no decision: use a toast or an inline alert.',
    ],
    'events' => [
        'Open and close with `open-dialog` / `close-dialog` and the name, from Alpine (`$dispatch(\'open-dialog\', \'name\')`) or Livewire (`$this->dispatch(\'open-dialog\', name: \'name\')`).',
        '`action` calls a Livewire method with the dialog busy: it closes when the method returns anything but `false` or a string; a string is shown as the error.',
        'Without `action`, form and step dialogs dispatch `dialog-submitted` with the name; a discarded form dispatches `dialog-discarded`.',
    ],
];
