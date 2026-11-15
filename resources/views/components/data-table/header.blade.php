{{--
    x-data-table.header: the <thead>, printed by RowRenderer::header() so every response
    stays small. Sortable headers are real <button>s inside the th (wire:click sortBy, a
    24px target), with aria-sort on the th (ascending, descending, none). The active column
    shows a chevron in ink (pointing up for ascending); inactive sortable columns show a
    muted double chevron only on hover or focus. Numeric columns are right-aligned with
    the icon before the label. Sticky inside the table's scroll box. The select-all
    checkbox selects the rows on this page (indeterminate when some are).
    Props: table (TableView).
    Classes printed from PHP (kept here so Tailwind generates them): rotate-180 size-3.5
    size-4 shrink-0 text-ink text-ink-muted w-12 pr-2 sr-only type-label dt-check dt-pin.
--}}
@props(['table'])
<thead @if ($table->layout === 'stack') role="rowgroup" @endif>{{ \TallUiKit\Support\DataTable\RowRenderer::header($table) }}</thead>
