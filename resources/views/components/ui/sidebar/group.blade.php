{{--
    An expandable parent with nested x-ui.sidebar.item children (one level deep, pass
    `nested` on them). The parent is a disclosure button (aria-expanded, a turning
    chevron, x-collapse height); it shows active (full-strength text at 500) when a
    child is active and starts open then. In the icon rail the children hide and the
    parent takes the active fill; clicking it expands the sidebar and the group.
    Props: label, icon, active (null: true when a child in the slot is the current
    page; then re-marked with its children after wire:navigate), open (null: follows
    active), count.
--}}
@aware(['variant' => 'light'])
@props(['label', 'icon' => null, 'active' => null, 'open' => null, 'count' => null])

@php
    $tone = \TallUiKit\Support\SidebarTones::tones($variant);
    $matched = $active === null;
    $active ??= str_contains((string) $slot, 'aria-current="page"');
    $open ??= $active;
    $railActive = $variant === 'dark'
        ? 'rail:bg-white/[0.10] rail:ring-1 rail:ring-inset rail:ring-white/40'
        : 'rail:bg-black/[0.06] rail:ring-1 rail:ring-inset rail:ring-control-hover';
@endphp

<li x-data="{ open: @js((bool) $open), tip: false }" x-id="['sidebar-group']" data-nav-group @if ($active) data-active @endif @if ($matched) data-nav-match @endif>
    <button
        type="button"
        x-ref="link"
        x-on:click="if (railed($el)) { expand(); open = true } else { open = ! open }"
        x-bind:aria-expanded="open && ! railed($el) ? 'true' : 'false'"
        x-bind:aria-controls="$id('sidebar-group')"
        x-bind:aria-label="railed($el) ? @js($label) : null"
        aria-expanded="{{ $open ? 'true' : 'false' }}"
        x-on:mouseenter="tip = railed($el)"
        x-on:mouseleave="tip = false"
        x-on:focus="tip = railed($el)"
        x-on:blur="tip = false"
        data-nav-item
        @class([
            'group/nav flex min-h-8 py-1.5 w-full items-center gap-2.5 rounded-md px-2.5 text-left type-body transition-colors duration-100 focus-ring rail:justify-center rail:px-0',
            $tone['item'],
            $tone['name'].' font-medium '.$railActive => $active,
        ])
    >
        @if ($icon)
            <x-ui.icon :name="$icon" :stroke-width="1.75" data-nav-icon :class="\Illuminate\Support\Arr::toCssClasses(['size-4 shrink-0', $tone['iconActive'] => $active, $tone['icon'] => ! $active])" />
        @endif
        <span class="flex-1 wrap-anywhere rail:hidden">{{ $label }}</span>
        @if ($count !== null)
            <span @class(['shrink-0 font-mono text-[12px] leading-4 tabular-nums rail:hidden', $tone['count']]) data-nav-count>{{ number_format($count) }}</span>
        @endif
        <x-ui.icon name="chevron-right" :class="\Illuminate\Support\Arr::toCssClasses(['motion-chevron size-3.5 shrink-0 rail:hidden', $tone['chevron']])" />
    </button>

    <span x-show="tip" x-cloak x-transition.opacity.duration.100ms x-anchor.right.offset.10.fixed="$refs.link" role="tooltip" class="z-[60] w-max max-w-xs wrap-anywhere rounded-md bg-secondary px-2 py-1 font-body text-caption font-medium text-white pointer-events-none ring-1 ring-white/10">{{ $label }}</span>

    <ul x-bind:id="$id('sidebar-group')" x-show="open && ! railed($el)" x-collapse.duration.150ms @unless ($open) x-cloak @endunless class="mt-px flex flex-col gap-px" aria-label="{{ $label }}">
        {{ $slot }}
    </ul>
</li>
