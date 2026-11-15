{{--
    A non-modal popover anchored to a trigger, for small panels of content or controls
    (a curator's details, a filter's help, a colour legend). Built on the Popover API
    (`popover="auto"`): the browser gives light dismiss, Escape, the top layer (never
    clipped by overflow) and returning focus; Alpine anchors it (x-anchor) and mirrors
    aria-expanded on the trigger. Where the API is missing, Alpine shows the same panel
    in place and handles Escape and a click outside. Not for menus (x-ui.dropdown: roving
    focus and submenus) or hover hints (x-ui.tooltip: no click, no focusable content).
    Look: white, 1px `panel-border`, 8px radius, `shadow-popover`, 12px padding.
    Props: label (the trigger's text, an outline sm button), placement (bottom-start),
    width (w-72), title (optional heading in the subheading role). Slots: trigger
    (replaces the default button; must be a single <button>), default (content).
--}}
@props(['label' => 'Details', 'placement' => 'bottom-start', 'width' => 'w-72', 'title' => null])

@php
    $placement = in_array($placement, ['top', 'top-start', 'top-end', 'bottom', 'bottom-start', 'bottom-end', 'left', 'right'], true) ? $placement : 'bottom-start';
    // Placed below stays below (.noflip); the page scrolls to show it instead.
    $flip = str_starts_with($placement, 'bottom') ? '.noflip' : '';
    $panelId = 'popover-'.substr(md5($label.$title.uniqid()), 0, 8);
@endphp

<div
    x-data="uiPopover"
    x-on:keydown.escape="if (open && ! native) { $event.stopPropagation(); close() }"
    x-on:click.outside="if (open && ! native) open = false"
    {{ $attributes->class(['relative inline-block']) }}
    data-popover
>
    <span x-ref="anchor" class="contents" x-on:click="toggle()">
        @isset($trigger)
            {{ $trigger }}
        @else
            <x-ui.button variant="outline" size="sm">
                {{ $label }}
                <x-ui.icon name="chevron-down" class="size-4" />
            </x-ui.button>
        @endisset
    </span>
    <div
        x-ref="panel"
        id="{{ $panelId }}"
        popover
        x-show="native || open"
        x-anchor.{{ $placement }}{{ $flip }}.offset.6="$refs.anchor.firstElementChild"
        x-effect="open && $revealBelow($el, $refs.anchor.firstElementChild)"
        @if ($title) role="dialog" aria-labelledby="{{ $panelId }}-title" @else role="group" aria-label="{{ $label }}" @endif
        class="ui-popover z-50 rounded-lg border border-panel-border bg-popover p-3 text-left shadow-popover {{ $width }}"
        data-popover-panel
    >
        @if ($title)
            <h3 id="{{ $panelId }}-title" class="type-subheading">{{ $title }}</h3>
        @endif
        <div @class(['type-body', 'mt-1' => $title])>{{ $slot }}</div>
    </div>
</div>
