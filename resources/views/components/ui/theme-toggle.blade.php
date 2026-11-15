{{--
    Light / Dark / System switch for the colour scheme (docs/theming.md "Dark mode").
    The x-ui.segmented look: a `surface-subtle` track with a 1px `line` edge; the chosen
    segment on `surface` with a 1px `control` edge (3:1 boundary), the others ink-muted.
    A radiogroup (accessible name `label`): arrow keys move and choose, only the chosen
    segment is in the tab order, focus shows the kit ring. Each change sets
    html[data-color-scheme] at once (no reload) and is announced in a polite live region
    ("Colour scheme: Dark"). Needs x-ui.head in the layout; renders nothing when
    config('tall-ui-kit.color_scheme.schemes') offers one scheme only.

    Persistence follows config('tall-ui-kit.color_scheme.persist'): localStorage, plus
    $wire.setColorScheme() (the WithColorScheme trait) when 'server' and the toggle sits
    inside a Livewire component that uses it.

    Props: label (Colour scheme), size (default | sm), iconOnly (false: icon and word;
    true: icons with the word as each segment's accessible name, for tight headers).
--}}
@props(['label' => 'Colour scheme', 'size' => 'default', 'iconOnly' => false])

@php
    $all = [
        'light' => ['label' => 'Light', 'icon' => 'sun'],
        'dark' => ['label' => 'Dark', 'icon' => 'moon'],
        'system' => ['label' => 'System', 'icon' => 'monitor'],
    ];
    $schemes = \TallUiKit\Support\ColorScheme\ColorScheme::schemes();
    $initial = \TallUiKit\Support\ColorScheme\ColorScheme::default();
    $small = $size === 'sm';
@endphp

@if (\TallUiKit\Support\ColorScheme\ColorScheme::switchable())
<div
    x-data="uiThemeToggle({ persist: @js(\TallUiKit\Support\ColorScheme\ColorScheme::persist()), announceLabel: @js($label) })"
    role="radiogroup"
    aria-label="{{ $label }}"
    x-on:keydown.right.prevent="move(1)"
    x-on:keydown.down.prevent="move(1)"
    x-on:keydown.left.prevent="move(-1)"
    x-on:keydown.up.prevent="move(-1)"
    data-theme-toggle
    {{ $attributes->class(['inline-flex shrink-0 items-center gap-0.5 rounded-md border border-line bg-surface-subtle p-0.5', $small ? 'h-8' : 'h-9']) }}
>
    @foreach ($schemes as $scheme)
        <button
            type="button"
            role="radio"
            data-value="{{ $scheme }}"
            @if ($iconOnly) aria-label="{{ $all[$scheme]['label'] }}" @endif
            x-bind:aria-checked="value === @js($scheme) ? 'true' : 'false'"
            x-bind:tabindex="value === @js($scheme) ? 0 : -1"
            x-on:click="choose(@js($scheme))"
            aria-checked="{{ $initial === $scheme ? 'true' : 'false' }}"
            tabindex="{{ $initial === $scheme ? 0 : -1 }}"
            @class([
                'inline-flex shrink-0 items-center justify-center gap-1.5 rounded-sm border border-transparent font-body text-meta font-medium leading-none text-ink-muted transition-colors duration-100 motion-reduce:transition-none focus-ring',
                'state-hover:text-ink aria-checked:border-control aria-checked:bg-surface aria-checked:text-ink',
                $iconOnly ? ($small ? 'size-6.5' : 'size-7.5') : ($small ? 'h-6.5 px-2' : 'h-7.5 px-2.5'),
            ])
        >
            <x-ui.icon :name="$all[$scheme]['icon']" class="size-4" />
            @unless ($iconOnly){{ $all[$scheme]['label'] }}@endunless
        </button>
    @endforeach
    <span class="sr-only" role="status" aria-live="polite" data-theme-toggle-live></span>
    {{-- Marks the stored choice while the page parses, so the first paint already shows it (Alpine takes over on init). --}}
    <script>(function (group) { var scheme = document.documentElement.dataset.colorScheme; group.querySelectorAll('[role=radio]').forEach(function (b) { var on = b.dataset.value === scheme; b.setAttribute('aria-checked', on ? 'true' : 'false'); b.tabIndex = on ? 0 : -1; }); })(document.currentScript.parentElement);</script>
</div>
@endif
