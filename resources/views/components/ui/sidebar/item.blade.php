{{--
    One sidebar destination: 16px icon, label, then an optional muted count (numeric,
    right-aligned), an x-ui.badge (`badge` label with `badgeVariant`) or a "New" text
    tag. Rows are 32px. Active: a subtle fill, full-strength text at 500 and a 1px inset
    ring, aria-current="page"; never an accent bar. Disabled: an <a> without href,
    aria-disabled, the same legible text with a dashed underline and `reason` linked
    by aria-describedby; never faded. In the icon rail the label hides, the link is
    named by aria-label and a tooltip shows the label (and count) on hover and focus.
    External (`external`): opens in a new tab (rel noopener noreferrer), is never active,
    and shows the kit's external affordance: a 14px arrow-up-right after the label and
    "(opens in new tab)" for screen readers (the rail's name says it too).
    In-app links use wire:navigate when the sidebar's `navigate` is on (the default):
    the main region cross-fades and the persisted sidebar keeps its scroll and state.
    `reload` makes one item an ordinary link (a full page load), for destinations
    outside the shell's layout. External and disabled items never navigate in place.
    An item whose `active` is left to the href (null) is re-marked on the client after
    each wire:navigate (data-nav-match, resources/js/navigate.js).
    Props: href, icon, label (or the slot), active (null: SidebarTones::isActive(href)),
    count, badge, badgeVariant (neutral), new, disabled, reason, nested (inside a group),
    external, reload.
--}}
@aware(['variant' => 'light', 'navigate' => true])
@props(['href' => '#', 'icon' => null, 'label' => null, 'active' => null, 'count' => null, 'badge' => null, 'badgeVariant' => 'neutral', 'new' => false, 'disabled' => false, 'reason' => null, 'nested' => false, 'external' => false, 'reload' => false])

@php
    use TallUiKit\Support\SidebarTones;

    $tone = SidebarTones::tones($variant);
    $text = $label ?? trim(strip_tags((string) $slot));
    $matched = $active === null && ! $disabled && ! $external && $href !== '#';
    $navigates = $navigate && ! $reload && ! $disabled && ! $external && $href !== '#';
    $active ??= ! $disabled && ! $external && $href !== '#' && SidebarTones::isActive($href);
    $reasonId = $disabled && $reason ? 'nav-reason-'.\Illuminate\Support\Str::slug($text).'-'.substr(md5($href.$text), 0, 6) : null;
    $tip = trim($text.($count !== null ? ' '.number_format($count) : ''));
    $railName = $external ? $tip.' (opens in new tab)' : $tip;
@endphp

<li x-data="{ tip: false }" {{ $attributes->only(['wire:key'])->merge(['data-nav-row' => '']) }}>
    <a
        @if ($disabled) aria-disabled="true" role="link" tabindex="0" @if ($reasonId) aria-describedby="{{ $reasonId }}" @endif @else href="{{ $href }}" @endif
        @if ($external && ! $disabled) target="_blank" rel="noopener noreferrer" data-nav-external @endif
        @if ($navigates) wire:navigate @endif
        @if ($matched) data-nav-match @endif
        x-ref="link"
        @if ($active) aria-current="page" @endif
        x-bind:aria-label="railed($el) ? @js($railName) : null"
        x-on:mouseenter="tip = railed($el)"
        x-on:mouseleave="tip = false"
        x-on:focus="tip = railed($el)"
        x-on:blur="tip = false"
        x-on:keydown.escape="tip = false"
        data-nav-item
        @if ($active) data-active @endif
        @if ($disabled) data-disabled @endif
        {{ $attributes->except(['wire:key'])->class([
            'group/nav flex min-h-8 py-1.5 items-center gap-2.5 rounded-md px-2.5 type-body transition-colors duration-100 focus-ring rail:justify-center rail:px-0',
            'pl-9 rail:pl-0' => $nested,
            $tone['itemActive'] => $active,
            $tone['itemDisabled'] => $disabled,
            $tone['item'] => ! $active && ! $disabled,
        ]) }}
    >
        @if ($icon)
            <x-ui.icon :name="$icon" data-nav-icon :class="\Illuminate\Support\Arr::toCssClasses(['size-4 shrink-0 transition-colors duration-100', $tone['iconActive'] => $active, $tone['iconDisabled'] => $disabled, $tone['icon'] => ! $active && ! $disabled])" />
        @endif
        <span @class(['flex-1 wrap-anywhere rail:hidden', 'underline decoration-dashed underline-offset-4' => $disabled])>{{ $text }}@if ($external)<span class="sr-only"> (opens in new tab)</span><x-ui.icon name="arrow-up-right" class="ml-0.5 inline-block size-3.5 align-[-2px]" />@endif</span>
        @if ($new)
            <span @class(['shrink-0 rounded-sm border px-1 font-body text-caption font-medium rail:hidden', $tone['tag']]) data-nav-new>New</span>
        @endif
        @if ($badge)
            <x-ui.badge :variant="$badgeVariant" size="sm" class="shrink-0 rail:hidden" data-nav-badge>{{ $badge }}</x-ui.badge>
        @elseif ($count !== null)
            <span @class(['shrink-0 font-mono text-[12px] leading-4 tabular-nums rail:hidden', $tone['count']]) data-nav-count>{{ number_format($count) }}</span>
        @endif
    </a>
    @if ($reasonId)
        <span id="{{ $reasonId }}" class="sr-only">{{ $reason }}</span>
    @endif

    <span
        x-show="tip"
        x-cloak
        x-transition.opacity.duration.100ms
        x-anchor.right.offset.10.fixed="$refs.link"
        role="tooltip"
        class="z-[60] w-max max-w-xs wrap-anywhere rounded-md bg-secondary px-2 py-1 font-body text-caption font-medium text-on-secondary pointer-events-none ring-1 ring-on-secondary/10"
    >{{ $text }}@if ($count !== null) <span class="font-mono text-on-secondary/70">{{ number_format($count) }}</span>@endif</span>
</li>
