{{--
    Sidebar header: the logo (white on dark, black on light), the collapse toggle (the
    rail; "Close menu" in the mobile drawer) and the Cmd+K search trigger, which
    dispatches `open-command-palette`. Props: home (logo link), search (true),
    searchLabel (Search), and a `logo` slot that replaces the configured logo
    (config('tall-ui-kit.brand'): logo_light, logo_dark, name). Reads variant, collapsible
    and navigate (wire:navigate on the logo link) from x-ui.sidebar.
--}}
@aware(['variant' => 'light', 'collapsible' => true, 'navigate' => true])
@props(['home' => '/', 'search' => true, 'searchLabel' => 'Search', 'logo' => null])

@php $tone = \TallUiKit\Support\SidebarTones::tones($variant); @endphp

<div class="flex h-14 items-center justify-between gap-2 px-4 rail:justify-center rail:px-0">
    <a href="{{ $home }}" @if ($navigate) wire:navigate @endif class="flex items-center rounded-sm focus-ring rail:hidden">
        @if ($logo)
            {{ $logo }}
        @elseif ($tone['logo'])
            <img src="{{ $tone['logo'] }}" alt="{{ config('tall-ui-kit.brand.name') }}" width="104" height="24" class="h-[22px] w-auto">
        @else
            <span @class(['type-heading', $tone['name']])>{{ config('tall-ui-kit.brand.name') }}</span>
        @endif
    </a>

    @if ($collapsible)
        <button
            type="button"
            x-on:click="toggle()"
            x-bind:aria-label="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            x-bind:aria-expanded="collapsed ? 'false' : 'true'"
            aria-label="Collapse sidebar"
            data-sidebar-toggle
            @class(['flex size-8 items-center justify-center rounded-md transition-colors duration-100 focus-ring group-data-[sidebar-root=drawer]/sidebar:hidden', $tone['control']])
        >
            <x-ui.icon name="panel-left" :stroke-width="1.75" class="size-4" />
        </button>
    @endif
    <button type="button" x-on:click="closeDrawer()" aria-label="Close menu" @class(['hidden size-8 items-center justify-center rounded-md transition-colors duration-100 focus-ring group-data-[sidebar-root=drawer]/sidebar:flex', $tone['control']])>
        <x-ui.icon name="x" class="size-4" />
    </button>
</div>

@if ($search)
    <div class="px-3 pb-2 rail:px-2">
        <button
            type="button"
            x-on:click="closeDrawer(); $dispatch('open-command-palette')"
            aria-label="{{ $searchLabel }} pages"
            aria-keyshortcuts="Meta+K Control+K"
            @class(['flex min-h-8 w-full items-center gap-2 rounded-md border px-2.5 type-body transition-colors duration-100 focus-ring rail:justify-center rail:px-0', $tone['search']])
            data-sidebar-search
        >
            <x-ui.icon name="search" :stroke-width="1.75" class="size-4 shrink-0" />
            <span class="flex-1 text-left rail:hidden">{{ $searchLabel }}</span>
            <span class="hidden sm:flex rail:hidden!">
                <x-ui.kbd :tone="$tone['kbd']" x-data x-text="/Mac|iPhone|iPad/.test(navigator.userAgent) ? '⌘K' : 'Ctrl K'">⌘K</x-ui.kbd>
            </span>
        </button>
    </div>
@endif
