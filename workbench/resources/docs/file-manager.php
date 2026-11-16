<?php

return [
    'intro' => 'Folders and files in a grid or list, with breadcrumbs, sorting, selection, a bulk bar, inline rename, a Move dialog, delete with confirmation, new folders, downloads and drag-and-drop uploads. Data in, Livewire actions out.',
    'keyboard' => [
        ['Tab', 'Reaches the toolbar, then one item (focus is roving inside the list).'],
        ['Arrow keys', 'Grid: Left and Right move by one, Up and Down by a row. List: Up and Down.'],
        ['Shift + arrows', 'Extends the selection from the last item you chose.'],
        ['Space', 'Selects or deselects the focused item (Shift + Space selects a range).'],
        ['Enter', 'Opens the focused folder or file.'],
        ['F2', 'Renames inline: Enter saves, Escape cancels and focus returns to the item.'],
        ['Delete', 'Asks to delete the selection (or the focused item).'],
        ['Ctrl / Cmd + A', 'Selects every loaded item.'],
        ['Escape', 'Clears the selection.'],
        ['A letter', 'Moves to the next item whose name starts with it.'],
        ['Home / End', 'First / last item.'],
    ],
    'a11y' => [
        'The items are one listbox (`role="listbox"`, `aria-multiselectable`) in both views; each item is an option with `aria-selected`. The list view is the APG listbox pattern; the grid adds two-dimensional arrows.',
        'Selection counts and finished actions are announced in a polite status: "3 items selected.", "Renamed to Brief.pdf.", "Moved 2 items to Invoices.", "Deleted 1 item."',
        'After a delete or move focus goes to the next item, else the previous, else the folder heading. After opening a folder it goes to the first item.',
        'The rename field is named "Rename <name>"; its error is linked with `aria-describedby` and focus stays in it.',
        'Every drag has a button: Upload opens the file chooser; Move opens a folder picker (an `x-ui.tree`).',
        'Names wrap anywhere; they are never cut off. Names are escaped.',
    ],
    'events' => [
        '`openAction(id)`, `renameAction(id, name)`, `moveAction(ids, folderId)`, `deleteAction(ids)`, `createFolderAction(name)`, `downloadAction(ids)`, `sortAction(field, direction)`, `loadMoreAction()`, `retryAction()`: Livewire methods. Return a string from rename, move, delete or create to show it as the error.',
        'Uploads: `upload` names a `WithFileUploads` array property. Dropped files are uploaded through it after `uploadTarget` (default `uploadFolder`) is set, without a request, to the folder dropped on.',
        'The toolbar, bulk bar and dialogs are sent once: later Livewire responses carry an empty `wire:ignore` placeholder for them (the `X-Tall-Ui-Chrome` request header), so each round trip carries the items only.',
    ],
    'do' => [
        'Check access to every id on the server: ids, names and folder ids come from the browser.',
        'Page big folders with `total` and `loadMoreAction` (24 to 50 items per page).',
        'Open files in `x-ui.file-preview` from `openAction`.',
    ],
    'dont' => [
        'Put an action menu on every item: act on the selection from the bulk bar.',
        'Render hundreds of items at once: use Load more.',
    ],
];
