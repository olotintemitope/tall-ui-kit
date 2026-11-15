{{--
    Button or link styled as a button.
    Props: variant (default|primary|secondary|outline|ghost|destructive|link; `primary` is an
    alias of `default`), size (default h-9, `md` is an alias of `default` | sm h-8 | lg h-10 | icon 36 | icon-sm 32 | icon-lg 40), href
    (renders an <a>), type (button), loading (a Livewire action name, or true for any: a
    spinner over the label while it runs, same width, aria-busy and clicks blocked),
    busy (the loading state drawn statically, e.g. while an Alpine fetch runs), disabled
    (aria-disabled on a <button>, which stays focusable with clicks blocked, or an <a>
    with aria-disabled and no href), pressed (true or
    false makes it a toggle with aria-pressed; bind x-bind:aria-pressed for live state).
    Everything else passes through (wire:click, x-on).

    States (docs/ui-kit.md "States"), from App-wide variants in app.css so the /admin/ui
    matrix can force them with data-force="hover|active|focus":
      hover     one step of fill or border, no lift or shadow (`state-hover:`)
      pressed   one step past hover (`state-active:`), no transform
      focus     2px `focus` outline, 2px offset, :focus-visible only (`focus-ring`)
      disabled  no opacity: surface-subtle fill, dashed control border (3.38:1 on the fill,
                3.65:1 on white), ink-muted text (5.42:1), cursor-not-allowed, no hover or
                pressed change; links lose the underline and turn ink-muted (5.86:1)
      loading   spinner over the label (kept for the accessible name, drawn transparent,
                same width), aria-busy="true" at once and aria-disabled 50ms later; keeps the
                variant's colours (busy is not disabled) and keeps focus
    Unavailable buttons use aria-disabled, never the `disabled` attribute, so a focused
    button keeps focus; a capture-phase click guard blocks mouse and keyboard activation.
      toggle on aria-pressed="true": surface-muted fill, ink text and a 1px control border
    The accent fill carries `on-primary` text (ink in light) in every state: 5.32:1, hover 5.97:1, pressed 4.62:1.
--}}
@props(['variant' => 'default', 'size' => 'default', 'href' => null, 'type' => 'button', 'loading' => null, 'busy' => false, 'disabled' => false, 'pressed' => null])

@php
    $variants = [
        'default' => 'bg-primary text-on-primary border border-primary-hover state-hover:bg-primary-fill-hover state-active:bg-primary-fill-active',
        'secondary' => 'bg-secondary text-on-secondary border border-secondary state-hover:bg-secondary-light state-hover:border-secondary-light state-active:bg-neutral-900 state-active:border-neutral-900',
        'outline' => 'border border-control bg-surface text-ink state-hover:bg-surface-subtle state-hover:border-control-hover state-active:bg-surface-muted state-active:border-control-hover',
        'ghost' => 'border border-transparent text-ink-body state-hover:bg-surface-subtle state-hover:text-ink state-active:bg-surface-muted state-active:text-ink',
        'destructive' => 'bg-danger text-on-danger border border-danger state-hover:bg-danger-hover state-hover:border-danger-hover state-active:bg-danger-active state-active:border-danger-active',
        'link' => 'text-primary-text underline-offset-4 state-hover:underline state-active:text-ink state-active:underline',
    ];
    $variants['primary'] = $variants['default'];

    // A toggle that is on: a filled, bordered state that differs from off by more than colour.
    $toggle = 'aria-pressed:border-control aria-pressed:bg-surface-muted aria-pressed:text-ink';

    $sizes = [
        // Heights are minimums: a long label wraps and the button grows, it is never cut.
        'default' => 'min-h-9 px-3 py-1.5 text-body-strong',
        'sm' => 'min-h-8 px-2.5 py-1 text-meta font-medium',
        'lg' => 'min-h-10 px-4 py-2 text-body-strong',
        'icon' => 'h-9 w-9',
        'icon-sm' => 'h-8 w-8',
        'icon-lg' => 'h-10 w-10',
    ];
    $sizes['md'] = $sizes['default'];

    $isBusy = (bool) $busy;
    $classes = [
        'inline-flex items-center justify-center gap-1.5 text-center wrap-break-word rounded-md font-body transition-colors duration-100 motion-reduce:transition-none focus-ring',
        $variant === 'link' ? 'state-disabled:cursor-not-allowed state-disabled:text-ink-muted state-disabled:no-underline' : 'state-disabled:cursor-not-allowed state-disabled:border-dashed state-disabled:border-control state-disabled:bg-surface-subtle state-disabled:text-ink-muted',
        'aria-busy:cursor-progress',
        '[&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
        $variants[$variant] ?? $variants['default'],
        $pressed !== null || $attributes->has('x-bind:aria-pressed') ? $toggle : '',
        $variant === 'link'
            ? ($size === 'sm' ? 'text-meta font-medium' : 'text-body-strong')
            : ($sizes[$size] ?? $sizes['default']),
    ];

    $target = is_string($loading) ? $loading : null;

    // Unavailable buttons stay focusable: aria-disabled (never `disabled`, which drops a
    // focused button to <body>) and a capture-phase guard that swallows the click.
    $guard = 'if ($el.getAttribute(\'aria-disabled\') === \'true\' || $el.getAttribute(\'aria-busy\') === \'true\') { $event.preventDefault(); $event.stopImmediatePropagation() }';
    $spinner = in_array($variant, ['default', 'primary', 'secondary', 'destructive'], true) ? 'inverse' : 'default';
@endphp

@if ($href)
    <a
        @if ($disabled) aria-disabled="true" role="link" tabindex="0" @else href="{{ $href }}" @endif
        @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
        {{ $attributes->class($classes) }}
    >{{ $slot }}</a>
@elseif ($loading || $isBusy)
    <button
        type="{{ $type }}"
        x-data
        x-on:click.capture="{{ $guard }}"
        @if ($isBusy) aria-busy="true" aria-disabled="true" @else wire:loading.attr="aria-busy" wire:loading.delay.shortest.attr="aria-disabled" @endif
        @if ($target) wire:target="{{ $target }}" @endif
        @if ($disabled && ! $isBusy) aria-disabled="true" @endif
        @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
        {{ $attributes->class([...$classes, 'relative aria-busy:pointer-events-none']) }}
        data-button-loading
    >
        <span @class(['inline-flex items-center gap-1.5', 'opacity-0' => $isBusy]) @unless ($isBusy) wire:loading.delay.short.class="opacity-0" @if ($target) wire:target="{{ $target }}" @endif @endunless>{{ $slot }}</span>
        <span @class(['absolute inset-0 items-center justify-center', 'flex' => $isBusy, 'hidden' => ! $isBusy]) @unless ($isBusy) wire:loading.delay.short.flex @if ($target) wire:target="{{ $target }}" @endif @endunless>
            <x-ui.spinner :tone="$spinner" label="Loading" :announce="false" />
        </span>
    </button>
@else
    <button
        type="{{ $type }}"
        @if ($disabled) aria-disabled="true" x-data x-on:click.capture="{{ $guard }}" @endif
        @if ($pressed !== null) aria-pressed="{{ $pressed ? 'true' : 'false' }}" @endif
        {{ $attributes->class($classes) }}
    >{{ $slot }}</button>
@endif
