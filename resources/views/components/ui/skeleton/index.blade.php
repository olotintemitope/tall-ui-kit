{{--
    Loading placeholder block: a static `surface-muted` block with a slow opacity pulse
    (none under reduced motion; no shimmer). Size it with classes; corners default to 4px.
    Presets match the real component's height exactly, so nothing jumps when content
    arrives: .text, .avatar, .table, .stat / .stats, .card, .chart, .grid, .list.
    Each preset is one role="status" with its blocks hidden from screen readers.
--}}
<div {{ $attributes->class(['animate-pulse motion-reduce:animate-none rounded bg-surface-muted']) }}></div>
