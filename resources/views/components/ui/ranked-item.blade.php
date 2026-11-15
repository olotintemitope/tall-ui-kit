{{--
    One row of x-ui.ranked-list: optional rank, label (body-strong) with a sub line
    (meta), and a value (numeric) on the right. With `share`, the row becomes a
    compact bar row instead: label, a 4px chart-2 bar on a surface-muted track for
    the row's share of the largest value, then the value.
    Props: label, sub, rank, value (used when the slot is empty), share (0 to 1), href
    (the label becomes a link: ink, underlined on hover, focus ring; the row stays a list
    item so the list keeps its semantics).
    Slot: value.
--}}
@props(['label', 'sub' => null, 'rank' => null, 'value' => null, 'share' => null, 'href' => null])

@php
    $valueHtml = $slot->hasActualContent() ? $slot : ($value !== null ? number_format($value) : '');
@endphp

@if ($share !== null)
    <li {{ $attributes->class(['flex items-center gap-3 border-b border-line py-1.5']) }}>
        @if ($href)
            <a href="{{ $href }}" class="type-body w-24 shrink-0 wrap-anywhere rounded-sm text-ink hover:underline underline-offset-2 focus-ring">{{ $label }}</a>
        @else
            <span class="type-body w-24 shrink-0 wrap-anywhere">{{ $label }}</span>
        @endif
        <span class="h-1 flex-1 bg-surface-muted" aria-hidden="true">
            <span class="block h-full bg-chart-2 forced-color-adjust-none" style="width: {{ max(2, round($share * 100)) }}%"></span>
        </span>
        <span class="type-numeric w-10 shrink-0 text-right text-ink">{{ $valueHtml }}</span>
    </li>
@else
    <li {{ $attributes->class(['flex items-start gap-3 border-b border-line py-2']) }}>
        @if ($rank !== null)
            <span class="type-numeric w-5 shrink-0 text-right text-ink-muted">{{ $rank }}</span>
        @endif
        <span class="min-w-0 flex-1">
            @if ($href)
                <a href="{{ $href }}" class="type-body-strong block wrap-anywhere rounded-sm hover:underline underline-offset-2 focus-ring">{{ $label }}</a>
            @else
                <span class="type-body-strong block wrap-anywhere">{{ $label }}</span>
            @endif
            @if ($sub)
                <span class="type-meta block wrap-anywhere">{{ $sub }}</span>
            @endif
        </span>
        <span class="type-numeric shrink-0 text-right text-ink">{{ $valueHtml }}</span>
    </li>
@endif
