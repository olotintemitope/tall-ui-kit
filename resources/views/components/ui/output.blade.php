{{--
    A calculated result: <output>, in the numeric role, announced politely when it
    changes (output is a live region by role "status"). Props: for (ids of the inputs
    it is computed from), label (visible text before it, meta role), size (default |
    figure for a total). Slot: the value (bind with x-text or print server-side).
--}}
@props(['for' => null, 'label' => null, 'size' => 'default', 'name' => null])

<p {{ $attributes->only('class')->class(['flex items-baseline gap-2']) }} data-output>
    @if ($label)
        <span class="type-meta">{{ $label }}</span>
    @endif
    <output @if ($for) for="{{ $for }}" @endif @if ($name) name="{{ $name }}" @endif aria-live="polite" {{ $attributes->except('class') }} @class(['type-numeric text-ink' => $size !== 'figure', 'type-figure' => $size === 'figure'])>{{ $slot }}</output>
</p>
