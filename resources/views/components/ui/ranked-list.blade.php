{{--
    Ordered list of x-ui.ranked-item rows. Shows the empty message (meta) when
    there are no rows. Props: empty. Slot: x-ui.ranked-item rows.
--}}
@props(['empty' => 'Nothing to rank yet.'])

@if (! $slot->hasActualContent())
    <p class="type-meta py-4">{{ $empty }}</p>
@else
    <ol {{ $attributes }}>{{ $slot }}</ol>
@endif
