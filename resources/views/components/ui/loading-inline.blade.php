{{--
    Inline loading line for a section: a spinner and a meta-role message, e.g.
    "Loading transactions". One polite status; the spinner itself is silent.
    Props: label (the message, default "Loading"), size (spinner size, default sm).
--}}
@props(['label' => 'Loading', 'size' => 'sm'])

<p role="status" {{ $attributes->class(['type-meta inline-flex items-center gap-2']) }} data-loading-inline>
    <x-ui.spinner :size="$size" :announce="false" />
    <span>{{ $label }}</span>
</p>
