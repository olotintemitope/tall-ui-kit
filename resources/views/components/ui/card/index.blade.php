{{--
    Card base: white, 1px `line` border, 8px radius, no shadow (cards are flat;
    x-ui.panel is the only elevated surface). Two ways to use it:
      - Compose: x-ui.card.header / title / description / content / footer children.
      - Props: `title` (heading role) with `description` (meta) and an `actions` slot
        draw a header above a 1px rule; the body gets 16px padding (`flush` drops it,
        e.g. for a table or divided rows); a `footer` slot sits below a 1px rule on
        `panel-footer`.
    No coloured or thick edges, icon tiles, coloured headers, hover lift or shadow.
    Cards are for peers in a grid. Never nest cards, never put one in a panel.
    The typed cards (summary, list, table, chart, detail, link, profile, selectable)
    are built on this. Props: title, description, flush, as (div). Slots: badge (after
    the title, e.g. a status x-ui.badge), actions, footer.
--}}
@props(['title' => null, 'description' => null, 'flush' => false, 'as' => 'div'])

<{{ $as }} {{ $attributes->class(['min-w-0 rounded-lg border border-line bg-card text-card-foreground']) }} data-card>
    @if ($title !== null || isset($actions))
        <div class="flex items-start justify-between gap-3 border-b border-line px-4 py-3" data-card-header>
            <div class="min-w-0">
                @if ($title !== null)
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <h3 class="type-heading">{{ $title }}</h3>
                        {{ $badge ?? '' }}
                    </div>
                @endif
                @if ($description)
                    <p class="type-meta">{{ $description }}</p>
                @endif
            </div>
            @isset($actions)
                <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
            @endisset
        </div>
        <div @class(['p-4' => ! $flush]) data-card-body>{{ $slot }}</div>
    @else
        {{ $slot }}
    @endif
    @isset($footer)
        <div {{ $footer->attributes->class(['type-meta flex flex-wrap items-center justify-between gap-2 rounded-b-lg border-t border-line bg-panel-footer px-4 py-2.5']) }} data-card-footer>{{ $footer }}</div>
    @endisset
</{{ $as }}>
