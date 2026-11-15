{{--
    x-data-table.bulk-bar: shown while rows are selected; sticky to the bottom of the
    viewport. "3 selected · Select all 1,284 · Clear" and the bulk action buttons (each
    gated by its ability; destructive ones are red and confirm first). Focus is never moved
    into it: the selection count is announced by the table's polite status. Clear returns
    focus to the select-all checkbox. The table's scroll box and the page keep 4rem and 5rem
    of scroll padding at the bottom, so the bar never covers the focused element.
    Props: table (TableView).
--}}
@props(['table'])

@php
    /** @var \TallUiKit\Support\DataTable\TableView $table */
    $canSelectAll = ! $table->selectAllMatching && $table->total !== null && $table->total > $table->selectedCount;
@endphp

<div x-data x-init="const set = () => document.documentElement.style.setProperty('--dt-bulk-h', $el.offsetHeight + 'px'); set(); $el._dtRo = new ResizeObserver(set); $el._dtRo.observe($el)" data-dt-bulk-measure class="sticky bottom-0 z-20 mt-3 flex flex-wrap items-center justify-between gap-x-4 gap-y-2 border-t border-line bg-surface py-2.5 shadow-bar" wire:key="{{ $table->name }}-bulk-bar" data-dt-bulk-bar>
    <p class="type-body flex flex-wrap items-center gap-x-2">
        <span class="type-body-strong tabular-nums">{{ $table->selectAllMatching ? 'All '.number_format($table->selectedCount) : number_format($table->selectedCount) }} selected</span>
        @if ($canSelectAll)
            <span class="text-ink-muted" aria-hidden="true">&middot;</span>
            <x-ui.button variant="link" size="sm" class="h-6 px-0" wire:click="selectAllMatchingRows" data-fk="select-all-matching">Select all {{ number_format($table->total) }}</x-ui.button>
        @endif
        <span class="text-ink-muted" aria-hidden="true">&middot;</span>
        <x-ui.button variant="link" size="sm" class="h-6 px-0" wire:click="clearSelection" x-on:click="clearSelectionIntent()" data-fk="clear-selection">Clear</x-ui.button>
    </p>
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($table->bulkActions as $action)
            <x-ui.button
                :variant="$action->destructive ? 'destructive' : 'outline'"
                size="sm"
                x-on:click="bulk({{ \Illuminate\Support\Js::from($action->key) }}, {{ $action->needsConfirmation() ? 'true' : 'false' }}, $el)"
                data-fk="bulk-{{ $action->key }}"
                wire:key="{{ $table->name }}-bulk-{{ $action->key }}"
            >
                @if ($action->icon)<x-ui.icon :name="$action->icon" />@endif
                {{ $action->label }}
            </x-ui.button>
        @endforeach
    </div>
</div>
