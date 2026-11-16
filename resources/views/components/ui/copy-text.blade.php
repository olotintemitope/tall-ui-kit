{{--
    An inline value with a copy button, e.g. a reference in a table cell. Lean markup for
    dense rows (styles are the .ui-copy-* classes in app.css): the value wraps (long
    references break anywhere, never an ellipsis), then a 24px icon button that is always
    in the tab order with its space reserved; it shows on hover of the value, on keyboard
    focus, while a state shows, and always on touch screens. Its name is the `label`; a
    tooltip with the label (or "Copied", or "Press ⌘C to copy" after a failure) is
    anchored with `fixed` positioning, so a scrolling table never clips it. The slot is
    the visible value and is what is copied, so a masked value copies its mask, unless the
    page passes `value` explicitly.
    Props: value (explicit copy value), label (button name, "Copy"; say what, e.g.
    "Copy reference"), mono (the mono face, for references and ids), toast.
    Attributes go to the wrapper (data-force="copied|failed" draws a state, gallery).
--}}
@props(['value' => null, 'label' => 'Copy', 'mono' => false, 'toast' => false])

@php
    $force = in_array($attributes->get('data-force'), ['copied', 'failed'], true) ? $attributes->get('data-force') : null;
    $state = $force ?? 'idle';
    $options = array_filter(['value' => $value, 'toast' => $toast ?: null, 'force' => $force], fn ($v) => $v !== null);
@endphp

<span x-data="uiCopy(@js((object) $options))" x-on:focusout="leave($event)" x-on:keydown.escape="hideTip($event)" {{ $attributes->except('data-force')->class(['ui-copy-text group/copy']) }} data-copy-text><span x-ref="value" @class(['min-w-0 wrap-anywhere', 'font-mono text-meta' => $mono])>{{ $slot }}</span><button type="button" class="ui-copy-inline focus-ring" x-on:click="copy()" x-on:mouseenter="tip = true" x-on:mouseleave="leaveTip($el)" x-on:focus="tip = $el.matches(':focus-visible')" x-on:blur="tip = false" aria-label="{{ $label }}" x-bind:data-copy-state="state" data-copy-state="{{ $state }}" data-copy-button="inline"><svg viewBox="0 0 24 24" aria-hidden="true" x-show="! copied" @if ($state === 'copied') style="display:none" @endif><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg><svg viewBox="0 0 24 24" aria-hidden="true" x-show="copied" @if ($state !== 'copied') style="display:none" @endif><path d="M20 6 9 17l-5-5"/></svg></button><span x-show="tip || failed" x-cloak x-anchor.top.offset.6.fixed="$el.previousElementSibling" x-on:mouseleave="tip = false" class="ui-copy-tip" role="tooltip" x-text="failed ? 'Press ' + shortcut + ' to copy' : (copied ? 'Copied' : @js($label))" @if ($force === 'failed') style="display:block" @endif>{{ $state === 'failed' ? 'Press Ctrl+C to copy' : ($state === 'copied' ? 'Copied' : $label) }}</span><span class="sr-only" aria-live="polite" x-text="message" data-copy-status></span><span x-ref="selectable" class="sr-only" aria-hidden="true" data-copy-selectable></span></span>
