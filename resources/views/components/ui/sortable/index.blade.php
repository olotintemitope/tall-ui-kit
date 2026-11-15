{{--
    A list the user reorders by dragging or from the keyboard (docs/components/workflow.md,
    "Sortable"). Generic: rows are data (`items`) or x-ui.sortable.item rows in the slot.

    Each row has a drag handle (a 32px grip button named "Reorder <label>"), so links and
    text in the row keep working. Pointer: press the handle and drag (mouse, pen or touch;
    the handle has touch-action: none, so a finger drag never scrolls the page); the row
    follows the pointer, the others slide aside (transform only, --motion-sm, instant
    under reduced motion), and the page scrolls near the window's edges. Keyboard: Space
    or Enter on a handle picks the row up, Up and Down (Home, End) move it, Space or
    Enter drops it, Escape puts it back. A polite live region says each step ("Picked up
    Mobile money. Position 2 of 5.", "Moved to position 3 of 5.", "Dropped. Position 3
    of 5.", "Cancelled. Back at position 2."). Focus stays on the moved row's handle.

    When the order changed, the new order (the rows' keys, top to bottom) goes to the
    surrounding Livewire component as one call, `action(keys)` (validate the keys on the
    server: they come from the browser), and a bubbling `ui-sort` DOM event fires with
    detail { order, key, from, to }. No Alpine per row and none for the list: one lazy
    module (resources/js/sortable-core.js) with delegated listeners serves every list.

    Look: rows on 1px `line` rules (or `bordered`: a 1px `line` card); the grip in
    ink-muted, ink on hover; the lifted row has a `surface-subtle` fill and the popover
    shadow; a picked-up row (keyboard) the same plus a 2px focus ring on its handle.

    Props: items (list of [key, label, description]), action (Livewire method), label
    (the list's accessible name, "Reorder list"), bordered (true), disabled, id. Slot:
    x-ui.sortable.item rows when `items` is not given.
--}}
@props(['items' => null, 'action' => null, 'label' => 'Reorder list', 'bordered' => true, 'disabled' => false, 'id' => null])

@php
    // Not from the items: their order changes, and the rows' wire:keys must not.
    $id ??= 'sortable-'.substr(md5($label), 0, 6);
@endphp

<div {{ $attributes->class(['min-w-0']) }} data-sortable-root>
    <p id="{{ $id }}-help" class="sr-only">Press Space or Enter to pick up a row, the arrow keys to move it, Space or Enter to drop it and Escape to cancel.</p>
    <ul
        id="{{ $id }}"
        aria-label="{{ $label }}"
        @class(['ui-sortable min-w-0 divide-y divide-line', 'rounded-lg border border-line bg-surface' => $bordered])
        data-sortable
        @if ($action) data-sortable-action="{{ $action }}" @endif
        @if ($disabled) data-sortable-disabled @endif
    >
        @if ($items !== null)
            @foreach (collect($items)->values() as $i => $item)
                <x-ui.sortable.item :key="(string) ($item['key'] ?? $i)" :label="$item['label']" :description="$item['description'] ?? null" :help="$id.'-help'" :disabled="$disabled" wire:key="{{ $id }}-{{ $item['key'] ?? $i }}" />
            @endforeach
        @else
            {{ $slot }}
        @endif
    </ul>
    <p class="sr-only" aria-live="polite" aria-atomic="true" wire:ignore data-sortable-live></p>
</div>
