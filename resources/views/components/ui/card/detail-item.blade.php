{{--
    One label and value pair of x-ui.card.detail: label in meta, value in body (or
    numeric, ink, for amounts, counts and ids). Props: label, numeric. Slot: the value (the default slot).
--}}
@props(['label', 'numeric' => false])

<div {{ $attributes->class(['min-w-0']) }}>
    <dt class="type-meta">{{ $label }}</dt>
    <dd @class(['mt-0.5 break-words', 'type-numeric text-ink' => $numeric, 'type-body' => ! $numeric])>{{ $slot }}</dd>
</div>
