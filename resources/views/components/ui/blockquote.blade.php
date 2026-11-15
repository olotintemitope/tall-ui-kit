{{--
    Quotation: 16px indent, body text, no side bar or fill. Props: attribution (meta
    line under the quote), cite (source URL for the cite attribute).
--}}
@props(['attribution' => null, 'cite' => null])

<figure {{ $attributes->class(['pl-4']) }}>
    <blockquote @if ($cite) cite="{{ $cite }}" @endif class="type-body">{{ $slot }}</blockquote>
    @if ($attribution)
        <figcaption class="mt-2 type-meta">{{ $attribution }}</figcaption>
    @endif
</figure>