<?php

return [
    'keyboard' => [
        ['Tab', 'Focuses the file input that covers the drop area.'],
        ['Enter / Space', 'Opens the system file chooser.'],
        ['Enter / Space on Remove', 'Images variant: takes that image out; focus moves to the next image\'s Remove, else the drop area.'],
    ],
    'a11y' => [
        'A real `<input type="file">` covers the drop area, so click, keyboard and drag and drop all feed the same control and its label names it.',
        'The area\'s hint ("PDF, up to 10 MB.") and the field\'s error are linked with `aria-describedby`.',
        'Chosen files are listed in a named list ("Chosen files", "Chosen images"); each preview uses the file name as alt text and has a "Remove <name>" button.',
        'Livewire upload progress shows as a `progressbar` named "Upload progress".',
    ],
    'events' => [
        '`wire:model` uploads work unchanged; progress comes from Livewire\'s `livewire-upload-*` events.',
        'Removing an image rebuilds the input\'s files and fires `change`, so `wire:model` uploads follow.',
        'The `files` slot replaces the client-side list, e.g. with `x-ui.upload-progress.list` or the server\'s temporary uploads (`$file->temporaryUrl()`).',
    ],
    'do' => [
        'Say the accepted types and size limit in `hint`, and enforce both on the server.',
        'Set `accept` so the chooser only offers files that will be accepted.',
    ],
    'dont' => [
        'Trust the file name or type from the browser: check the content on the server.',
        'Hide the file input and fake one with a button: drag and drop and assistive tech would break.',
    ],
];
