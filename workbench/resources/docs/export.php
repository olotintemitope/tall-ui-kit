<?php

return [
    'intro' => 'A multi-level export menu (CSV, PDF, date range, columns) and a column picker dialog. On this static site the menu opens and the picker works, but running an export needs the Livewire `export()` method from `WithExports`.',
    'keyboard' => [
        ['Enter / Space / Down', 'Opens the menu from the trigger.'],
        ['Right / Enter / Space', 'Opens a submenu (CSV, PDF, Date range) with focus on its first item; Left goes back.'],
        ['Up / Down / Home / End', 'Move within the current level (Up and Down wrap).'],
        ['Escape', 'Closes every level and returns focus to the trigger.'],
        ['Alt+Up / Alt+Down', 'In the column picker, moves the focused column; Move up and Move down buttons do the same.'],
    ],
    'a11y' => [
        'Submenus are `menuitem`s with `aria-haspopup="menu"` and `aria-expanded`; below 640px they drill down in the same panel with a Back row.',
        'Every column move is announced in a live region.',
        'Required columns stay checked and disabled with a dashed border, never faded, and are tagged "Required".',
        'Export stays unavailable until at least one column is chosen.',
    ],
    'do' => [
        'Pass `rows` and `total` so the menu says how many rows each scope exports.',
        'Pass `columns` as `true` to fetch them on first open and keep the page\'s queries unchanged.',
        'Check on the server that the user may export the rows and columns asked for.',
    ],
    'dont' => [
        'Trust the column keys from the browser without checking them against `exportColumnOptions()`.',
    ],
    'events' => [
        'Menu items call `$wire.export(format, scope, range, columns)` on the surrounding component (`WithExports`, app side); the server toasts "Export ready" or "We\'ll email you when it\'s ready".',
        '`x-ui.export-columns` on its own dispatches `export-columns` on `window` with `{ name, format, columns }`.',
        'A window event `export-columns-load` with `{ name, columns }` replaces the picker\'s list (lazy loading).',
    ],
];
