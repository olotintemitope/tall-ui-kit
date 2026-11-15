{{--
    Heading. `level` (1 to 6) sets the tag for the document outline; `size` sets the
    look and is independent of it. Default size by level: h1 title, h2 heading,
    h3 subheading, h4 to h6 label. Props: level (2), size (title|heading|subheading|label).
--}}
@props(['level' => 2, 'size' => null])

@php
    $level = max(1, min(6, (int) $level));
    $size = in_array($size, ['title', 'heading', 'subheading', 'label'], true)
        ? $size
        : match ($level) {
            1 => 'title',
            2 => 'heading',
            3 => 'subheading',
            default => 'label',
        };
@endphp

<h{{ $level }} {{ $attributes->class(['type-'.$size]) }} data-heading-size="{{ $size }}">{{ $slot }}</h{{ $level }}>