<?php

return [
    'intro' => 'One row per file being uploaded: name, size, status, a bar, and the actions for its state. `x-ui.upload-progress` is server-driven; `x-ui.upload-progress.list` follows Livewire\'s own uploads.',
    'keyboard' => [
        ['Tab', 'Moves through each row\'s actions: Cancel while uploading, Retry and Remove on failure, Remove when done.'],
        ['Enter / Space', 'Runs the focused action.'],
    ],
    'a11y' => [
        'Status is said in words ("Uploading, 45%", "Failed"), never by colour alone; the error is printed below.',
        'Action buttons are named with the file ("Cancel upload of stems.zip").',
        'The list announces each change once in a polite status ("report.pdf uploaded.").',
        'Long file names wrap; they are never cut off.',
    ],
    'do' => [
        'Validate files on the server as usual: the rows only show progress.',
        'Render rows as `as="li"` inside a named list.',
    ],
    'dont' => [
        'Hide a failed upload: keep the row with Retry and Remove.',
    ],
    'events' => [
        '`cancelAction`, `retryAction` and `removeAction` are Livewire calls (e.g. `removeFile(3)`); without them the buttons fire a bubbling `ui-upload` event with `{ action, name }`.',
        '`x-ui.upload-progress.list for="files"` listens to `livewire-upload-start`, `-progress`, `-finish`, `-error` and `-cancel` from the input bound with `wire:model="files"`; Cancel calls `$wire.cancelUpload`, Retry uploads the same files again.',
    ],
];
