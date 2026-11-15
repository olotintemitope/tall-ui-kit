{{-- Small-caps section label (label role: 11px, 600, uppercase, 0.08em, ink-muted). Props: as (p). --}}
@props(['as' => 'p'])

<{{ $as }} {{ $attributes->class(['type-label']) }}>{{ $slot }}</{{ $as }}>
