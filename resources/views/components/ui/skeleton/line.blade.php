{{--
    One skeleton bar inside a line box of a type role's line height, so stacked bars
    take exactly the height of the text they stand in for.
    Props: role (label 16 | caption 16 | meta 18 | body 20 | figure 26), width (a w-* class).
--}}
@props(['role' => 'body', 'width' => 'w-24'])

@php
    [$box, $bar] = match ($role) {
        'label', 'caption' => ['h-4', 'h-2.5'],
        'meta' => ['h-4.5', 'h-3'],
        'figure' => ['h-6.5', 'h-5'],
        default => ['h-5', 'h-3.5'],
    };
@endphp

<div {{ $attributes->class(['flex items-center', $box]) }} aria-hidden="true"><x-ui.skeleton class="{{ $bar }} {{ $width }}" /></div>
