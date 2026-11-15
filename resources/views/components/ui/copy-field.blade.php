{{--
    Read-only value with a joined copy button inside the same 1px `control` border
    (API keys, referral links, webhook URLs). The input is focusable and selects its
    text on focus; the button keeps focus through every copy state (x-ui.copy-button).
    Props:
      text       the value shown (required)
      value      what is copied when it differs from `text`; without it the shown text
                 is copied, so a masked text copies the mask
      label      accessible name of the input when not inside x-ui.field
      size       default (36px), sm (32px) or lg (40px); width full (default), md or sm
      iconOnly   an icon-only button (tooltip "Copy") instead of icon and "Copy"
      mono       the value in the mono face (keys, references)
      toast      also a toast, as x-ui.copy-button
    Attributes go to the outer box (data-force="copied|failed" draws a state, gallery).
--}}
@props([
    'text' => '',
    'value' => null,
    'label' => null,
    'size' => 'default',
    'width' => 'full',
    'iconOnly' => false,
    'mono' => false,
    'toast' => false,
])
@aware(['id' => null, 'name' => null, 'hint' => null])

@php
    $small = \TallUiKit\Support\FormControl::size($size) === 'sm';
    $force = in_array($attributes->get('data-force'), ['copied', 'failed'], true) ? $attributes->get('data-force') : null;
    $options = array_filter(['value' => $value, 'toast' => $toast ?: null, 'force' => $force], fn ($v) => $v !== null);
    // Inside x-ui.field the field's label points at this input; alone, a stable id from the text.
    $controlId = ($id || $name) ? \TallUiKit\Support\FormControl::id($id, $name) : 'copy-field-'.substr(md5($text.($label ?? '')), 0, 8);
    $widths = ['full' => 'w-full', 'md' => 'w-full max-w-sm', 'sm' => 'w-full max-w-60'];
@endphp

<div
    x-data="uiCopy(@js((object) $options))"
    x-on:focusout="leave($event)"
    {{ $attributes->except('data-force')->class([
        'relative flex items-stretch overflow-hidden rounded-md border border-control bg-surface transition-colors duration-100 has-[input:focus-visible]:border-focus has-[input:focus-visible]:ring-2 has-[input:focus-visible]:ring-primary/20',
        \TallUiKit\Support\FormControl::height($size),
        $widths[$width] ?? $widths['full'],
    ]) }}
    data-copy-field
    data-control-box
>
    <input
        x-ref="field"
        type="text"
        readonly
        id="{{ $controlId }}"
        value="{{ $text }}"
        @if ($label) aria-label="{{ $label }}" @endif
        @if ($hint) aria-describedby="{{ \TallUiKit\Support\FormControl::hintId($controlId) }}" @endif
        x-on:focus="$el.select()"
        @class([
            'min-w-0 flex-1 wrap-anywhere bg-transparent px-3 text-ink outline-none',
            'font-mono text-meta' => $mono,
            'font-body text-body' => ! $mono,
        ])
    />
    {{-- Joined: the field's border (clipped by overflow-hidden) is the button's outline; the button adds only the divider and an inset focus ring. --}}
    <x-ui.copy-button :scoped="false" joined="field" :variant="$iconOnly ? 'icon' : 'label'" :size="$size" :data-force="$force" />
    <span class="sr-only" aria-live="polite" x-text="message" data-copy-status></span>
    <span x-ref="selectable" class="sr-only" aria-hidden="true" data-copy-selectable></span>
</div>
