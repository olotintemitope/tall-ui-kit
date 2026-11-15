{{--
    Initials on a neutral circle, or a photo. Props: name (initials come from the first two
    words; the photo's alt text), initials (override), src (a photo: fixed size,
    object-cover, lazy, decoded async; if it fails to load the initials show instead),
    alt (overrides the alt text; "" when the name is printed beside the avatar),
    size (xs 20 | sm 24 | default 28 | lg 36), tone (light|dark, for the dark admin sidebar).
    Without src the avatar is decorative (aria-hidden): the name is always printed beside it.
--}}
@props(['name' => '', 'initials' => null, 'size' => 'default', 'tone' => 'light', 'src' => null, 'alt' => null])

@php
    $letters = $initials ?? collect(preg_split('/\s+/', trim((string) $name)))
        ->filter()
        ->take(2)
        ->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');

    $sizes = [
        'xs' => 'size-5 text-[11px]',
        'sm' => 'size-6 text-[11px]',
        'default' => 'size-7 text-[12px]',
        'lg' => 'size-9 text-[13px]',
    ];
@endphp

@if ($src)
<span
    x-data="{ failed: false }"
    {{ $attributes->class([
        'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full font-body font-semibold leading-none select-none',
        $sizes[$size] ?? $sizes['default'],
        $tone === 'dark' ? 'bg-white/12 text-white' : 'bg-surface-muted text-ink ring-1 ring-inset ring-line',
    ]) }}
    data-avatar="image"
>
    <span x-show="failed" x-cloak aria-hidden="true">{{ $letters !== '' ? $letters : '?' }}</span>
    <img src="{{ $src }}" alt="{{ $alt ?? $name }}" loading="lazy" decoding="async" class="absolute inset-0 size-full object-cover" x-show="! failed" x-on:error="failed = true">
</span>
@else
<span
    {{ $attributes->class([
        'inline-flex shrink-0 items-center justify-center rounded-full font-body font-semibold leading-none select-none',
        $sizes[$size] ?? $sizes['default'],
        $tone === 'dark' ? 'bg-white/12 text-white' : 'bg-surface-muted text-ink ring-1 ring-inset ring-line',
    ]) }}
    @if ($name) title="{{ $name }}" @endif
    aria-hidden="true"
>{{ $letters !== '' ? $letters : '?' }}</span>
@endif
