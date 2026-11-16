<?php

return [
    'intro' => 'Images in a figure with a caption, a compact audio preview player, video with the browser\'s own controls, and third-party players behind a click-to-load facade. Nothing heavy loads before the user asks for it.',
    'keyboard' => [
        ['Space / Enter', 'On the audio play button, plays or pauses.'],
        ['Left / Right', 'On the play button or seek bar, moves 5 seconds.'],
        ['Home / End', 'On the seek bar, jumps to the start or end.'],
        ['Enter / Space', 'On an embed\'s "Load player" button, loads the player and moves focus into it.'],
    ],
    'a11y' => [
        'Figures need `alt` text; `alt=""` is accepted only with `decorative`, otherwise it throws in local and testing.',
        'The play and mute toggles use `aria-pressed`; the seek bar says "0:12 of 0:30".',
        'Add captions to video with `<track kind="captions">` elements in the slot.',
        'An embed\'s iframe has a title ("Campaign launch recap on YouTube") and a sandbox.',
    ],
    'do' => [
        'Pass `width` and `height` (or rely on `aspect`) so images never shift the layout.',
        'Use `eager` only for the largest image above the fold.',
        'Keep `autoload` off on embeds unless the player is the page\'s only purpose: the facade sets no cookies.',
    ],
    'dont' => [
        'Autoplay video or audio.',
        'Blur cover art behind a player or add a logo tile.',
    ],
];
