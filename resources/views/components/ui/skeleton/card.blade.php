{{--
    Loading state for x-ui.card.summary: the same card box with label (16), figure (26)
    and a delta line (18). Props: label, sub (adds the 16px sub line).
--}}
@props(['label' => 'Loading', 'sub' => false])

<div {{ $attributes->class(['rounded-lg border border-line bg-white p-4']) }} role="status" data-skeleton="card">
    <span class="sr-only">{{ $label }}</span>
    <x-ui.skeleton.line role="label" width="w-20" />
    <x-ui.skeleton.line role="figure" width="w-28" class="mt-1" />
    <x-ui.skeleton.line role="meta" width="w-24" class="mt-1" />
    @if ($sub)
        <x-ui.skeleton.line role="caption" width="w-20" class="mt-1" />
    @endif
</div>
