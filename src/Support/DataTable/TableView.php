<?php

namespace TallUiKit\Support\DataTable;

use Illuminate\Contracts\Pagination\Paginator;

/**
 * Everything x-data-table needs for one render, built by
 * WithDataTable::dataTable(). Read-only; the views never query.
 */
final class TableView
{
    /**
     * @param  list<Column>  $columns  visible, in order
     * @param  list<Column>  $allColumns  every column this user may see (column menu)
     * @param  list<Filter>  $filters  filters this user may use
     * @param  array<string, mixed>  $filterValues  key => normalized value (defaults applied)
     * @param  list<array{key: string, label: string, value: string|null}>  $tokens
     * @param  list<Action>  $bulkActions
     * @param  list<Action>  $rowActions
     * @param  array<string, array{label: string, actions: list<string>, hrefs: array<string, string>}>  $rowMeta
     * @param  list<string>  $selectedIds
     * @param  list<int>  $perPageOptions
     * @param  array{title: string, description?: string|null, actionLabel?: string|null, actionHref?: string|null}  $empty
     */
    public function __construct(
        public readonly string $name,
        public readonly string $caption,
        public readonly string $noun,
        public readonly string $placeholder,
        public readonly string $layout,
        public readonly string $density,
        public readonly string $pagination,
        public readonly string $search,
        public readonly string $term,
        public readonly ?string $sort,
        public readonly string $direction,
        public readonly bool $sortIsDefault,
        public readonly int $perPage,
        public readonly array $perPageOptions,
        public readonly array $columns,
        public readonly array $allColumns,
        public readonly array $filters,
        public readonly array $filterValues,
        public readonly array $tokens,
        public readonly array $bulkActions,
        public readonly array $rowActions,
        public readonly ?Paginator $rows,
        public readonly ?int $total,
        public readonly array $rowMeta,
        public readonly array $selectedIds,
        public readonly bool $selectAllMatching,
        public readonly int $selectedCount,
        public readonly bool $pageAllSelected,
        public readonly bool $pageSomeSelected,
        public readonly bool $loaded,
        public readonly ?string $error,
        public readonly bool $exports,
        public readonly string $announcement,
        public readonly array $empty,
    ) {}

    public function hasCriteria(): bool
    {
        return $this->term !== '' || $this->tokens !== [];
    }

    public function sortLabel(): ?string
    {
        foreach ($this->columns as $column) {
            if ($column->key === $this->sort) {
                return $column->label.' '.($this->direction === 'asc' ? 'ascending' : 'descending');
            }
        }

        return null;
    }

    public function count(int $n, ?string $noun = null): string
    {
        return number_format($n).' '.($n === 1 ? 'result' : 'results');
    }
}
