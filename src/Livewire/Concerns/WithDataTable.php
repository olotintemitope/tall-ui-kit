<?php

namespace TallUiKit\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Url;
use Livewire\WithPagination;
use TallUiKit\Support\DataTable\Action;
use TallUiKit\Support\DataTable\Column;
use TallUiKit\Support\DataTable\Filter;
use TallUiKit\Support\DataTable\InvalidTableDefinition;
use TallUiKit\Support\DataTable\TableQuery;
use TallUiKit\Support\DataTable\TableView;
use TallUiKit\Support\Exports\ExportColumn;
use Throwable;

/**
 * Server-driven data table for a Livewire component (docs/ui-kit-tables.md).
 * Render it with <x-data-table :table="$this->dataTable()" />.
 *
 * The component implements:
 *  - tableQuery(): the base Eloquent builder of rows this user may see. Search,
 *    filters, sort and selection are all applied on top of it, and selected ids
 *    are re-scoped through it before any action runs.
 *  - tableColumns(): list<Column>.
 * Optional: tableFilters(), tableBulkActions(), tableRowActions(), tableAbility()
 * (the page capability, checked on mount and before every action),
 * tableDefaultSort() ([key, direction]), tableCaption(), tableNoun(),
 * tableSearchPlaceholder(), tableLayout() (scroll | stack), tablePagination()
 * (length | simple), tableDeferred(), tableRowLabel($row), tableIdPattern(),
 * tableEmpty().
 *
 * State is public and URL-bound so links and back/forward work: search (q), sort,
 * direction (dir), filters, perPage (per), page, columns (cols, comma separated
 * keys) and density. Every value is validated on use; nothing from the browser
 * reaches the query unchecked. Selection (selected ids on screen, or
 * selectAllMatching for "every row matching the current criteria") is not in the URL.
 *
 * With exports, use WithDataTableExports instead: it adds WithExports and maps
 * the export query, columns, filters and selection to the table's.
 */
trait WithDataTable
{
    use WithPagination;

    public const TABLE_PER_PAGE = [10, 25, 50, 100];

