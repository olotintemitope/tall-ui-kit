{{-- Nested comments with a depth limit and more replies --}}
<x-ui.comment-thread label="Docs comments" :depth="2" continue-href="#thread-:key" now="2026-11-16 14:00:00" id="docs-thread" :comments="[
    ['key' => 'x1', 'author' => 'Funmi Lawal', 'time' => '2026-11-16 12:10:00', 'body' => 'Is the 3pm slot still open?', 'more' => 2, 'replies' => [
        ['key' => 'x1-1', 'author' => 'Ada Obi', 'time' => '2026-11-16 12:20:00', 'body' => 'Yes, until Friday.', 'replies' => [
            ['key' => 'x1-1-1', 'author' => 'Funmi Lawal', 'time' => '2026-11-16 12:25:00', 'body' => 'Booked, thanks.'],
        ]],
    ]],
    ['key' => 'x2', 'author' => 'Gbenga Musa', 'time' => '2026-11-16 13:02:00', 'body' => 'Will the recordings be shared?', 'edited' => true],
]" />
