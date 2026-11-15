{{--
    A plain row of x-ui.card.list: title (body-strong), an optional meta line and an
    optional value on the right (numeric; pass money through x-ui.money).
    Props: title, meta. Slot: value.
--}}
@props(['title', 'meta' => null])

<li {{ $attributes->class(['flex items-start justify-between gap-3 border-b border-line py-2.5']) }}>
    <span class="min-w-0">
        <span class="type-body-strong block wrap-anywhere">{{ $title }}</span>
        @if ($meta)
            <span class="type-meta block wrap-anywhere">{{ $meta }}</span>
        @endif
    </span>
    @if ($slot->hasActualContent())
        <span class="type-numeric shrink-0 text-right text-ink">{{ $slot }}</span>
    @endif
</li>