    public const TABLE_DENSITIES = ['comfortable', 'compact'];

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(history: true)]
    public string $sort = '';

    #[Url(as: 'dir', history: true)]
    public string $direction = '';

    /** @var array<string, mixed> */
    #[Url(history: true)]
    public array $filters = [];

    #[Url(as: 'per', history: true)]
    public int $perPage = 25;

    /** Visible column keys, comma separated; empty means the defaults. */
    #[Url(as: 'cols')]
    public string $columns = '';

    #[Url]
    public string $density = 'comfortable';

    /** @var list<string> ids selected on screen */
    public array $selected = [];

    /** Every row matching the current search and filters is selected (kept as criteria, not ids). */
    public bool $selectAllMatching = false;

    /**
     * The polite status text ("1,284 results, sorted by date descending"). Kept between
     * requests so an unrelated re-render never blanks it mid-announcement; it only changes
     * when search, filters or sort do (a repeat gets a trailing no-break space so it is
     * announced again).
     */
    public string $tableStatus = '';

    /** False until the first deferred load (wire:init), so the first paint is a skeleton. */
    public bool $tableLoaded = false;

    /** Set when this request changed search, filters or sort, so the live region speaks once. */
    protected bool $tableAnnounce = false;

    /** @var array<string, mixed> per-request memo */
    protected array $tableMemo = [];

    /** @return Builder<Model> */
    abstract protected function tableQuery(): Builder;

    /** @return list<Column> */
    abstract protected function tableColumns(): array;

    /** @return list<Filter> */
    protected function tableFilters(): array
    {
        return [];
    }

    /** @return list<Action> */
    protected function tableBulkActions(): array
    {
        return [];
    }

    /** @return list<Action> */
    protected function tableRowActions(): array
    {
        return [];
    }

    protected function tableAbility(): ?string
    {
        return null;
    }

    /** @return array{0: string, 1: string}|null [column key, asc|desc]; null orders by the key, newest first */
    protected function tableDefaultSort(): ?array
    {
        return null;
    }

    protected function tableCaption(): string
    {
        return Str::headline(class_basename(static::class));
    }

    /** Plural noun for counts and placeholders ("transactions"). */
    protected function tableNoun(): string
    {
        return 'rows';
    }

    protected function tableSearchPlaceholder(): string
    {
        return 'Search '.$this->tableNoun().'…';
    }

    /** scroll (sideways inside its box, first column sticky) or stack (a card per row below 640px). */
    protected function tableLayout(): string
    {
        return 'scroll';
    }

    /** length (counts and page numbers) or simple (previous and next, no count query). */
    protected function tablePagination(): string
    {
        return 'length';
    }

    protected function tableDeferred(): bool
    {
        return true;
    }

    /** How a row is named in labels and toasts: its short reference, never its id. */
    protected function tableRowLabel(Model $row): string
    {
        return method_exists($row, 'shortReference') ? $row->shortReference() : 'this '.Str::singular($this->tableNoun());
    }

    /** A term matching this pattern searches the primary key exactly (capture group 1 is the id). */
    protected function tableIdPattern(): ?string
    {
        return '/^#?(\d{1,18})$/';
    }

    /** @return array{title: string, description?: string|null, actionLabel?: string|null, actionHref?: string|null} */
    protected function tableEmpty(): array
    {
        return ['title' => 'No '.$this->tableNoun().' yet.'];
    }

    // ---- Lifecycle -------------------------------------------------------

    public function bootWithDataTable(): void
    {
        if (app()->environment('local', 'testing')) {
            $this->validateTableDefinition();
        }
    }

    public function mountWithDataTable(): void
    {
        $this->authorizeTable();
        $this->sanitizeTableState();
        $this->tableLoaded = ! $this->tableDeferred();
    }

    /** Every later request re-checks the page ability (a role can change mid-session). */
    public function hydrateWithDataTable(): void
    {
        $this->authorizeTable();
    }

    /** Resets the page and selection whenever the criteria change. */
    public function updatedWithDataTable(string $name, mixed $value): void
    {
        $this->sanitizeTableState();

        if (preg_match('/^(search|filters|sort|direction|perPage)(\.|$)/', $name)) {
            $this->tableCriteriaChanged();
        }
    }

    // ---- Actions (callable from the browser; every input is re-validated) ----

    public function loadTable(): void
    {
        $this->tableLoaded = true;
    }

    /** Header click: ascending, then descending, then back to the default sort. */
    public function sortBy(string $key): void
    {
        $column = $this->sortableColumns()->get($key);
        if (! $column) {
            return;
        }

        [$sort, $direction] = $this->resolvedSort();
        if ($this->sort !== $key || $sort !== $key) {
            [$this->sort, $this->direction] = [$key, 'asc'];
        } elseif ($direction === 'asc') {
            $this->direction = 'desc';
        } else {
            [$this->sort, $this->direction] = ['', ''];
        }

        $this->tableCriteriaChanged();
    }

    public function toggleFilterValue(string $key, string $value): void
    {
        $filter = $this->allowedFilters()->get($key);
        if (! $filter || ! $filter->isMulti()) {
            return;
        }

        $current = $filter->normalize($this->filters[$key] ?? null) ?? [];
        $next = in_array($value, $current, true) ? array_values(array_diff($current, [$value])) : [...$current, $value];
        $this->filters[$key] = $filter->normalize($next) ?? [];
        $this->sanitizeTableState();
        $this->tableCriteriaChanged();
    }

    /** Removes a filter (or one value of a multi filter). Filters with a default go back to it. */
    public function removeFilter(string $key, ?string $value = null): void
    {
        $filter = $this->allowedFilters()->get($key);
        if (! $filter) {
            return;
        }

        if ($value !== null && $filter->isMulti()) {
            $this->toggleFilterValue($key, $value);

            return;
        }

        unset($this->filters[$key]);
        $this->tableCriteriaChanged();
    }

    public function clearFilters(): void
    {
        $this->filters = [];
        $this->tableCriteriaChanged();
    }

    public function clearSearchAndFilters(): void
    {
        $this->search = '';
        $this->filters = [];
        $this->tableCriteriaChanged();
    }

    public function toggleColumn(string $key): void
    {
        $column = $this->allowedColumns()->get($key);
        if (! $column || $column->required) {
            return;
        }

        $visible = $this->visibleColumns()->keys()->all();
        $visible = in_array($key, $visible, true) ? array_values(array_diff($visible, [$key])) : [...$visible, $key];
        $order = $this->allowedColumns()->keys()->filter(fn (string $k): bool => in_array($k, $visible, true))->values()->all();
        $defaults = $this->allowedColumns()->filter(fn (Column $c): bool => $c->defaultVisible || $c->required)->keys()->all();

        $this->columns = $order === $defaults ? '' : implode(',', $order);
        $this->tableMemo = [];
    }

    public function resetColumns(): void
    {
        $this->columns = '';
        $this->tableMemo = [];
    }

    /** Header checkbox: select every row on this page, or clear them when all are selected. */
    public function togglePage(): void
    {
        $this->authorizeTable();
        $ids = $this->currentPageIds();
        $selected = $this->selectedIds();

        if ($ids !== [] && array_diff($ids, $selected) === []) {
            $this->selected = array_values(array_diff($selected, $ids));
            $this->selectAllMatching = false;
        } else {
            $this->selected = array_values(array_unique([...$selected, ...$ids]));
        }
    }

    public function selectAllMatchingRows(): void
    {
        $this->authorizeTable();
        $this->selectAllMatching = true;
        $this->selected = $this->currentPageIds();
    }

    /** Unticking one row while every matching row is selected falls back to this page's rows minus that one. */
    public function deselectFromAll(string $id): void
    {
        $this->authorizeTable();
        $this->selectAllMatching = false;
        $this->selected = $this->tableKeyIsValid($id) ? array_values(array_diff($this->currentPageIds(), [$id])) : $this->currentPageIds();
    }

    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectAllMatching = false;
    }

    public function retryTable(): void
    {
        $this->tableMemo = [];
        $this->tableLoaded = true;
    }

    /**
     * Runs a bulk action on the selection, re-scoped through tableQuery(). The
     * page ability, the action's ability and, for actions with a confirm
     * dialog, the confirmation are all checked here.
     */
    public function runBulkAction(string $key, bool $confirmed = false): void
    {
        $this->authorizeTable();
        $action = collect($this->tableBulkActions())->first(fn (Action $a): bool => $a->key === $key);
        abort_if($action === null || $action->handler === null, 404);
        $this->authorizeAction($action);

        if ($action->needsConfirmation() && ! $confirmed) {
            throw ValidationException::withMessages(['action' => 'Confirm this action first.']);
        }

        $rows = $this->selectionQuery();
        $count = $rows->clone()->toBase()->getCountForPagination();

        if ($count === 0) {
            $this->dispatch('toast', type: 'info', message: 'Select at least one row.');

            return;
        }

        $result = ($action->handler)($rows, $count);
        $this->clearSelection();
        $this->tableMemo = [];
        $this->dispatch('toast', type: 'success', message: is_string($result) ? $result : $action->label.': '.number_format($count).' '.($count === 1 ? 'row' : 'rows'));
    }

    public function runRowAction(string $key, string $id, bool $confirmed = false): void
    {
        $this->authorizeTable();
        $action = collect($this->tableRowActions())->first(fn (Action $a): bool => $a->key === $key);
        abort_if($action === null || $action->handler === null, 404);
        $this->authorizeAction($action);

        $row = $this->tableKeyIsValid($id) ? $this->tableQuery()->whereKey($id)->first() : null;
        abort_if($row === null, 404);
        abort_unless($action->availableFor($row), 403);

        if ($action->needsConfirmation() && ! $confirmed) {
            throw ValidationException::withMessages(['action' => 'Confirm this action first.']);
        }

        $result = ($action->handler)($row);
        $this->tableMemo = [];
        $this->dispatch('toast', type: 'success', message: is_string($result) ? $result : $action->label.': '.$this->tableRowLabel($row));
    }

    // ---- Rendering ---------------------------------------------------------

    public function dataTable(): TableView
    {
        $columns = $this->visibleColumns();
        [$sort, $direction] = $this->resolvedSort();
        $filterValues = $this->activeFilterValues();
        $rows = null;
        $total = null;
        $error = null;

        if ($this->tableLoaded) {
            try {
                [$rows, $total] = $this->tablePage($columns);
            } catch (Throwable $e) {
                report($e);
                $error = 'Could not load '.$this->tableNoun().'. Try again.';
            }
        }

        $rowActions = $this->allowedActions($this->tableRowActions());
        $rowMeta = [];
        foreach ($rows?->items() ?? [] as $row) {
            $available = array_values(array_filter($rowActions, fn (Action $a): bool => $a->availableFor($row)));
            $rowMeta[(string) $row->getKey()] = [
                'label' => $this->tableRowLabel($row),
                'actions' => array_map(fn (Action $a): string => $a->key, $available),
                'hrefs' => collect($available)->filter(fn (Action $a): bool => $a->url !== null)->mapWithKeys(fn (Action $a): array => [$a->key => ($a->url)($row)])->all(),
            ];
        }

        $pageIds = array_keys($rowMeta);
        $selected = $this->selectedIds();
        $onPage = array_intersect($pageIds, $selected);

        return new TableView(
            name: 'dt-'.Str::kebab(class_basename(static::class)),
            caption: $this->tableCaption(),
            noun: $this->tableNoun(),
            placeholder: $this->tableSearchPlaceholder(),
            layout: $this->tableLayout(),
            density: $this->density,
            pagination: $this->tablePagination(),
            search: $this->search,
            term: TableQuery::term($this->search),
            sort: $sort,
            direction: $direction,
            sortIsDefault: $this->sort === '',
            perPage: $this->perPage,
            perPageOptions: self::TABLE_PER_PAGE,
            columns: $columns->values()->all(),
            allColumns: $this->allowedColumns()->values()->all(),
            filters: $this->allowedFilters()->values()->all(),
            filterValues: $filterValues,
            tokens: $this->filterTokens($filterValues),
            bulkActions: $this->allowedActions($this->tableBulkActions()),
            rowActions: $rowActions,
            rows: $rows,
            total: $total,
            rowMeta: $rowMeta,
            selectedIds: $selected,
            selectAllMatching: $this->selectAllMatching,
            selectedCount: $this->selectAllMatching && $total !== null ? $total : count($selected),
            pageAllSelected: $pageIds !== [] && count($onPage) === count($pageIds),
            pageSomeSelected: $onPage !== [] && count($onPage) < count($pageIds),
            loaded: $this->tableLoaded,
            error: $error,
            exports: method_exists($this, 'exportColumnOptions'),
            announcement: $this->tableStatus = $this->tableAnnounce && $this->tableLoaded && $error === null
                ? $this->nextTableStatus($this->tableAnnouncement($total, $rows))
                : $this->tableStatus,
            empty: $this->tableEmpty(),
        );
    }

    /**
     * Definition and current state, for the gallery's properties panel and docs.
     *
     * @return array<string, list<array<string, string>>>
     */
    public function tableProperties(): array
    {
        [$sort, $direction] = $this->resolvedSort();
        $default = $this->tableDefaultSort();

        return [
            'Table' => [
                ['name' => 'tableCaption()', 'value' => $this->tableCaption()],
                ['name' => 'tableNoun()', 'value' => $this->tableNoun()],
                ['name' => 'tableSearchPlaceholder()', 'value' => $this->tableSearchPlaceholder()],
                ['name' => 'tableAbility()', 'value' => $this->tableAbility() ?? 'none'],
                ['name' => 'tableDefaultSort()', 'value' => $default ? implode(' ', $default) : 'key desc'],
                ['name' => 'tableLayout()', 'value' => $this->tableLayout()],
                ['name' => 'tablePagination()', 'value' => $this->tablePagination()],
                ['name' => 'tableDeferred()', 'value' => $this->tableDeferred() ? 'true' : 'false'],
                ['name' => 'tableIdPattern()', 'value' => $this->tableIdPattern() ?? 'none'],
                ['name' => 'exports', 'value' => method_exists($this, 'exportColumnOptions') ? 'WithDataTableExports' : 'none'],
            ],
            'State' => [
                ['name' => 'search (q)', 'value' => $this->search === '' ? '(empty)' : $this->search],
                ['name' => 'sort', 'value' => ($sort ?? 'key').($this->sort === '' ? ' (default)' : '')],
                ['name' => 'direction (dir)', 'value' => $direction],
                ['name' => 'filters', 'value' => json_encode($this->activeFilterValues(), JSON_UNESCAPED_SLASHES) ?: '{}'],
                ['name' => 'perPage (per)', 'value' => (string) $this->perPage],
                ['name' => 'page', 'value' => (string) $this->getPage()],
                ['name' => 'columns (cols)', 'value' => $this->visibleColumns()->keys()->implode(', ')],
                ['name' => 'density', 'value' => $this->density],
                ['name' => 'selected', 'value' => count($this->selectedIds()).' ids'.($this->selectAllMatching ? ', all matching' : '')],
            ],
            'Columns' => collect($this->tableColumns())->map(fn (Column $c): array => $c->describe())->values()->all(),
            'Filters' => collect($this->tableFilters())->map(fn (Filter $f): array => $f->describe())->values()->all(),
            'Actions' => collect([...$this->tableBulkActions(), ...$this->tableRowActions()])->map(fn (Action $a): array => $a->describe())->values()->all(),
        ];
    }

    // ---- Query -------------------------------------------------------------

    /** The base query with search and filters (no sort, no eager loads). */
    protected function filteredTableQuery(): Builder
    {
        $query = $this->tableQuery();
        $this->applyTableSearch($query);

        $filters = $this->allowedFilters();
        foreach ($this->activeFilterValues() as $key => $value) {
            $filters[$key]->apply($query, $value);
        }

        return $query;
    }

    protected function applyTableSearch(Builder $query): void
    {
        $term = TableQuery::term($this->search);
        if ($term === '') {
            return;
        }

        $searchable = $this->allowedColumns()->filter(fn (Column $c): bool => $c->isSearchable())->values();

        // A short reference (TXN-9F3C21AB) or a full uuid names its records exactly.
        if ($this->tableReferenceSearch($query, $term)) {
            return;
        }

        if (($pattern = $this->tableIdPattern()) && preg_match($pattern, $term, $m)) {
            $query->whereKey($m[1] ?? $m[0]);

            return;
        }

        foreach ($searchable as $column) {
            if ($column->exactPattern && $column->searchPaths !== [] && preg_match($column->exactPattern, $term)) {
                $path = $column->searchPaths[0];
                str_contains($path, '.')
                    ? $query->whereHas(substr($path, 0, strrpos($path, '.')), fn (Builder $q) => $q->where($q->qualifyColumn(substr($path, strrpos($path, '.') + 1)), $term))
                    : $query->where($query->qualifyColumn($path), $term);

                return;
            }
        }

        if ($searchable->isNotEmpty()) {
            TableQuery::search($query, $searchable->all(), $term);
        }
    }

    protected function applyTableSort(Builder $query): void
    {
        [$sort, $direction] = $this->resolvedSort();

        if ($sort !== null) {
            TableQuery::sort($query, $this->allowedColumns()[$sort], $direction);
        }

        // Stable tie-breaker so equal values never shuffle between pages.
        $query->orderBy($query->qualifyColumn($query->getModel()->getKeyName()), $direction);
    }

    /** Selection re-scoped through tableQuery(): every matching row, or the selected ids the base query can reach. */
    protected function selectionQuery(): Builder
    {
        return $this->selectAllMatching
            ? $this->filteredTableQuery()
            : $this->tableQuery()->whereKey($this->selectedIds());
    }

    /**
     * One page of rows plus the total. The count runs only when it is needed:
     * never in simple mode, and not on a first page that is already short.
     *
     * @param  Collection<string, Column>  $columns
     * @return array{0: \Illuminate\Contracts\Pagination\Paginator, 1: int|null}
     */
    protected function tablePage(Collection $columns): array
    {
        $page = max(1, (int) $this->getPage());
        $with = $columns->flatMap(fn (Column $c): array => $c->with)->unique()->values()->all();
        $query = $this->filteredTableQuery()->with($with);
        $this->applyTableSort($query);
        $options = ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page'];

        if ($this->tablePagination() === 'simple') {
            $items = $query->forPage($page, $this->perPage + 1)->get();
            $this->primeTableRows($items);

            return [new Paginator($items, $this->perPage, $page, $options), null];
        }

        $items = $query->clone()->forPage($page, $this->perPage)->get();
        $this->primeTableRows($items);
        $total = $page === 1 && $items->count() < $this->perPage
            ? $items->count()
            : $this->filteredTableQuery()->toBase()->getCountForPagination();

        $last = max(1, (int) ceil($total / $this->perPage));
        if ($items->isEmpty() && $page > $last) {
            $this->setPage($page = $last);
            $items = $query->clone()->forPage($page, $this->perPage)->get();
            $this->primeTableRows($items);
        }

        return [new LengthAwarePaginator($items, $total, $this->perPage, $page, $options), $total];
    }

    /** @return list<string> */
    protected function currentPageIds(): array
    {
        $query = $this->filteredTableQuery();
        $this->applyTableSort($query);
        $key = $query->getModel()->getQualifiedKeyName();

        return $query->forPage(max(1, (int) $this->getPage()), $this->perPage)->pluck($key)->map(fn ($id): string => (string) $id)->all();
    }

    // ---- Definitions, abilities, state ------------------------------------

    /**
     * Abilities go through one Gate call. With `tall-ui-kit.data_table.gate` set (for
     * example 'capability'), each ability is passed as the argument of that gate;
     * with null, the ability itself is the gate name.
     */
    protected function tableGate(string $method, string $ability): mixed
    {
        $gate = config('tall-ui-kit.data_table.gate');

        return $gate === null ? Gate::{$method}($ability) : Gate::{$method}($gate, $ability);
    }

    /**
     * Hook for apps with short public references (TXN-9F3C21AB) or uuids: constrain
     * the query to the record the term names and return true, or return false to
     * fall through to the normal search. The kit does nothing here.
     */
    protected function tableReferenceSearch(Builder $query, string $term): bool
    {
        return false;
    }

    /**
     * Hook to batch-load anything rows need before they render (references, counts).
     *
     * @param  Collection<int, Model>  $items
     */
    protected function primeTableRows(Collection $items): void {}

    protected function authorizeTable(): void
    {
        if ($ability = $this->tableAbility()) {
            $this->tableGate('authorize', $ability);
        }
    }

    protected function authorizeAction(Action $action): void
    {
        if ($action->ability !== null) {
            $this->tableGate('authorize', $action->ability);
        }
    }

    protected function tableCan(?string $ability): bool
    {
        if ($ability === null) {
            return true;
        }

        return $this->tableMemo['can'][$ability] ??= $this->tableGate('allows', $ability);
    }

    /** @return Collection<string, Column> */
    protected function allowedColumns(): Collection
    {
        return $this->tableMemo['columns'] ??= collect($this->tableColumns())
            ->filter(fn (Column $c): bool => $this->tableCan($c->ability))
            ->keyBy(fn (Column $c): string => $c->key);
    }

    /** @return Collection<string, Column> */
    protected function visibleColumns(): Collection
    {
        $allowed = $this->allowedColumns();
        $chosen = array_filter(explode(',', $this->columns));

        return $allowed->filter(fn (Column $c): bool => $c->required || ($chosen === [] ? $c->defaultVisible : in_array($c->key, $chosen, true)));
    }

    /** @return Collection<string, Column> */
    protected function sortableColumns(): Collection
    {
        return $this->allowedColumns()->filter(fn (Column $c): bool => $c->isSortable());
    }

    /** @return Collection<string, Filter> */
    protected function allowedFilters(): Collection
    {
        return $this->tableMemo['filters'] ??= collect($this->tableFilters())
            ->filter(fn (Filter $f): bool => $this->tableCan($f->ability))
            ->keyBy(fn (Filter $f): string => $f->key);
    }

    /** @param  list<Action>  $actions
     * @return list<Action> */
    protected function allowedActions(array $actions): array
    {
        return array_values(array_filter($actions, fn (Action $a): bool => $this->tableCan($a->ability)));
    }

    /**
     * Validated filter values, defaults filled in. Unknown keys and invalid
     * values are dropped.
     *
     * @return array<string, mixed>
     */
    protected function activeFilterValues(): array
    {
        $values = [];
        foreach ($this->allowedFilters() as $key => $filter) {
            $value = array_key_exists($key, $this->filters) ? $filter->normalize($this->filters[$key]) : null;
            $value ??= $filter->defaultValue();
            if ($value !== null) {
                $values[$key] = $value;
            }
        }

        return $values;
    }

    /**
     * Tokens for active filters, except those sitting at their default.
     *
     * @param  array<string, mixed>  $values
     * @return list<array{key: string, label: string, value: string|null}>
     */
    protected function filterTokens(array $values): array
    {
        $tokens = [];
        foreach ($values as $key => $value) {
            $filter = $this->allowedFilters()[$key];
            if ($value === $filter->defaultValue()) {
                continue;
            }
            foreach ($filter->tokens($value) as $token) {
                $tokens[] = ['key' => $key, ...$token];
            }
        }

        return $tokens;
    }

    /** @return array{0: string|null, 1: string} the sort column key (null: key only) and direction */
    protected function resolvedSort(): array
    {
        $sortable = $this->sortableColumns();

        if ($this->sort !== '' && $sortable->has($this->sort)) {
            return [$this->sort, in_array($this->direction, ['asc', 'desc'], true) ? $this->direction : 'asc'];
        }

        $default = $this->tableDefaultSort();
        if ($default && $sortable->has($default[0])) {
            return [$default[0], in_array($default[1] ?? null, ['asc', 'desc'], true) ? $default[1] : 'asc'];
        }

        return [null, 'desc'];
    }

    /** @return list<string> */
    protected function selectedIds(): array
    {
        return array_values(array_unique(array_filter(array_map('strval', array_filter($this->selected, fn ($id): bool => is_int($id) || is_string($id))), fn (string $id): bool => $this->tableKeyIsValid($id))));
    }

    /**
     * Whether a row key from the browser has the shape of this table's keys: a positive
     * integer, or a UUID or ULID (string keys). Anything else is dropped before a query.
     * Override for another key shape.
     */
    protected function tableKeyIsValid(string $id): bool
    {
        return (ctype_digit($id) && $id !== '0' && strlen($id) <= 18)
            || (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)
            || (bool) preg_match('/^[0-7][0-9A-HJKMNP-TV-Z]{25}$/i', $id);
    }

    /** Keeps public state inside its allow-lists, so the URL only ever shows valid values. */
    protected function sanitizeTableState(): void
    {
        if (! in_array($this->perPage, self::TABLE_PER_PAGE, true)) {
            $this->perPage = 25;
        }
        if (! in_array($this->density, self::TABLE_DENSITIES, true)) {
            $this->density = 'comfortable';
        }
        if ($this->sort !== '' && ! $this->sortableColumns()->has($this->sort)) {
            [$this->sort, $this->direction] = ['', ''];
        }
        if ($this->sort === '') {
            $this->direction = '';
        } elseif (! in_array($this->direction, ['asc', 'desc'], true)) {
            $this->direction = 'asc';
        }
        if (mb_strlen($this->search) > TableQuery::MAX_TERM) {
            $this->search = mb_substr($this->search, 0, TableQuery::MAX_TERM);
        }

        $filters = $this->allowedFilters();
        $clean = [];
        foreach ($this->filters as $key => $value) {
            if (is_string($key) && $filters->has($key) && ($normalized = $filters[$key]->normalize($value)) !== null) {
                $clean[$key] = $normalized;
            }
        }
        // Defaults are written into the state, so bound controls show them; tokens skip them.
        foreach ($filters as $key => $filter) {
            if (! array_key_exists($key, $clean) && ($default = $filter->defaultValue()) !== null) {
                $clean[$key] = $default;
            }
        }
        $this->filters = $clean;

        $known = $this->allowedColumns()->keys()->all();
        $cols = array_values(array_intersect(array_filter(explode(',', $this->columns)), $known));
        $this->columns = implode(',', $cols);

        $this->selected = $this->selectedIds();
    }

    protected function tableCriteriaChanged(): void
    {
        $this->resetPage();
        $this->clearSelection();
        $this->tableAnnounce = true;
        $this->tableMemo = array_intersect_key($this->tableMemo, array_flip(['can', 'columns', 'filters']));
    }

    protected function nextTableStatus(string $text): string
    {
        return rtrim($this->tableStatus, "\u{00A0}") === $text && ! str_ends_with($this->tableStatus, "\u{00A0}") ? $text."\u{00A0}" : $text;
    }

    protected function tableAnnouncement(?int $total, mixed $rows): string
    {
        [$sort, $direction] = $this->resolvedSort();
        $count = $total !== null
            ? number_format($total).' '.($total === 1 ? 'result' : 'results')
            : ($rows && count($rows->items()) > 0 ? 'Results updated' : 'No results');
        $label = $sort !== null ? $this->allowedColumns()[$sort]->label : null;

        return $count.($label ? ', sorted by '.Str::lower($label).' '.($direction === 'asc' ? 'ascending' : 'descending') : '');
    }

    /**
     * Local and testing only: fail fast on definitions that do not fit together.
     */
    protected function validateTableDefinition(): void
    {
        $name = class_basename(static::class);
        $fail = fn (string $message) => throw InvalidTableDefinition::for($name, $message);

        $columns = $this->tableColumns();
        if ($columns === []) {
            $fail('tableColumns() returned no columns.');
        }
        $keys = [];
        foreach ($columns as $column) {
            if (! $column instanceof Column) {
                $fail('tableColumns() must return Column instances, got '.get_debug_type($column).'.');
            }
            if (isset($keys[$column->key])) {
                $fail("column key [{$column->key}] is defined twice.");
            }
            if ($column->required && ! $column->defaultVisible) {
                $fail("column [{$column->key}] is required and hidden; a required column is always shown.");
            }
            $keys[$column->key] = $column;
        }

        if ($default = $this->tableDefaultSort()) {
            [$key, $dir] = [$default[0] ?? null, $default[1] ?? null];
            if (! isset($keys[$key])) {
                $fail("default sort column [{$key}] is not a column. Known columns: ".implode(', ', array_keys($keys)).'.');
            }
            if (! $keys[$key]->isSortable()) {
                $fail("default sort column [{$key}] is not sortable.");
            }
            if (! in_array($dir, ['asc', 'desc'], true)) {
                $fail("default sort direction [{$dir}] must be asc or desc.");
            }
        }

        $filterKeys = [];
        foreach ($this->tableFilters() as $filter) {
            if (! $filter instanceof Filter) {
                $fail('tableFilters() must return Filter instances, got '.get_debug_type($filter).'.');
            }
            if (isset($filterKeys[$filter->key])) {
                $fail("filter key [{$filter->key}] is defined twice.");
            }
            $filterKeys[$filter->key] = true;
            if (in_array($filter->type, ['select', 'multi', 'status', 'boolean'], true) && $filter->options === []) {
                $fail("filter [{$filter->key}] ({$filter->type}) has no options.");
            }
            if ($filter->type === 'status' && collect($filter->options)->contains(fn (array $o): bool => $o['variant'] === null)) {
                $fail("status filter [{$filter->key}] needs a badge variant on every option.");
            }
            if ($filter->default !== null && $filter->defaultValue() === null) {
                $fail("filter [{$filter->key}] default ".json_encode($filter->default).' is not a valid value for it.');
            }
        }

        foreach (['bulk' => $this->tableBulkActions(), 'row' => $this->tableRowActions()] as $kind => $actions) {
            $actionKeys = [];
            foreach ($actions as $action) {
                if (! $action instanceof Action || $action->kind !== $kind) {
                    $fail("table{$kind}Actions() must return Action::{$kind}() instances.");
                }
                if (isset($actionKeys[$action->key])) {
                    $fail("{$kind} action [{$action->key}] is defined twice.");
                }
                $actionKeys[$action->key] = true;
                if ($action->handler === null && $action->url === null) {
                    $fail("{$kind} action [{$action->key}] needs handle() or href().");
                }
                if ($kind === 'bulk' && $action->url !== null) {
                    $fail("bulk action [{$action->key}] cannot be a link.");
                }
            }
        }

        if (! in_array($this->tableLayout(), ['scroll', 'stack'], true)) {
            $fail("layout [{$this->tableLayout()}] must be scroll or stack.");
        }
        if (! in_array($this->tablePagination(), ['length', 'simple'], true)) {
            $fail("pagination [{$this->tablePagination()}] must be length or simple.");
        }
    }

    // ---- Exports (used by WithDataTableExports) -----------------------------

    /**
     * filtered: search and filters; all: the base query; selected: the base the
     * selected ids are re-scoped through. Rows are written in id order (the
     * export writer reads lazyById), so the table's sort is not carried over.
     */
    protected function tableExportQuery(string $scope): Builder
    {
        $with = $this->visibleColumns()->flatMap(fn (Column $c): array => $c->with)->unique()->values()->all();

        return ($scope === 'filtered' ? $this->filteredTableQuery() : $this->tableQuery())->with($with);
    }

    /**
     * Export columns from the table's columns: same keys, labels and
     * abilities; the columns visible now are the export's defaults.
     *
     * @return list<ExportColumn>
     */
    protected function tableExportColumns(): array
    {
        $visible = $this->visibleColumns()->keys()->all();

        return collect($this->tableColumns())
            ->filter(fn (Column $c): bool => $c->exportable)
            ->map(function (Column $c) use ($visible): ExportColumn {
                $export = ExportColumn::make($c->key, $c->label)->ability($c->ability)->required($c->required);
                $export = match ($c->exportAs) {
                    'money' => $export->money(),
                    'number' => $export->number(),
                    'date' => $export->date(),
                    default => $export,
                };
                if ($c->exportValue) {
                    $export->value($c->exportValue);
                } elseif ($c->formatter && $c->exportAs === 'text') {
                    $export->value(fn (Model $row): ?string => ($v = $c->value($row)) === null ? null : strip_tags((string) $v));
                } elseif (str_contains($c->key, '.') || ! $c->formatter) {
                    $export->value(fn (Model $row): mixed => data_get($row, $c->key));
                }

                return in_array($c->key, $visible, true) ? $export : $export->hidden();
            })
            ->values()
            ->all();
    }

    /** @return array<string, string> */
    protected function tableExportFilters(): array
    {
        $out = [];
        if (($term = TableQuery::term($this->search)) !== '') {
            $out['Search'] = $term;
        }
        foreach ($this->filterTokens($this->activeFilterValues()) as $token) {
            [$label, $value] = explode(': ', $token['label'], 2) + [1 => ''];
            $out[$label] = isset($out[$label]) ? $out[$label].', '.$value : $value;
        }

        return $out;
    }

    /**
     * Selected ids for the "Selected rows" export. With every matching row
     * selected, the ids are read from the current criteria at export time.
     *
     * @return list<string>
     */
    protected function tableExportSelection(): array
    {
        if ($this->selectAllMatching) {
            $query = $this->filteredTableQuery();

            return $query->pluck($query->getModel()->getQualifiedKeyName())->map(fn ($id): string => (string) $id)->all();
        }

        return $this->selectedIds();
    }
}
