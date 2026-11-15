{{--
    A dialog footer button that follows the dialog's `busy` state: aria-busy and a
    spinner over the label (kept for the accessible name), clicks blocked while busy.
    Used inside x-ui.dialog.base only. Props: variant, type, form. Other attributes
    (x-on:click) pass through.
--}}
@props(['variant' => 'default', 'type' => 'button', 'form' => null])

@php $spinner = in_array($variant, ['secondary', 'destructive'], true) ? 'inverse' : 'default'; @endphp

<x-ui.button
    :variant="$variant"
    size="sm"
    :type="$type"
    :form="$form"
    x-bind:aria-busy="busy ? 'true' : null"
    x-bind:aria-disabled="busy ? 'true' : null"
    x-on:click.capture="if (busy) { $event.preventDefault(); $event.stopImmediatePropagation() }"
    {{ $attributes->class(['relative aria-busy:pointer-events-none']) }}
>
    <span class="inline-flex items-center gap-1.5" x-bind:class="busy ? 'opacity-0' : ''">{{ $slot }}</span>
    <span class="absolute inset-0 hidden items-center justify-center" x-bind:class="busy ? 'flex!' : ''"><x-ui.spinner :tone="$spinner" :announce="false" /></span>
</x-ui.button>
