<?php

return [
    'intro' => 'The right viewer for a file (image with zoom, PDF, audio, video, text or code, or a plain fallback), its details, Download and Open in new tab, and Previous / Next across a set. Inline, in a dialog or in a sheet.',
    'keyboard' => [
        ['Left / Right', 'Previous / next file while focus is in the preview (not in a media control or field).'],
        ['+ / - / 0', 'On the image: zoom in, zoom out, fit.'],
        ['Arrow keys', 'On a zoomed image: scroll it.'],
        ['Escape', 'Closes the dialog or sheet variant; focus returns to what opened it.'],
    ],
    'a11y' => [
        'The panel is a named region ("Preview of report.pdf"); the image box is a focusable region that names its keys.',
        'Zoom buttons are named; Fit is a toggle (`aria-pressed`); the zoom level is announced.',
        'Previous and Next say where they go ("Next file: notes.md") and "3 of 12" says where you are.',
        'The PDF `<object>` has a fallback link; unknown types say there is no preview and offer Download.',
    ],
    'events' => [
        '`navigateAction` is called with the key of the previous or next file; without it a bubbling `ui-file-preview` event fires with `{ key }`.',
    ],
    'do' => [
        'Give URLs your app serves (signed, owner-checked). The kit never fetches a URL on the server.',
        'Pass text yourself for text and code files, with a cap (`maxText`).',
        'Serve user files with `Content-Disposition` and `X-Content-Type-Options: nosniff`; SVG with a sandboxing CSP.',
    ],
    'dont' => [
        'Preview untrusted HTML inline: download it instead.',
    ],
];
