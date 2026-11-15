{{--
    Video with the browser's own controls (keyboard, captions menu, fullscreen and
    picture-in-picture come from the browser). A fixed aspect box (16/9 default, 9/16
    for vertical social clips) with an 8px radius and a 1px `line` edge on a
    `surface-muted` fill, so the frame never jumps while loading. preload="metadata"
    unless given; never autoplays. Captions: put <track> elements in the slot
    (kind="captions", srclang, label; mark one `default`).
    Props: src, poster, type (video/mp4), aspect (16/9 | 9/16 | 4/3 | 1/1), title (the
    accessible name), preload (metadata).
--}}
@props(['src', 'poster' => null, 'type' => 'video/mp4', 'aspect' => '16/9', 'title' => null, 'preload' => 'metadata'])

@php
    $aspects = ['16/9' => 'aspect-video', '9/16' => 'aspect-[9/16] max-w-sm', '4/3' => 'aspect-[4/3]', '1/1' => 'aspect-square'];
@endphp

<div {{ $attributes->class(['relative w-full overflow-hidden rounded-lg border border-line bg-surface-muted', $aspects[$aspect] ?? $aspects['16/9']]) }} data-video>
    <video
        class="absolute inset-0 size-full object-contain"
        controls
        playsinline
        preload="{{ $preload }}"
        @if ($poster) poster="{{ $poster }}" @endif
        @if ($title) aria-label="{{ $title }}" @endif
    >
        <source src="{{ $src }}" type="{{ $type }}">
        {{ $slot }}
        <p class="type-meta p-4">This browser cannot play the video. <a href="{{ $src }}" class="underline">Download it</a>.</p>
    </video>
</div>
