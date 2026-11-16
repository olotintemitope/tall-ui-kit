<?php

return [
    'intro' => 'A list the user reorders by dragging a grip handle (mouse, pen or touch) or from the keyboard. The new order goes to Livewire in one call.',
    'keyboard' => [
        ['Space / Enter', 'On a handle, picks the row up; again, drops it.'],
        ['Up / Down', 'Moves the picked-up row one place.'],
        ['Home / End', 'Moves the picked-up row to the top or bottom.'],
        ['Escape', 'Cancels and puts the row back.'],
    ],
    'a11y' => [
        'Each handle is a 32px button named "Reorder <label>" and described by the list\'s instructions.',
        'A polite live region announces each step, e.g. "Picked up Mobile money. Position 3 of 4." and "Dropped. Position 2 of 4."',
        'Focus stays on the moved row\'s handle.',
        'Links and text in a row keep working: only the handle drags.',
    ],
    'do' => [
        'Give every row a stable `key`; it is what the server receives.',
        'Validate the keys on the server: they come from the browser.',
        'In slot mode pass `help` (the list id plus `-help`) on each `x-ui.sortable.item` so the handle is described.',
    ],
    'dont' => [
        'Make the whole row draggable.',
        'Save after every move step: the order is sent once, on drop.',
    ],
    'events' => [
        '`action="reorder"` calls `reorder(keys)` on the closest Livewire component with the new order, top to bottom.',
        'A bubbling `ui-sort` DOM event fires with `{ order, key, from, to }`.',
    ],
];
