{{--
    Detail card: a header with the title, a status x-ui.badge and actions; the body is a
    two-column description list (one column on phones) with labels in meta and values in
    body, or numeric for amounts and ids.
    Props: title, description, status (badge text), statusVariant (x-ui.badge variant),
    items (list of {label, value, numeric?}; or use x-ui.card.detail-item in the slot).
    Slots: actions, default (detail items), footer.
--}}
@props(['title', 'description' => null, 'status' => null, 'statusVariant' => 'neutral', 'items' => []])

<x-ui.card :title="$title" :description="$description" {{ $attributes }} data-card-detail>
    @if ($status)
        <x-slot:badge><x-ui.badge :variant="$statusVariant" size="sm">{{ $status }}</x-ui.badge></x-slot:badge>
    @endif
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2">
        @foreach ($items as $item)
            <x-ui.card.detail-item :label="$item['label']" :numeric="$item['numeric'] ?? false">{{ $item['value'] }}</x-ui.card.detail-item>
        @endforeach
        {{ $slot }}
    </dl>

    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.card>
