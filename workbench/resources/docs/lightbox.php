<?php

return [
    'intro' => 'A thumbnail button that opens the full image in a dialog, with "Open full size" and Close.',
    'keyboard' => [
        ['Enter / Space', 'On the thumbnail, opens the image.'],
        ['Tab / Shift+Tab', 'Moves between Open full size and Close in the dialog.'],
        ['Escape', 'Closes the dialog; focus returns to the thumbnail.'],
    ],
    'a11y' => [
        'The thumbnail is a button named "View <alt>"; `alt` is required and should say what the image shows.',
        '"Open full size" opens a new tab and says so to screen readers.',
    ],
    'do' => [
        'Pass `width` and `height` so the dialog keeps the image\'s aspect.',
        'Give every lightbox on a page its own `name`.',
    ],
    'dont' => [
        'Use a file name as the `alt`.',
    ],
];
