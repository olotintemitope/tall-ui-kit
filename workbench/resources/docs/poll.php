<?php

return [
    'intro' => 'A poll: the question, single or multiple choice, and results as progress bars with percent and votes once the viewer has voted, the poll has ended, or always (`results="always"`).',
    'keyboard' => [
        ['Arrow keys', 'Move between options (one choice).'],
        ['Space', 'Ticks an option (several choices).'],
        ['Enter / Space', 'On Vote, sends; focus moves to the results.'],
    ],
    'a11y' => [
        'Options are native radios or check boxes in a fieldset named by the question, drawn in the kit look.',
        'Each result is an `x-ui.progress` with "42%, 120 votes" as its value text; your choice says "(your vote)".',
        'Voting with nothing chosen shows "Choose an option." in an alert.',
    ],
    'do' => [
        'Validate the choices on the server and ignore a second vote.',
        'Pass `endsAt` so people know how long they have.',
    ],
    'dont' => [
        'Use pie or donut charts for results.',
        'Show results before voting unless the poll is meant to be open.',
    ],
    'events' => [
        '`action(key, choices)`; without it a `ui-vote` event, detail `{ key, choices }`.',
    ],
];
