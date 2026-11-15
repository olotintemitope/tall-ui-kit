{{--
    x-data-table.footer: rows-per-page select (10, 25, 50, 100), "Showing 1–25 of 1,284" in
    meta and x-data-table.pagination. Always in the DOM (hidden while there are no rows) so
    the select can be rendered once inside wire:ignore. Props: table (TableView), initial.
--}}
@props(['table', 'initial' => true])
@php
    /** @var \TallUiKit\Support\DataTable\TableView $table */
    $rows = $table->rows;
    $n = $rows ? count($rows->items()) : 0;
    $first = $rows ? ($rows->currentPage() - 1) * $rows->perPage() + 1 : 0;
    $showing = $n > 0 ? 'Showing '.number_format($first)."\u{2013}".number_format($first + $n - 1).($table->total !== null ? ' of '.number_format($table->total) : '') : '';
    $show = $table->loaded && ! $table->error && $rows && ($n > 0 || $rows->currentPage() > 1);
@endphp
{{-- Side by side only from 1024px: with every page number showing, the pager does not fit beside the meta at tablet widths. --}}
<div class="mt-3 flex min-w-0 flex-col-reverse gap-3 lg:flex-row lg:items-center lg:justify-between" @unless ($show) hidden @endunless data-dt-footer>
<div class="flex flex-wrap items-center gap-x-4 gap-y-2">
<div wire:ignore class="flex items-center gap-2">@if ($initial)<label for="{{ $table->name }}-per-page" id="{{ $table->name }}-per-page-label" class="type-meta">Rows per page</label><x-ui.select id="{{ $table->name }}-per-page" size="sm" width="xs" class="w-20" :options="collect($table->perPageOptions)->mapWithKeys(fn ($v) => [$v => (string) $v])->all()" wire:model.live="perPage" data-fk="per-page" />@endif</div>
<p class="type-meta tabular-nums" data-dt-showing>{{ $showing }}</p>
</div>
@if ($rows)<x-data-table.pagination :paginator="$rows" :name="$table->name" />@endif
</div>
