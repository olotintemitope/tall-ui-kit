{{--
    A named list of files (docs/components/files.md, "Attachment list"): x-ui.file-card
    rows on 1px `line` rules, cards in a grid (2 columns on phones, `columns` from sm), or
    compact chips that wrap. Data in: each item is the file card's props as an array.

    The same Livewire action names apply to every item and are called with the item's key
    (removeAction="removeAttachment" runs removeAttachment('<key>')); validate the key on
    the server, it comes from the browser. `actions` turns on event buttons for every item
    (a bubbling `ui-file` event). An empty list prints `empty` as one meta line (or nothing).

    Props: items (list of [key, name, size, meta, type, href, thumb, alt, status, progress,
    error]), label (the list's accessible name, "Attachments"), variant (row | card |
    compact), columns (2 to 4, cards only), actions, downloadAction, cancelAction,
    retryAction, removeAction, newTab, empty.
--}}
@props([
    'items' => [], 'label' => 'Attachments', 'variant' => 'row', 'columns' => 3, 'actions' => [],
    'downloadAction' => null, 'cancelAction' => null, 'retryAction' => null, 'removeAction' => null, 'newTab' => false, 'empty' => null,
])

@php
    use Illuminate\Support\Js;

    $items = collect($items)->values();
    $call = fn (?string $method, string $key): ?string => $method ? $method.'('.Js::from($key).')' : null;
    $grid = [2 => 'sm:grid-cols-2', 3 => 'sm:grid-cols-2 lg:grid-cols-3', 4 => 'sm:grid-cols-3 lg:grid-cols-4'][$columns] ?? 'sm:grid-cols-2 lg:grid-cols-3';
@endphp

@if ($items->isEmpty())
    @if ($empty)
        <p {{ $attributes->class(['type-meta']) }} data-attachment-list-empty>{{ $empty }}</p>
    @endif
@else
    <ul aria-label="{{ $label }}" {{ $attributes->class([
        'min-w-0',
        'divide-y divide-line' => $variant === 'row',
        'grid grid-cols-2 gap-3 '.$grid => $variant === 'card',
        'flex flex-wrap gap-2' => $variant === 'compact',
    ]) }} data-attachment-list="{{ $variant }}">
        @foreach ($items as $i => $item)
            @php($itemKey = (string) ($item['key'] ?? $item['name'] ?? $i))
            <x-ui.file-card
                as="li"
                :variant="$variant"
                :key="$itemKey"
                :name="$item['name'] ?? 'Untitled'"
                :size="$item['size'] ?? null"
                :meta="$item['meta'] ?? null"
                :type="$item['type'] ?? null"
                :href="$item['href'] ?? null"
                :thumb="$item['thumb'] ?? null"
                :alt="$item['alt'] ?? null"
                :status="$item['status'] ?? null"
                :progress="$item['progress'] ?? 0"
                :error="$item['error'] ?? null"
                :actions="$actions"
                :new-tab="$newTab"
                :download-action="$call($downloadAction, $itemKey)"
                :cancel-action="$call($cancelAction, $itemKey)"
                :retry-action="$call($retryAction, $itemKey)"
                :remove-action="$call($removeAction, $itemKey)"
                wire:key="attachment-{{ $itemKey }}"
            />
        @endforeach
    </ul>
@endif
