{{--
    Link card: the whole card is one anchor. Title in body-strong, a meta line and an
    ink-muted arrow. Hover fills surface-subtle; keyboard focus shows the focus ring.
    Props: href, title, meta.
--}}
@props(['href', 'title', 'meta' => null])

<a href="{{ $href }}" {{ $attributes->class(['group flex min-w-0 items-center gap-3 rounded-lg border border-line bg-white p-4 transition-colors duration-100 hover:bg-surface-subtle focus-ring']) }} data-card-link>
    <span class="min-w-0 flex-1">
        <span class="type-body-strong block wrap-anywhere">{{ $title }}</span>
        @if ($meta)
            <span class="type-meta block">{{ $meta }}</span>
        @endif
    </span>
    <x-ui.icon name="arrow-right" class="size-4 shrink-0 text-ink-muted transition-colors duration-100 group-hover:text-ink" />
</a>
