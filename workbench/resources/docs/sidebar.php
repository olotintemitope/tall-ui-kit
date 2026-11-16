<?php

return [
    'intro' => 'The app shell sidebar: a fixed 240px rail that collapses to a 56px icon rail from lg and becomes a drawer on phones. The examples use `:fixed="false"`, the static preview block.',
    'keyboard' => [
        ['Tab', 'Moves through the header (logo, collapse, search), the nav items and the account menu.'],
        ['Down / Up', 'Moves between nav items, wrapping.'],
        ['Home / End', 'Jumps to the first or last nav item.'],
        ['Enter / Space', 'On a group or a collapsible section, opens or closes it.'],
        ['Escape', 'Closes the mobile drawer (a native modal dialog) and the account menu.'],
    ],
    'a11y' => [
        'The current item has `aria-current="page"` and a fill and inset ring, never an accent bar.',
        'In the icon rail labels hide, each link is named by `aria-label` and a tooltip shows the label on hover and focus.',
        'Disabled items keep their contrast, lose the link and point to their `reason` with `aria-describedby`.',
        'External items open in a new tab and say "(opens in new tab)" to screen readers.',
        'Groups and collapsible sections are disclosure buttons with `aria-expanded`.',
    ],
    'do' => [
        'Feed the sidebar, the drawer and the command palette from one list of destinations.',
        'Give the shell sidebar a `persist` key that changes with its items.',
        'Use `reload` for items outside the shell layout, and keep log out and area switches as full loads.',
    ],
    'dont' => [
        'Fade disabled items or mark the active item with a coloured edge.',
        'Nest groups more than one level deep.',
        'Use `wire:navigate.hover` on items.',
    ],
    'events' => [
        'Open the mobile drawer with the `open-sidebar-drawer` window event (the top bar\'s menu button).',
        'With `navigate` (the default) items, the logo and Settings use `wire:navigate`; `persist` keeps the element across page changes and `resources/js/navigate.js` re-marks the current item.',
        '`area` persists the collapse in localStorage; print `<x-ui.head :area="...">` so it applies before first paint.',
    ],
];
