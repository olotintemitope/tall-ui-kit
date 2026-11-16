<?php

return [
    'intro' => 'A small, static, dense table: flat and bordered, scrolling sideways inside its own box so the page never does. For lists with search, sort, filters and paging, use the data table.',
    'keyboard' => [
        ['Tab', 'When the table overflows, its scroll region takes focus so the arrow keys can scroll it.'],
        ['Left / Right / Up / Down', 'Scroll the focused region.'],
    ],
    'a11y' => [
        'Give every table a `caption`: it names the table and its scroll region; `captionVisible` shows it.',
        'Header cells are `th scope="col"`; use `x-ui.table.th row` for the first cell of a row and for totals labels.',
        'Totals go in the `foot` slot (a `<tfoot>`), drawn in body-strong over a 1px rule.',
    ],
    'do' => [
        'Use `numeric` for amounts and counts, `mono` for references, `muted` for dates.',
        'Use `wrap` on a cell that needs a second line (a name and an email).',
        'Use `sticky` with `maxHeight` for a long table whose header should stay in view.',
    ],
    'dont' => [
        'Truncate cell text with an ellipsis.',
        'Put a plain table on a list page that needs search or paging: use `x-data-table`.',
    ],
];
