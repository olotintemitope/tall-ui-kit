{{--
    One row of x-ui.kv-list: label (body) with an optional sub line (caption), value
    (numeric, ink). Pass money through x-ui.money in the slot so privacy mode masks it.
    Props: label, sub. Slots: default (value), subline (a sub line with markup,
    e.g. masked money; replaces `sub`).
--}}
@props(['label', 'sub' => null])

<div {{ $attributes->class(['flex items-baseline justify-between gap-4 border-b border-line py-2.5']) }}>
    <dt class="min-w-0">
        <span class="type-body block">{{ $label }}</span>
        @if (isset($subline) || $sub)
            <span class="type-caption block">{{ $subline ?? $sub }}</span>
        @endif
    </dt>
    <dd class="type-numeric shrink-0 text-right text-ink">{{ $slot }}</dd>
</div>
