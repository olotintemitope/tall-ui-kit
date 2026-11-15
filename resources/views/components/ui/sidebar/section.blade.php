{{--
    A labelled block of sidebar items. The label is in the label role (small caps).
    `collapsible` turns it into a disclosure button (aria-expanded, a turning chevron)
    whose height animates with x-collapse (150ms) and whose state persists per area
    (localStorage.<prefix>sidebar_<area>_<key>). In the icon rail the label becomes
    a 1px rule and the items always show. Props: label (null: no heading), key,
    collapsible (false), open (true).
--}}
@aware(['variant' => 'light', 'area' => null])
@props(['label' => null, 'key' => null, 'collapsible' => false, 'open' => true])

@php
    $tone = \TallUiKit\Support\SidebarTones::tones($variant);
    $slug = $key ?? \Illuminate\Support\Str::slug((string) $label);
    $persist = $collapsible && $area && $slug;
    $state = $persist
        ? '$persist('.\Illuminate\Support\Js::from((bool) $open).').as('.\Illuminate\Support\Js::from(config('tall-ui-kit.storage_prefix', 'tallui_').'sidebar_'.$area.'_'.$slug).')'
        : \Illuminate\Support\Js::from((bool) $open);
@endphp

<div x-data="{ open: {!! $state !!} }" x-id="['sidebar-section']" @class(['pt-4 first:pt-1' => $label, 'pt-1' => ! $label]) data-sidebar-section="{{ $slug }}">
    @if ($label)
        @if ($collapsible)
            <button
                type="button"
                x-on:click="open = ! open"
                x-bind:aria-expanded="open ? 'true' : 'false'"
                x-bind:aria-controls="$id('sidebar-section')"
                aria-expanded="{{ $open ? 'true' : 'false' }}"
                @class(['flex min-h-6 w-full items-center justify-between rounded-sm px-2.5 mb-1 type-label transition-colors duration-100 focus-ring rail:hidden', $tone['label']])
                data-sidebar-section-toggle
            >
                <span>{{ $label }}</span>
                <x-ui.icon name="chevron-right" class="motion-chevron size-3.5" />
            </button>
        @else
            <p @class(['px-2.5 pb-2 type-label rail:hidden', $tone['label']])>{{ $label }}</p>
        @endif
        <div @class(['hidden mx-2 mb-2 border-t rail:block', $tone['line']]) aria-hidden="true"></div>
    @endif

    <ul
        x-bind:id="$id('sidebar-section')"
        x-show="open || railed($el)"
        x-collapse.duration.150ms
        @if ($collapsible && ! $open) x-cloak @endif
        class="flex flex-col gap-px"
        @if ($label) aria-label="{{ $label }}" @endif
    >
        {{ $slot }}
    </ul>
</div>
