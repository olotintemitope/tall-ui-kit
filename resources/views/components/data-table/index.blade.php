{{--
    x-data-table: the server-driven table for a Livewire component using
    TallUiKit\Livewire\Concerns\WithDataTable (docs/ui-kit-tables.md).

        <x-data-table :table="$this->dataTable()" />

    Props:
      table     TallUiKit\Support\DataTable\TableView (required), built by the trait. Everything
                the table shows comes from it: columns, filters, tokens, rows, counts,
                selection, actions, layout (scroll | stack), density, empty copy.
      heading   optional visible heading (h2). Without it a visually hidden h2 ("Transactions,
                1,284 results, page 2 of 52") is the focus target after pagination; the
                table's caption stays a plain name (a focusable caption is not allowed
                inside role=table).
    Slots:
      empty     replaces the empty-dataset action (one line plus an optional action).
      toolbar   extra toolbar controls after the filters.

    Parts (each its own file, composed from x-ui.*): toolbar, filter, tokens, header,
    row-menu, bulk-bar, footer, pagination, states, properties, assets. Rows are printed by
    TallUiKit\Support\DataTable\RowRenderer (compact HTML, see Performance in the docs).

    URL parameters: q, sort, dir, filters[key], per, page, cols, density.
    Events: open-confirm and confirmed (x-ui.confirm-dialog, names "{name}-bulk-{key}" and
    "{name}-row-{key}"), toast (from the trait), open-dialog (export dialogs, kit).
    Focus: see assets.blade.php and docs/ui-kit-tables.md, Focus.
--}}
@props(['table', 'heading' => null])

@php
    /** @var \TallUiKit\Support\DataTable\TableView $table */
    $hasBulk = $table->bulkActions !== [];
    $rows = $table->rows;
    $count = $rows ? count($rows->items()) : 0;
    $stack = $table->layout === 'stack';
    $initial = ! app('livewire')->isLivewireRequest();
    $pageInfo = $rows instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $rows->lastPage() > 1 ? ', page '.$rows->currentPage().' of '.$rows->lastPage() : '';
    $defaults = collect($table->allColumns)->filter(fn ($c) => $c->defaultVisible || $c->required)->pluck('key')->values()->all();
    $required = collect($table->allColumns)->filter(fn ($c) => $c->required)->pluck('key')->values()->all();
@endphp

<div
    x-data="uiDataTable($el)"
    data-default-columns="{{ implode(',', $defaults) }}"
    data-required-columns="{{ implode(',', $required) }}"
    x-on:confirmed.window="confirmed($event.detail)"
    data-dt="{{ $table->name }}"
    data-layout="{{ $table->layout }}"
    data-density="{{ $table->density }}"
    @if ($hasBulk) data-has-check @endif
    @if ($table->rowActions !== []) data-has-actions @endif
    @if ($table->selectedCount > 0) data-selection @endif
    @unless ($table->loaded) wire:init="loadTable" @endunless
    {{ $attributes->class(['flex flex-col']) }}
>
<div wire:ignore data-dt-assets>@if ($initial)<x-data-table.assets />@endif</div>
<p class="sr-only" role="status" aria-live="polite" data-dt-live>{{ $table->loaded ? $table->announcement : 'Loading '.$table->noun }}</p>
<p class="sr-only" role="status" aria-live="polite" data-dt-selection-live>{{ $table->selectedCount > 0 ? number_format($table->selectedCount).' selected' : '' }}</p>
@if ($heading)<h2 class="type-heading mb-3" tabindex="-1" data-dt-heading>{{ $heading }}</h2>@else<h2 class="sr-only" tabindex="-1" data-dt-heading>{{ $table->caption }}@if ($table->loaded && $table->total !== null), {{ $table->count($table->total) }}{{ $pageInfo }}@endif</h2>@endif
<x-data-table.toolbar :table="$table" :initial="$initial">@isset($toolbar){{ $toolbar }}@endisset</x-data-table.toolbar>
@if ($table->tokens !== [])<x-data-table.tokens :table="$table" />@endif
@if ($table->error)
<x-data-table.states state="error" :table="$table" />
@elseif (! $table->loaded || $count > 0)
<x-ui.loading-overlay :label="'Updating '.$table->noun" class="mt-3">
<div class="dt-scroll focus-ring rounded-lg border border-line" data-dt-scroll="{{ $table->caption }}" wire:key="{{ $table->name }}-scroll">
<table @if ($stack) role="table" @endif>
<caption class="sr-only">{{ $table->caption }}</caption>
<x-data-table.header :table="$table" />
<tbody @if ($stack) role="rowgroup" @endif>@if ($table->loaded){{ \TallUiKit\Support\DataTable\RowRenderer::render($table, lean: ! $initial) }}@else<x-data-table.states state="skeleton" :table="$table" :colspan="count($table->columns) + 2" />@endif</tbody>
</table>
</div>
</x-ui.loading-overlay>
@elseif ($table->hasCriteria())
<x-data-table.states state="no-results" :table="$table" />
@else
<x-data-table.states state="empty" :table="$table">@isset($empty){{ $empty }}@endisset</x-data-table.states>
@endif
<x-data-table.footer :table="$table" :initial="$initial" />
@if ($hasBulk && $table->selectedCount > 0)<x-data-table.bulk-bar :table="$table" />@endif
<div wire:ignore data-dt-static-tail>@if ($initial)
@if ($table->rowActions !== [])<x-data-table.row-menu :table="$table" />@endif
@foreach ([...$table->bulkActions, ...$table->rowActions] as $action)
@if ($action->needsConfirmation())<x-ui.confirm-dialog :name="$table->name.'-'.$action->kind.'-'.$action->key" :title="$action->confirmTitle" :description="$action->confirmDescription" :confirm-label="$action->confirmLabel" :variant="$action->destructive ? 'destructive' : 'default'" />@endif
@endforeach
@endif</div>
</div>
