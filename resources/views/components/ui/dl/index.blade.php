{{--
    A description list (<dl>) of label and value pairs: labels in meta, values in body
    (or numeric). Layouts: `stacked` (label over value, the default), `inline` (label
    left, value right, 1px rules between rows: a receipt or a spec sheet), `grid` (two
    columns of stacked pairs from sm, as x-ui.card.detail). Props: items (list of
    ['label', 'value', 'numeric']) or a slot of x-ui.dl.item, layout, dense (8px rows).
--}}
@props(['items' => [], 'layout' => 'stacked', 'dense' => false])

<dl {{ $attributes->class([
    'flex flex-col gap-3' => $layout === 'stacked',
    'divide-y divide-line border-y border-line' => $layout === 'inline',
    'grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2' => $layout === 'grid',
]) }} data-dl="{{ $layout }}">
    @foreach ($items as $item)
        <x-ui.dl.item :label="$item['label']" :numeric="$item['numeric'] ?? false" :layout="$layout" :dense="$dense">{{ $item['value'] }}</x-ui.dl.item>
    @endforeach
    {{ $slot }}
</dl>
