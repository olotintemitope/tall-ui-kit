<?php

return [
    'intro' => 'A server-driven table for Livewire pages: search, sort, filters, pagination, column visibility, density, selection with bulk actions, row actions and exports. It needs a Livewire component, so it cannot run on this static site: the live table runs in the workbench gallery (`composer serve`, then /tables).',
    'keyboard' => [
        ['Tab', 'Moves through search, filters, column menu, export, the sort buttons, row checkboxes, row menus and pagination.'],
        ['Enter / Space', 'On a sortable heading\'s button, sorts by that column (again to flip the direction); on a token\'s ×, removes that filter.'],
        ['Down / Up', 'In the row menu, moves between actions; Home and End jump to the first and last.'],
        ['Escape', 'Closes the row menu and returns focus to the row\'s trigger; Tab closes it too.'],
    ],
    'a11y' => [
        'Headers are `th scope="col"` with `aria-sort` on the sorted column; the sort control is a real `<button>` inside the heading.',
        'Without a `heading`, a visually hidden h2 names the table with its result count and page ("Payouts, 1,284 results, page 2 of 52") and takes focus after paging.',
        'Active filters sit in a list under a hidden "Active filters" heading; each token\'s × is a 24px button named "Remove filter: <label>".',
        'The `stack` layout turns rows into cards below 640px, so the page never scrolls sideways.',
    ],
    'do' => [
        'Scope `tableQuery()` to the rows the signed-in user may see, and gate it with `tableAbility()`.',
        'Use `tableLayout()` = `stack` on list pages.',
        'Show records by a short reference in the mono role; build links from the uuid.',
    ],
    'dont' => [
        'Build a hand-made table with its own search and sort when `WithDataTable` covers it.',
        'Trust ids from bulk actions without checking ownership on the server.',
        'Put a pill-shaped filter chip in place of `x-ui.filter-token`: pills are status badges only.',
    ],
    'events' => [
        '`use TallUiKit\\Livewire\\Concerns\\WithDataTable;` then define `tableQuery()`, `tableColumns()`, `tableFilters()` and render `x-data-table` with `:table="$this->dataTable()"`.',
        'State (search, sort, filters, page, selection) lives on the Livewire component; filters bind to `filters.{key}`.',
        'Filter tokens call `removeFilter(key, value)`; Clear all calls `clearFilters`.',
        'Exports go through `WithExports` and `x-ui.export-menu`, see the Export page.',
    ],
];
