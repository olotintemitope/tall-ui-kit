{{--
    A day of x-ui.timeline in slot mode: a heading ("Today", "14 Nov 2026") and its own
    <ol> of x-ui.timeline.item rows. Data mode (`items` with `groupByDay`) builds these
    itself. Props: label, headingLevel (3), id.
--}}
@props(['label', 'headingLevel' => 3, 'id' => null])

@php
    $id ??= 'timeline-day-'.\Illuminate\Support\Str::slug($label);
    $tag = 'h'.max(2, min(6, (int) $headingLevel));
@endphp

<li {{ $attributes->class(['min-w-0 list-none']) }} data-timeline-day>
    <{{ $tag }} id="{{ $id }}" class="type-label pt-1 pb-2">{{ $label }}</{{ $tag }}>
    <ol aria-labelledby="{{ $id }}" class="min-w-0">{{ $slot }}</ol>
</li>
