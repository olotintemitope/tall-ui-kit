{{--
    An image thumbnail that opens the full image in a dialog (promoted from the support
    ticket attachments). The thumbnail is a button (`size` px square, 8px radius,
    object-cover, lazy) named "View <alt>"; the dialog (x-ui.dialog, size xl) shows
    x-ui.figure with fit contain at the image's own aspect, capped to 55vh tall, a title
    and description, and "Open full size" (new tab) and Close in the footer. Focus
    returns to the thumbnail on close.
    Props: name (unique dialog name), src (full image), thumb (thumbnail URL, default
    src), alt (required: what the image shows), title (dialog title, default alt),
    description, width, height (intrinsic size: picks the aspect), size (thumbnail px,
    96), fullUrl (Open full size, default src; null hides it).
--}}
@props(['name', 'src', 'thumb' => null, 'alt', 'title' => null, 'description' => null, 'width' => null, 'height' => null, 'size' => 96, 'fullUrl' => false])

@php
    $ratio = $width && $height ? $width / $height : 4 / 3;
    $aspect = collect(['16/9' => 16 / 9, '3/2' => 1.5, '4/3' => 4 / 3, '1/1' => 1.0, '9/16' => 9 / 16])->sortBy(fn ($r) => abs($r - $ratio))->keys()->first();
    $full = $fullUrl === false ? $src : $fullUrl;
@endphp

<div {{ $attributes->whereDoesntStartWith('data-')->class(['inline-block']) }} data-lightbox>
    <button type="button" {{ $attributes->whereStartsWith('data-') }} class="block overflow-hidden rounded-lg border border-line bg-surface-muted hover:border-control-hover focus-ring" style="width: {{ (int) $size }}px; height: {{ (int) $size }}px" x-on:click="$dispatch('open-dialog', @js($name))" aria-label="View {{ $alt }}" data-lightbox-thumb>
        <img src="{{ $thumb ?? $src }}" alt="" width="{{ (int) $size }}" height="{{ (int) $size }}" loading="lazy" decoding="async" class="size-full object-cover">
    </button>
    <x-ui.dialog :name="$name" :title="$title ?? $alt" :description="$description" size="xl" data-image-lightbox>
        <x-ui.figure :src="$src" :alt="$alt" :aspect="$aspect" fit="contain" :width="$width" :height="$height" class="mx-auto w-full" :style="'max-width: min(100%, calc(55vh * '.round($ratio, 4).'))'" />
        <x-slot:footer>
            @if ($full)
                <x-ui.button variant="outline" size="sm" :href="$full" target="_blank" rel="noopener">Open full size<span class="sr-only"> (opens in new tab)</span></x-ui.button>
            @endif
            <x-ui.button size="sm" x-on:click="close()" autofocus>Close</x-ui.button>
        </x-slot:footer>
    </x-ui.dialog>
</div>
