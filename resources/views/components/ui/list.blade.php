{{--
    List of short items (write the <li> elements in the slot). Body text, 6px between
    items, markers in ink-muted. Props: type (ul|ol), variant (default with markers |
    plain: no markers or indent, for stacked lines that are not a sentence list).
--}}
@props(['type' => 'ul', 'variant' => 'default'])

@php
    $tag = $type === 'ol' ? 'ol' : 'ul';
    $plain = $variant === 'plain';
@endphp

<{{ $tag }} {{ $attributes->class([
    'flex flex-col gap-1.5 type-body',
    'list-none' => $plain,
    'pl-5 marker:text-ink-muted' => ! $plain,
    'list-disc' => ! $plain && $tag === 'ul',
    'list-decimal' => ! $plain && $tag === 'ol',
]) }}>{{ $slot }}</{{ $tag }}>