{{--
    Inline text link: primary-text (5.21:1), always underlined 3px below the text,
    ink on hover. Inherits the surrounding size. target="_blank" adds rel="noopener",
    a small arrow and "(opens in new tab)" for screen readers. For a "View all" link
    to a fuller page use x-ui.link-arrow. Props: href.
--}}
@props(['href'])

@php
    $external = $attributes->get('target') === '_blank';
    $rel = collect(explode(' ', (string) $attributes->get('rel')))
        ->filter()
        ->when($external, fn ($rel) => $rel->push('noopener'))
        ->unique()
        ->implode(' ');
@endphp

<a
    href="{{ $href }}"
    @if ($rel !== '') rel="{{ $rel }}" @endif
    {{ $attributes->except('rel')->class(['rounded-sm text-primary-text underline decoration-1 underline-offset-3 transition-colors duration-100 hover:text-ink focus-ring']) }}
>@if ($external)<span>{{ $slot }}</span><span class="sr-only"> (opens in new tab)</span><span class="ml-0.5 inline-block align-[-2px]"><x-ui.icon name="arrow-up-right" class="size-3.5" /></span>@else{{ $slot }}@endif</a>