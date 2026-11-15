{{--
    An image in a <figure> with an optional caption: fixed aspect box (no layout
    shift), 8px radius, 1px `line` edge, `surface-muted` while loading, object-cover.
    Lazy by default (loading="lazy", decoding="async"); pass `eager` for the largest
    image above the fold (fetchpriority="high"). Responsive: `srcset` and `sizes` on
    the <img>, and `sources` for a <picture> (AVIF or WebP first, or art direction by
    media query). Alt text is required: `alt=""` is only accepted with `decorative`,
    otherwise it throws in local and testing.
    Props: src, alt, decorative, caption (or a `caption` slot), aspect (16/9 | 4/3 |
    1/1 | 3/2 | 9/16), srcset, sizes ("100vw"), sources (list of ['srcset', 'type',
    'media', 'sizes']), width, height (intrinsic, recommended), eager, fit (cover |
    contain).
--}}
@props([
    'src', 'alt' => null, 'decorative' => false, 'caption' => null, 'aspect' => '16/9', 'srcset' => null, 'sizes' => '100vw',
    'sources' => [], 'width' => null, 'height' => null, 'eager' => false, 'fit' => 'cover',
])

@php
    if (! $decorative && blank($alt)) {
        if (app()->environment('local', 'testing')) {
            throw new \InvalidArgumentException('x-ui.figure needs alt text, or `decorative` for an image that adds nothing.');
        }
        $alt = '';
    }
    $aspects = ['16/9' => 'aspect-video', '4/3' => 'aspect-[4/3]', '1/1' => 'aspect-square', '3/2' => 'aspect-[3/2]', '9/16' => 'aspect-[9/16]'];
@endphp

<figure {{ $attributes->class(['flex min-w-0 flex-col gap-2']) }} data-figure>
    <div @class(['relative w-full overflow-hidden rounded-lg border border-line bg-surface-muted', $aspects[$aspect] ?? $aspects['16/9']])>
        <picture>
            @foreach ($sources as $source)
                <source srcset="{{ $source['srcset'] }}" @isset($source['type']) type="{{ $source['type'] }}" @endisset @isset($source['media']) media="{{ $source['media'] }}" @endisset sizes="{{ $source['sizes'] ?? $sizes }}">
            @endforeach
            <img
                src="{{ $src }}"
                alt="{{ $decorative ? '' : $alt }}"
                @if ($srcset) srcset="{{ $srcset }}" sizes="{{ $sizes }}" @endif
                @if ($width) width="{{ $width }}" @endif
                @if ($height) height="{{ $height }}" @endif
                loading="{{ $eager ? 'eager' : 'lazy' }}"
                decoding="async"
                @if ($eager) fetchpriority="high" @endif
                @class(['absolute inset-0 size-full', 'object-cover' => $fit === 'cover', 'object-contain' => $fit === 'contain'])
            >
        </picture>
    </div>
    @if ($caption)
        <figcaption class="type-caption">{{ $caption }}</figcaption>
    @endif
</figure>
