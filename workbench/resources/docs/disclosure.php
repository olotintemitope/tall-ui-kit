<?php

return [
    'intro' => 'A single show and hide region on native `<details>` and `<summary>`, and an accordion of them divided by 1px rules. Both work without JavaScript; Alpine adds the height animation.',
    'keyboard' => [
        ['Enter / Space', 'On a summary, opens or closes its region; focus stays on the summary.'],
        ['Tab', 'Moves between summaries and into an open region\'s content.'],
    ],
    'a11y' => [
        'The summary is the button; the browser announces expanded or collapsed.',
        'Find in page opens a closed disclosure that contains the match.',
        'Summaries are buttons, not headings: put the accordion under its own heading.',
    ],
    'do' => [
        'Give each exclusive accordion on a page its own `name`.',
        'Use `meta` for a short count or note on the right of the summary.',
    ],
    'dont' => [
        'Hide information people need to complete the task.',
        'Box each item or tint the open one.',
    ],
];
