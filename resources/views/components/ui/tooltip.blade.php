{{--
    Hover and focus tooltip. Props: text, position (top|bottom|left|right). Slot
    `content` replaces `text` for richer tips, e.g. a label with an x-ui.kbd
    (tone="dark"). Positioned `fixed`, so it escapes scrolling and overflow-hidden parents.
--}}
@props(['text' => null, 'position' => 'top'])

@php
    $position = in_array($position, ['top', 'bottom', 'left', 'right'], true) ? $position : 'top';
@endphp

<span
    x-data="{ open: false }"
    x-on:mouseenter="open = true"
    x-on:mouseleave="open = false"
    x-on:focusin="open = true"
    x-on:focusout="open = false"
    x-on:keydown.escape="open = false"
    {{ $attributes->class(['inline-flex']) }}
>
    <span x-ref="trigger" class="inline-flex" x-bind:aria-describedby="open ? $id('tooltip') : null">{{ $slot }}</span>

    <span
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        x-anchor.{{ $position }}.offset.6.fixed="$refs.trigger"
        x-bind:id="$id('tooltip')"
        role="tooltip"
        class="z-[60] w-max max-w-xs wrap-anywhere rounded-md bg-secondary px-2 py-1 font-body text-caption font-medium text-white pointer-events-none"
    >{{ $content ?? $text }}</span>
</span>
