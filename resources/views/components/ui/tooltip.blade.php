{{--
    Hover and focus tooltip. Props: text, position (top|bottom|left|right; `placement` is
    an alias, as on x-ui.popover). Slot `content` replaces `text` for richer tips, e.g.
    a label with an x-ui.kbd (tone="dark"). Positioned `fixed`, so it escapes scrolling
    and overflow-hidden parents.
    WCAG 1.4.13: the tip stays while the pointer moves onto it (a 100ms grace on leave)
    and Escape hides it without closing a dialog around it. The tip describes the first
    focusable element in the slot (aria-describedby on that control, not the wrapper),
    unless its text is already the control's aria-label.
--}}
@props(['text' => null, 'position' => 'top', 'placement' => null])

@php
    $position = $placement ?? $position;
    $position = in_array($position, ['top', 'bottom', 'left', 'right'], true) ? $position : 'top';
@endphp

<span
    x-data="{
        open: false,
        timer: null,
        show() { clearTimeout(this.timer); this.open = true },
        hide(delay = 100) { clearTimeout(this.timer); this.timer = setTimeout(() => this.open = false, delay) },
        describe() {
            const target = this.$refs.trigger.querySelector('a[href], button, input, textarea, [tabindex]:not([tabindex=\'-1\'])') ?? this.$refs.trigger;
            const id = this.$id('tooltip');
            const tip = document.getElementById(id);
            if (! tip || (target.getAttribute('aria-label') ?? '').trim() === tip.textContent.trim()) return;
            const ids = (target.getAttribute('aria-describedby') ?? '').split(' ').filter((x) => x && x !== id);
            target.setAttribute('aria-describedby', [...ids, id].join(' '));
        },
    }"
    x-init="$nextTick(() => describe())"
    x-on:mouseenter="show()"
    x-on:mouseleave="hide()"
    x-on:focusin="show()"
    x-on:focusout="hide(0)"
    x-on:keydown.escape="if (open) { $event.preventDefault(); $event.stopPropagation(); open = false }"
    {{ $attributes->except('placement')->class(['inline-flex']) }}
>
    <span x-ref="trigger" class="inline-flex">{{ $slot }}</span>

    <span
        x-show="open"
        x-cloak
        x-transition.opacity.duration.100ms
        x-anchor.{{ $position }}.offset.6.fixed="$refs.trigger"
        x-bind:id="$id('tooltip')"
        role="tooltip"
        class="z-[60] w-max max-w-xs wrap-anywhere rounded-md bg-secondary px-2 py-1 font-body text-caption font-medium text-on-secondary"
    >{{ $content ?? $text }}</span>
</span>
