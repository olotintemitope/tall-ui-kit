{{--
    Copy to clipboard (docs/ui-kit-copy-icons.md). Behaviour is `uiCopy` in
    resources/js/ui.js: idle, copied (2s, the icon cross-fades to a check over
    150ms, "Copied", emerald-700, polite "Copied to clipboard"), failed (the label
    stays "Copy"; an attached hint below the button says "Press ⌘C to copy", Ctrl+C
    off a Mac, the value is selected and the polite status announces it). Focus stays
    on the button. "Copy" and "Copied" share one grid cell, so the wider reserves the
    width; the hint is anchored outside the flow (fixed), so nothing shifts.

    Props:
      value        the text to copy
      variant      label (icon and label, outline button), icon (icon only, ghost,
                   label as aria-label and tooltip), link (icon and label, link style)
      size         default (36px), sm (32px) or lg (40px), as x-ui.button
      label        "Copy" (say what for icon-only buttons: "Copy reference");
      copiedLabel  "Copied"
      toast        also a "Copied to clipboard." toast, for copies from a menu
    Internal (x-ui.copy-field, x-ui.copy-text): scoped false (an outer uiCopy owns the
    state), joined field|inline (the joined field button or the 24px inline button).
    Attributes go to the button. data-force="copied|failed" draws that state (gallery);
    data-force="hover|focus" a pointer state, as on x-ui.button.
--}}
@props([
    'value' => null,
    'variant' => 'label',
    'size' => 'default',
    'label' => 'Copy',
    'copiedLabel' => 'Copied',
    'toast' => false,
    'scoped' => true,
    'joined' => null,
])

@php
    $variant = in_array($variant, ['label', 'icon', 'link'], true) ? $variant : 'label';
    $small = \TallUiKit\Support\FormControl::size($size) === 'sm';
    $large = \TallUiKit\Support\FormControl::size($size) === 'lg';
    $force = in_array($attributes->get('data-force'), ['copied', 'failed'], true) ? $attributes->get('data-force') : null;
    $state = $force ?? 'idle';
    $options = array_filter(['value' => $value, 'toast' => $toast ?: null, 'force' => $force], fn ($v) => $v !== null);

    $base = 'relative inline-flex shrink-0 items-center justify-center gap-1.5 whitespace-nowrap font-body transition-colors duration-100 motion-reduce:transition-none [&_svg]:pointer-events-none [&_svg]:size-4';
    $look = match (true) {
        $joined === 'field' => ['self-stretch border-l border-control focus-ring-inset state-hover:bg-surface-subtle state-active:bg-surface-muted', $variant === 'icon' ? ($small ? 'w-8' : ($large ? 'w-10' : 'w-9')) : ($small ? 'px-2.5 text-meta font-medium' : ($large ? 'px-4 text-body-strong' : 'px-3 text-body-strong'))],
        $joined === 'inline' => ['size-6 rounded-sm focus-ring state-hover:bg-surface-subtle'],
        $variant === 'icon' => ['rounded-md border border-transparent focus-ring state-hover:bg-surface-subtle state-active:bg-surface-muted', $small ? 'size-8' : ($large ? 'size-10' : 'size-9')],
        $variant === 'link' => ['rounded-sm underline-offset-4 focus-ring state-hover:underline', $small ? 'text-meta font-medium' : 'text-body-strong'],
        default => ['rounded-md border border-control bg-surface focus-ring state-hover:bg-surface-subtle state-hover:border-control-hover state-active:bg-surface-muted', $small ? 'min-h-8 px-2.5 text-meta font-medium' : ($large ? 'min-h-10 px-4 text-body-strong' : 'min-h-9 px-3 text-body-strong')],
    };

    // Idle colour per variant (icons ink-muted 5.86:1, link primary-text 5.21:1); copied is emerald-700 (5.48:1) everywhere.
    $idleTone = match (true) {
        $variant === 'link' => ['text-primary-text'],
        $variant === 'icon' => ['text-ink-muted', 'state-hover:text-ink'],
        default => ['text-ink'],
    };
    $binding = '{ '.collect(['text-success-text' => 'copied', ...array_fill_keys($idleTone, '! copied')])->map(fn ($when, $class) => "'{$class}': {$when}")->implode(', ').' }';
    if ($extra = $attributes->get('x-bind:class')) {
        $binding = "Object.assign({$binding}, {$extra})";
    }
    $buttonAttributes = $attributes->except(['x-bind:class'])->class([$base, ...$look, ...($state === 'copied' ? ['text-success-text'] : $idleTone)]);
@endphp

@if ($scoped)
<span x-data="uiCopy(@js((object) $options))" x-on:focusout="leave($event)" class="relative inline-flex" data-copy>
@endif
    @if ($variant === 'icon')
        <x-ui.tooltip>
            <button
                type="button"
                x-on:click="copy()"
                aria-label="{{ $label }}"
                {{ $buttonAttributes }}
                x-bind:class="{{ $binding }}"
                x-bind:data-copy-state="state"
                data-copy-state="{{ $state }}"
                data-copy-button="icon"
            >
                <x-ui.copy-icons :state="$state" />
            </button>
            <x-slot:content>
                <span x-text="copied ? @js($copiedLabel) : (failed ? 'Press ' + shortcut + ' to copy' : @js($label))">{{ $state === 'copied' ? $copiedLabel : ($state === 'failed' ? 'Press Ctrl+C to copy' : $label) }}</span>
            </x-slot:content>
        </x-ui.tooltip>
    @else
        <button
            type="button"
            x-on:click="copy()"
            {{ $buttonAttributes }}
            x-bind:class="{{ $binding }}"
            x-bind:data-copy-state="state"
            data-copy-state="{{ $state }}"
            data-copy-button="{{ $variant }}"
        >
            <x-ui.copy-icons :state="$state" />
            {{-- One grid cell for all three labels: the widest sets the width, so no state moves the layout. Hidden ones are `invisible`, so out of the accessibility tree too. --}}
            <span class="inline-grid text-left [&>span]:[grid-area:1/1]" data-copy-labels>
                <span @class(['invisible' => $state === 'copied']) x-bind:class="{ invisible: copied }">{{ $label }}</span>
                <span @class(['invisible' => $state !== 'copied']) x-bind:class="{ invisible: ! copied }">{{ $copiedLabel }}</span>
            </span>
        </button>
        {{-- Failure guidance, attached below the button and out of the flow (fixed, so an overflow-hidden field cannot clip it). The polite status announces it; the button is described by it while it shows. --}}
        <span
            x-show="failed"
            @if ($state !== 'failed') x-cloak @endif
            x-anchor.bottom-start.offset.6.fixed="$el.previousElementSibling"
            x-bind:id="$id('copy-hint')"
            x-effect="failed ? $el.previousElementSibling.setAttribute('aria-describedby', $id('copy-hint')) : $el.previousElementSibling.removeAttribute('aria-describedby')"
            class="pointer-events-none z-[60] w-max max-w-xs wrap-anywhere rounded-md bg-secondary px-2 py-1 font-body text-caption font-medium text-on-secondary"
            data-copy-hint
        >Press <span x-text="shortcut">Ctrl+C</span> to copy</span>
    @endif
@if ($scoped)
    <span class="sr-only" aria-live="polite" x-text="message" data-copy-status></span>
    {{-- Failure fallback: the value goes here and is selected, so the shortcut copies it while focus stays on the button. --}}
    <span x-ref="selectable" class="sr-only" aria-hidden="true" data-copy-selectable></span>
</span>
@endif
