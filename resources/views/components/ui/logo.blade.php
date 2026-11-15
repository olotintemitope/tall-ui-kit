{{--
    The brand logo for headers, auth pages and anywhere outside x-ui.sidebar.header.
    config('tall-ui-kit.brand'): logo_light on light grounds, logo_dark in the dark
    colour scheme (both images are printed and CSS shows one, so a scheme change needs
    no reload). With only one logo it shows in both schemes; with none the brand name
    prints in the heading role. Props: href (null: no link), height (22, px).
--}}
@props(['href' => null, 'height' => 22])

@php
    $name = (string) config('tall-ui-kit.brand.name');
    $light = config('tall-ui-kit.brand.logo_light');
    $dark = config('tall-ui-kit.brand.logo_dark');
    $tag = $href ? 'a' : 'span';
@endphp

<{{ $tag }} @if ($href) href="{{ $href }}" @endif {{ $attributes->class(['inline-flex items-center', 'rounded-sm focus-ring' => $href]) }} data-logo>
    @if ($light && $dark)
        <img src="{{ $light }}" alt="{{ $name }}" style="height: {{ (int) $height }}px" class="w-auto dark:hidden">
        <img src="{{ $dark }}" alt="{{ $name }}" style="height: {{ (int) $height }}px" class="hidden w-auto dark:block">
    @elseif ($light || $dark)
        <img src="{{ $light ?: $dark }}" alt="{{ $name }}" style="height: {{ (int) $height }}px" class="w-auto">
    @else
        <span class="type-heading">{{ $name }}</span>
    @endif
</{{ $tag }}>
