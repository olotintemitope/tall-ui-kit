<?php

return [
    'intro' => 'Page links for a Laravel paginator ("1–20 of 312", previous, pages, next), and a Load more button for long feeds.',
    'keyboard' => [
        ['Tab', 'Moves through previous, the page links and next.'],
        ['Enter', 'Opens that page (in Livewire, changes page and scrolls to `scrollTo`).'],
        ['Enter / Space', 'On Load more, loads the next batch; focus stays on the button.'],
    ],
    'a11y' => [
        'A `<nav>` named "Pagination"; pass `aria-label` when a page has two.',
        'The current page has `aria-current="page"`; previous and next are named ("Previous page", "Next page"), and an unavailable end is `aria-disabled`.',
        'Load more says "Showing 20 of 134" as a status, and its button shows its loading state while the action runs.',
    ],
    'do' => [
        'Use `$rows->links()` in Livewire: the kit\'s pagination views render this component.',
        'Use Load more for feeds and activity, pages for tables people return to.',
    ],
    'dont' => [
        'Rely on `auto` alone for Load more: the button stays as the keyboard and fallback path.',
    ],
    'events' => [
        'In a `WithPagination` component `$rows->links()` passes `livewire` for you; pass it yourself when you render `<x-ui.pagination :paginator="$rows" livewire />`.',
        'Load more calls `action` (default `loadMore`) on the Livewire component; it hides once `shown` reaches `total`.',
    ],
];
