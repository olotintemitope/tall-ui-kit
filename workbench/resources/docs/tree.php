<?php

return [
    'intro' => 'A nested list the user expands and collapses, on the WAI-ARIA tree view pattern. Rows can be selectable (one), checkable (many, with mixed parents) or links, and children can load lazily from Livewire.',
    'keyboard' => [
        ['Down / Up', 'Moves to the next or previous visible row.'],
        ['Right', 'Opens a closed parent, or moves to its first child.'],
        ['Left', 'Closes an open parent, or moves to the parent.'],
        ['Home / End', 'Moves to the first or last visible row.'],
        ['Enter', 'Selects the row, follows its `href`, or toggles a parent when nothing is selectable.'],
        ['Space', 'Selects the row, or toggles its check with `checkable`.'],
        ['*', 'Opens every sibling of the row.'],
        ['A to Z', 'Jumps to the next row whose label starts with what was typed.'],
    ],
    'a11y' => [
        '`role="tree"` with `treeitem` rows carrying `aria-level`, `aria-setsize`, `aria-posinset` and `aria-expanded`; children are `role="group"`.',
        'One row is in the Tab order (roving tabindex).',
        'Selection is `aria-selected`; checks are `aria-checked` true, false or mixed.',
        'A lazily loading row is `aria-busy` and says "Loading"; a failure says so, and Right tries again.',
        '`label` is required: it names the tree.',
    ],
    'do' => [
        'Bind with `wire:model` or `x-model`: a single id with `selectable`, a list of ids with `checkable`.',
        'Check in the `lazy` method that the user may see the node: the id comes from the browser.',
        'Change the tree\'s `wire:key` to rebuild it from new data.',
    ],
    'dont' => [
        'Use a tree for a flat list or for site navigation menus.',
        'Mix `selectable` and `href` rows in one tree.',
    ],
    'events' => [
        '`wire:model` gets the selected id, or every checked id (fully checked parents included).',
        '`lazy="children"` calls `$wire.call(\'children\', id)` the first time a node with `hasChildren` opens; return nodes in the same shape from a `#[Renderless]` method.',
    ],
];
