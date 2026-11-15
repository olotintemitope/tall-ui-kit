# Cards and charts

## Cards

A named family on one base. Usage rule: prefer a flat section (section label and 1px rules) for page regions; use cards only for peers in a grid. Never nest cards, never put a card inside a panel or a panel inside a card.

Base (`x-ui.card`): white, 1px `line` border, 8px radius, no shadow (cards are flat; `x-ui.panel` is the only elevated surface), 16px padding. Optional header above a 1px rule (`title` in the heading role, `description` in meta, `badge` and `actions` slots); optional `footer` slot below a 1px rule on `panel-footer`, meta text or actions; `flush` drops the body padding. No coloured or thick edges, icon tiles, coloured headers, hover lift or shadow. The older composition (`x-ui.card.header`, `title`, `description`, `content`, `footer`) still works.

| Card | Use | Props and slots |
|---|---|---|
| `x-ui.card.summary` | One metric. Three or more in a row read better as an `x-ui.stat-strip` | `label`, `value`, `money`, `currency`, `previous`, `period`, `upIsGood`, `sparkline` (points), `sparklineLabel`, `sub`, `loading`, `error`; slot value |
| `x-ui.card.list` | Rows divided by 1px rules: `x-ui.kv` (`as="dl"`), `x-ui.ranked-item` (`as="ol"`) or `x-ui.card.row` (default `ul`: body-strong title, meta line, optional numeric value) | `title`, `description`, `as`, `empty`, `viewAll` (href; a "View all" `x-ui.link-arrow` footer), `viewAllLabel`, `loading`, `error`; slot `actions` |
| `x-ui.card.table` | A flush `x-ui.table` edge to edge with a pagination footer | `title`, `description`, `caption`, `paginator`, `livewire`, `empty`, `loading`, `rows`, `error`; slots `actions`, `head`, `footer` |
| `x-ui.card.chart` | The `x-ui.chart` anatomy in a card | every `x-ui.chart` prop; slots `actions`, `summary` |
| `x-ui.card.detail` | One record: title, a status badge, actions, then a two-column `dl` (one column on phones), labels in meta, values in body or numeric | `title`, `description`, `status`, `statusVariant`, `items` (label, value, numeric); slot of `x-ui.card.detail-item` (`label`, `numeric`); slots `actions`, `footer` |
| `x-ui.card.link` | The whole card is one anchor: body-strong title, meta line, `ink-muted` arrow. Hover fills `surface-subtle`; keyboard focus shows the focus ring | `href`, `title`, `meta` |
| `x-ui.card.profile` | A person: neutral initials avatar, name, role or meta line, optional "Verified" (`info` badge), an inline strip of 2 or 3 small stats (meta label, numeric value) | `name`, `meta`, `verified`, `stats`; slot `actions` |
| Selectable: `x-ui.radio-group variant="cards"` | Plain one-of choices (plan, schedule) | See the components table |
| Selectable: `x-ui.card.selectable` | A choice with a price column: a real radio or checkbox, title, meta lines, badges, the price (numeric, plain text: catalogue prices are not private) with `unit` and `priceNote` captions | `type` radio or checkbox, `name`, `value`, `title`, `meta`, `price`, `currency`, `unit`, `priceNote`, `badges`, `checked`, `disabled`, `reason` (linked with `aria-describedby`); `wire:model` / `x-model` go on the input; slot replaces the price column |

Selectable states match the radio-group cards: 1px `control` border (3.65:1), hover `control-hover`, checked 1px `primary-text` border (5.21:1) and a `primary/5` tint with the control filled ink, focus ring around the card, 8px radius; disabled keeps a `line` border and `cursor-not-allowed` and says why in `reason`. `x-marketplace.target-option` can migrate to `x-ui.card.selectable` (and `x-marketplace.icon` to the kit icons) later; the marketplace files are unchanged.

States: empty is one meta line at the height of its content; loading uses skeletons (announced once); error is an inline `x-ui.alert`.

The gallery shows every type, the states, and a dashboard grid (3 summary cards, a list card and a table card) that stacks to one column on phones, where the table scrolls sideways inside its card.

## Charts

`x-ui.chart` (`resources/views/components/ui/chart/index.blade.php`). One look per type; anything else is not in the kit.

**Rendering.** Line, bar and stacked-bar plots are drawn on the server from `TallUiKit\Support\Charts\ChartPlot` (nice ticks in `NiceScale`, label thinning in `TickThinning`), so they are sharp at any DPR, zoom and width and their text is the page's own:

- The plot has a fixed height (the chart height less a 20px x-axis row), so y is in real CSS pixels: gridlines and the axis sit on half pixels (`shape-rendering="crispEdges"`, 1px, one device pixel at DPR 1). Only x stretches: one `<svg>` with a 1000-unit `viewBox` and `preserveAspectRatio="none"`, every stroke `vector-effect="non-scaling-stroke"`, so lines keep 1.5px and dashes keep 4 3 at any width. Nothing is redrawn or measured on resize.
- Bars are HTML boxes (percentage widths capped at 24px, heights in whole pixels), which the browser snaps to device pixels. Stack segments are rounded once at each running total, so a stack is exactly its total's height.
- Ticks are HTML text in the caption role, placed in percentages. The y-axis column is as wide as its widest tick. X labels are thinned on the server to at most six, with nested strides (s, 2s, 4s) whose extra labels show from a container width on the axis row (`@min-[320px]:block`, every 32px up to 512px, then wider steps), so labels never overlap and nothing is measured in the browser. The first label may hang 12px into the y-axis column; edge labels are clamped inside the plot.
- The `uiChart` component (`adminChart` is the same) is one Alpine component per chart, with no per-point Alpine: pointer-move finds the nearest index from the stored x positions and draws a 1px crosshair, hover dots (lines) or marks the slot (bars), and fills the tooltip. The overlay is `wire:ignore`; the plot is not, so Livewire morphs the new SVG in place.

| Type | For | Look |
|---|---|---|
| `line` | Time series | 1.5px straight lines (tension 0), no fill, points (4px, white ring) and a 1px `chart-axis` crosshair only on hover. Up to 3 series; the third is dashed (4 3), so colour is never the only cue, and its legend key is a dashed line instead of a square |
| `bar` | Categorical or daily counts | Bars at most 24px wide, 2px top corners, y from 0. One series (hover darkens to `chart-1`) or up to 3 grouped |
| `stacked-bar` (or `bar` + `stacked`) | Composition over time | Up to 3 segments separated by a 1px white gap (a white top edge, not a border); only the top segment is rounded |
| `bar-horizontal` | Ranked categories | Rows, not a plot: label in body on the left (128px, wrapping), bar, value in numeric right-aligned (80px). Up to 8 rows it is the ranked-list share row (4px bar on a `surface-muted` track, 1px rules); above 8 it is a denser 24px row with an 8px bar and no rules |
| `sparkline` | A trend inside a table cell or stat strip, never decoration | 24px tall inline SVG (`x-ui.chart.sparkline`, default 80px wide), 1.5px `chart-2` line, 3px dot on the last point, y anchored at 0. No axes, no tooltip |

Banned: pie, donut, gauge, radar, area or gradient fills, 3D, shadows, glow, smoothing, pill legends, and more than 3 series. An unknown type or a fourth series throws in local and testing (fold the rest into "Other" or split the chart).

**Anatomy** (every type except sparklines; each part only when asked for):

- Header: title in the label role, `period` in meta beside it, `actions` slot on the right for a `x-ui.segmented size="sm"` period control; `toggle` adds an icon-only chart/table segmented control.
- Summary: `total` in the figure role plus `x-ui.delta` (when `previous` is given), or the `summary` slot. Shown when there is a title; `total` defaults to the sum of the points.
- Legend (2 or 3 series, plotted types): inline 8px square swatches, label in meta, the series total in numeric. Under the summary, never inside the plot.
- Plot: horizontal gridlines only (`chart-grid`), x axis line `chart-axis`, ticks in the caption size coloured `chart-tick`; ISO days print as "14 Nov", other labels as given. `yFormat` number (default), compact or percent.
- Tooltip: HTML, white, 1px `panel-border`, `rounded-md`, `shadow-popover`; the date in meta, each value in numeric with a square swatch and, with several series, its name in meta. Built with `textContent` only.
- Empty (all zero): the plot stays (a flat line, or empty bars on a 0 to 1 axis) with the `empty` message as a caption over it, so the frame matches its neighbours. Horizontal bars show the message as one meta line.
- Loading (`loading`): a skeleton the same height as the plot (and the figure when there is a summary), announced once.
- Error (`error="..."`): an inline `x-ui.alert tone="danger"` in place of the plot.
- Privacy: money charts (`money`, `currency`) hide the y-axis ticks (keeping the column's width, through `html[data-privacy=hidden]`, so nothing flashes before Alpine starts), show "•••••" in the tooltip, blur the summary, legend totals and table cells (`x-ui.money`), and drop the amount from the accessible name ("total hidden").

**Accessibility**: the plot is `role="img"` with a summary as its name, e.g. "Revenue, last 30 days, ₦15 total, down 99% vs prior period" (`label` overrides the first part; the empty message is appended). Every chart carries a visually hidden data table; with `toggle` the same table is shown in place of the plot (`x-ui.table` look). Series colours come only from chart tokens (`token`), each at least 3:1 on white.

**Props**: `type`, `series` (list of `name`, `token`, `points`; `label`, `color`, `data` also accepted, plus an optional `total`), `labels`, `height` (default 160px, `sm` 120px, or an `h-*` class), `money`, `currency`, `stacked`, `yFormat`, `empty`, `title`, `period`, `label`, `total`, `previous`, `deltaPeriod` ("30d"), `upIsGood`, `legend` (false to hide), `toggle`, `loading`, `error`. Slots `actions`, `summary`.

The overview uses the same chart: `x-admin.chart` is a thin wrapper (plot only, `adminChart` name kept, `h-32`), and `x-admin.trend` draws its own row header and passes `total`, `previous` and the period through for the accessible summary.
# Data tables

A server-driven data table for Livewire pages: search, sort, filters, pagination, column
visibility, density, selection with bulk actions, row actions and exports. One trait
(`TallUiKit\Livewire\Concerns\WithDataTable`), definition classes in `TallUiKit\Support\DataTable`, and the
`x-data-table.*` components, composed from `x-ui.*`.

- Demo: the workbench `/tables` page (`workbench/app/Livewire/TableGallery.php`): 320 invented
  payments on a private SQLite connection (`DemoTables`), 15 columns, 7 filters, bulk and row
  actions, a layout switch (scroll or stacked cards) and an empty and error state switch.
  Under each demo a Properties panel lists the table's definitions and current state, generated from
  the component (`tableProperties()`), so it cannot drift from the code.
- Abilities: `Column::ability()`, `Action::ability()` and `tableAbility()` go through Laravel's Gate. Set `tall-ui-kit.data_table.gate` to route them through one capability gate (`Gate::allows('capability', $ability)`), or leave it null to use each ability as a gate name.
- App hooks: override `tableReferenceSearch(Builder $query, string $term): bool` to look rows up by a short public reference or uuid, and `primeTableRows(Collection $items)` to batch-load what rows need. The kit does nothing in either.

## Quick start

```php
use TallUiKit\Livewire\Concerns\WithDataTable;
use TallUiKit\Support\DataTable\{Action, Column, Filter};

class Index extends Component
{
    use WithDataTable;

    protected function tableAbility(): ?string { return 'payouts.read'; }

    protected function tableQuery(): Builder { return Payout::query(); }   // rows this user may see

    protected function tableColumns(): array
    {
        return [
            Column::make('id', 'ID')->required()->role('mono')->sortable(),
            Column::make('curator', 'Curator')->role('strong')
                ->sortable('user.first_name')->searchable(['user.first_name', 'user.last_name'])
                ->with('user:id,first_name,last_name,email')
                ->format(fn ($p) => $p->user?->name)->secondary(fn ($p) => $p->user?->email),
            Column::make('amount', 'Amount')->numeric()->sortable()->view('livewire.admin.payouts.amount')->export('money'),
        ];
    }

    protected function tableFilters(): array
    {
        return [Filter::status('status', 'Status')->options(['pending' => ['label' => 'Pending', 'variant' => 'warning']])->default(['pending'])];
    }

    protected function tableDefaultSort(): ?array { return ['created_at', 'asc']; }

    public function render(): View { return view('livewire.admin.payouts.index', ['table' => $this->dataTable()]); }
}
```

```blade
<x-ui.page-header title="Payouts" />
<x-data-table :table="$table" />
```

## Components

Every component's Blade doc comment lists the same props.

### `x-data-table`

| Prop or slot | Type | Default | Allowed | Description | Example |
|---|---|---|---|---|---|
| `table` | `TableView` | required | from `dataTable()` | Everything the table shows | `:table="$table"` |
| `heading` | string or null | null | any | Visible h2 above the toolbar, also the focus target after paging. Without it a visually hidden h2 ("Transactions, 1,284 results, page 2 of 52") is used | `heading="Payouts"` |
| slot `toolbar` | html | none | | Extra controls after the filters | `<x-slot:toolbar>…</x-slot:toolbar>` |
| slot `empty` | html | none | | Replaces the empty-dataset action | `<x-slot:empty><x-ui.button …/></x-slot:empty>` |

### Parts (used by `x-data-table`; usable alone with a `TableView`)

| Component | Props (type, default) | Description |
|---|---|---|
| `x-data-table.toolbar` | `table` (TableView), `initial` (bool, true) | Search (`x-ui.search`, 300ms debounce), inline filters (1 to 3, from 640px) or a Filters button with a count badge and a side sheet, result count, column menu, density, export menu. Static controls render once inside `wire:ignore` |
| `x-data-table.filter` | `filter` (Filter), `context` (`inline` or `sheet`, inline), `name` (string, `dt`) | One filter control by type, bound to `filters.{key}` |
| `x-data-table.tokens` | `table` | Active filters as `x-ui.filter-token`s in a list under a hidden "Active filters" heading, plus Clear all |
| `x-data-table.header` | `table` | The `<thead>`: `th scope="col"`, `aria-sort`, sort `<button>`s, select-all checkbox |
| `x-data-table.row-menu` | `table` | One action menu shared by all rows |
| `x-data-table.bulk-bar` | `table` | "3 selected · Select all 1,284 · Clear" and the bulk action buttons |
| `x-data-table.footer` | `table`, `initial` (bool, true) | Rows per page, "Showing 1–25 of 1,284", pagination |
| `x-data-table.pagination` | `paginator` (Paginator), `name` (string) | Previous, page numbers, next; unavailable buttons use `aria-disabled` |
| `x-data-table.states` | `state` (`skeleton`, `empty`, `no-results`, `error`), `table`, `colspan` (int, 1) | Non-row states |
| `x-data-table.properties` | `properties` (array), `title` (string, Properties), `name` (string, `dt`) | The gallery's Properties panel |
| `x-data-table.assets` | none | Scoped CSS, icon sprite and the Alpine controller, printed once per page |

## Definitions

### `Column` fields

| Field (method) | Type | Default | Allowed | Description | Example |
|---|---|---|---|---|---|
| `key` (`make`) | string | required | `^[a-z][a-z0-9_]*$` | Identity; used in `sort` and `cols` URLs | `make('amount', 'Amount')` |
| `label` (`make`) | string | required | | Header and stacked-card label | |
| `sortBy` (`sortable()`) | bool, string, Closure | false | true, column, `a.b.col` (belongsTo path, correlated subquery), `count:relation`, `fn (Builder $q, string $dir)` | Sort source; anything else is unknown and ignored | `sortable('user.first_name')` |
| `searchPaths` (`searchable()`) | list | [] | columns or dot paths | Columns searched; dot paths use `whereHas` | `searchable(['user.email'], prefix: true)` |
| `searchClosure` (`searchable(fn)`) | Closure | null | `fn (Builder $q, string $word, string $escapedLike)` | Custom search | |
| `searchPrefix` (`searchable(prefix:)`) | bool | false | | `word%` instead of `%word%`, so an index is used | ids, emails, references |
| `exactPattern` (`exact()`) | regex | null | valid regex | When the whole term matches, exact equality on the first path and nothing else | `exact('/^NB-\d{5}$/i')` |
| `numeric` (`numeric()`) | bool | false | | Right-aligned, numeric role | |
| `role` (`role()`) | string | body | body, strong, meta, mono, numeric | Cell type role | `role('strong')` |
| `width` (`width()`) | string | null | Tailwind classes in a scanned file (views, `app/Livewire`) | Width or max width; text in the cell wraps within it (never an ellipsis) | `width('max-w-56')` |
| `defaultVisible` (`hidden()`) | bool | true | | Shown until the user changes columns | `hidden()` |
| `required` (`required()`) | bool | false | | Always shown, disabled in the column menu, always exported | |
| `ability` (`ability()`) | string | null | capability | Needed to see, sort, search, filter or export the column | `ability('*')` |
| `formatter` (`format()`) | Closure | null | `fn (Model): string|Htmlable|null` | Cell text; strings are escaped and highlighted | |
| `view` (`view()`) | string | null | view name | Cell view, given `$row`, `$column`, `$term` | `view('livewire.admin.transactions.amount')` |
| `secondaryLine` (`secondary()`) | Closure | null | `fn (Model): ?string` | Meta second line (two-line cell) | |
| `wrap` (`wrap()`) | bool | false | | A cell view prints two lines | |
| `with` (`with()`) | list | [] | relations | Eager loaded only while the column is visible | `with('wallet.user:id,first_name')` |
| `highlightMatches` (`highlight()`) | bool or null | null (on for searchable text without a view) | | `<mark>` around search matches | |
| `exportAs`, `exportValue` (`export()`) | string, Closure | text, null | text, number, money, date | Export type and value | `export('money')` |
| `exportable` (`withoutExport()`) | bool | true | | Leave out of exports | |

### `Filter` fields

| Field (method) | Type | Default | Allowed | Description | Example |
|---|---|---|---|---|---|
| `key`, `label`, `type` (`make` or a typed constructor) | string | required | type: select, multi, status, boolean, date-range, number-range | URL key `filters[key]` | `Filter::multi('type', 'Type')` |
| `options` (`options()`) | array | [] | `value => label` or `value => ['label', 'variant']` (variant: neutral, success, warning, danger, info, accent) | Choices; status filters need a variant on each | |
| `column` (`column()`) | string | the key | column or dot path (`whereHas`) | What the value applies to | `column('customer.country')` |
| `using` (`using()`) | Closure | null | `fn (Builder $q, mixed $value)` | Replaces the default query | scopes, computed filters |
| `default` (`default()`) | mixed | null | a valid value | Applied when absent; written into the state; its token is hidden; Clear returns to it | `default('NGN')` |
| `ability` (`ability()`) | string | null | capability | Filter shown and applied only with it | |
| `placeholder` (`placeholder()`) | string | All, or Any for boolean | | Empty option of select filters | `placeholder('All platforms')` |
| `min`, `max` (`between()`) | float | null | | Number range bounds; outside values are dropped | `between(0, 10_000_000)` |

Values: select a string from the options; multi and status a list (definition order); boolean
`'1'` or `'0'`; date-range `['from' => 'Y-m-d', 'to' => 'Y-m-d']` (either end, inclusive, Lagos
time; a reversed range is ignored); number-range `['min' => n, 'max' => n]`. Anything else is
dropped by `normalize()`, so a hand-edited URL never reaches the query.

### `Action` fields (bulk and row)

| Field (method) | Type | Default | Description | Example |
|---|---|---|---|---|
| `key`, `label`, `kind` (`Action::bulk()` or `Action::row()`) | string | required | `^[a-z][a-z0-9_-]*$` | `Action::row('reject', 'Reject')` |
| `ability` (`ability()`) | string | null | Checked on the server before it runs, and before it is shown | `ability('payouts.write')` |
| `destructive` (`destructive($title, $description, $label = 'Delete')`) | bool | false | Red, and confirms first (`x-ui.confirm-dialog`); the server refuses it unless confirmed | |
| `confirmTitle`, `confirmDescription`, `confirmLabel` (`confirm()`) | string | null, null, Confirm | A confirm dialog without the danger styling | |
| `handler` (`handle()`) | Closure | null | Bulk: `fn (Builder $rows, int $count)`, rows re-scoped through `tableQuery()`. Row: `fn (Model $row)`, loaded through `tableQuery()`. A returned string is the toast | |
| `url` (`href()`) | Closure | null | Row only: `fn (Model): string`, rendered as a link | |
| `visibleWhen` (`visible()`) | Closure | null | Row only: `fn (Model): bool`, per-row availability, also checked on the server | `visible(fn ($p) => $p->status === 'pending')` |
| `icon` (`icon()`) | string | null | Kit icon name | `icon('trash-2')` |

## `WithDataTable`

### Public state (URL)

| Property | URL | Type | Default | Allowed | Description |
|---|---|---|---|---|---|
| `search` | `q` | string | '' | up to 100 characters | Search term; trimmed, single-spaced, ignored under 2 characters |
| `sort` | `sort` | string | '' (the default sort) | a sortable column key | Unknown keys reset to the default |
| `direction` | `dir` | string | '' | asc, desc | Anything else becomes asc |
| `filters` | `filters[key]` | array | defaults | per filter definition | Unknown keys and invalid values are dropped |
| `perPage` | `per` | int | 25 | 10, 25, 50, 100 | |
| `page` | `page` | int | 1 | | From `WithPagination`; clamped to the last page |
| `columns` | `cols` | string | '' (defaults) | comma-separated known keys | Visible columns, in definition order |
| `density` | `density` | string | comfortable | comfortable, compact | 40px or 32px rows |

Not in the URL: `selected` (list of id strings on screen), `selectAllMatching` (bool: every row
matching the current search and filters, kept as criteria, never as thousands of ids),
`tableLoaded` (bool, deferred first load) and `tableStatus` (the live region text).
Search, filter, sort and page size changes reset the page and the selection.

### Methods the component implements

| Method | Returns | Default | Description |
|---|---|---|---|
| `tableQuery()` | Builder | required | Rows this user may see; search, filters, sort and selections are applied on top |
| `tableColumns()` | list of Column | required | |
| `tableFilters()` | list of Filter | [] | |
| `tableBulkActions()` | list of Action | [] | Selection checkboxes appear only with bulk actions |
| `tableRowActions()` | list of Action | [] | |
| `tableAbility()` | ?string | null | Page capability, checked on mount, on every request and before every action |
| `tableDefaultSort()` | ?array | null (key desc) | `[column key, asc or desc]` |
| `tableCaption()` | string | class name | Table caption and scroll region name |
| `tableNoun()` | string | rows | Plural noun: "No transactions yet.", "Loading transactions" |
| `tableSearchPlaceholder()` | string | "Search {noun}…" | |
| `tableLayout()` | string | scroll | scroll (sideways in its box, first column pinned) or stack (cards below 640px) |
| `tablePagination()` | string | length | length (counts, page numbers) or simple (previous and next, no count query) |
| `tableDeferred()` | bool | true | First paint is a skeleton; rows load with `wire:init` |
| `tableRowLabel($row)` | string | `#id` | Used in "Select row …" and "Actions for …" |
| `tableKeyIsValid($id)` | bool | positive integers, UUIDs and ULIDs | Shape check for row keys from the browser (selection, row actions); anything else is dropped before a query. Override for another key shape |
| `tableIdPattern()` | ?string | `/^#?(\d{1,18})$/` | A matching term searches the key exactly |
| `tableEmpty()` | array | "No {noun} yet." | `title`, `description`, `actionLabel`, `actionHref` |

### Methods the browser may call

`loadTable()`, `sortBy($key)`, `toggleFilterValue($key, $value)`, `removeFilter($key, $value = null)`,
`clearFilters()`, `clearSearchAndFilters()`, `toggleColumn($key)`, `resetColumns()`, `togglePage()`,
`selectAllMatchingRows()`, `deselectFromAll($id)`, `clearSelection()`, `retryTable()`,
`runBulkAction($key, $confirmed = false)`, `runRowAction($key, $id, $confirmed = false)`, plus
`gotoPage`, `previousPage`, `nextPage` from `WithPagination`. Every argument is re-validated.

### Other methods

`dataTable(): TableView` (one render's data), `tableProperties()` (definitions and state for the
Properties panel), protected `filteredTableQuery()`, `applyTableSearch()`, `applyTableSort()`,
`selectionQuery()`, `tablePage()`, `currentPageIds()`, `authorizeTable()` (override for a
non-capability gate, as the gallery does), `allowedColumns()`, `visibleColumns()`,
`allowedFilters()`, `activeFilterValues()`, `resolvedSort()`, `validateTableDefinition()`.

### Events

| Event | From | Payload | Description |
|---|---|---|---|
| `toast` | trait | type, message | After bulk and row actions ("Deleted 3 payments") |
| `open-confirm` / `confirmed` | Alpine, `x-ui.confirm-dialog` | `{name}-bulk-{key}` or `{name}-row-{key}` | Confirm dialogs; `confirmed` runs the action with `$confirmed = true` |
| `open-dialog` | export menu (kit) | dialog name | Custom range and column picker |

## Search

Trimmed, single-spaced, at most 100 characters, at least 2. A term matching `tableIdPattern()` is
an exact key lookup; a term matching a column's `exact()` pattern is an exact equality on that column.
Otherwise each word (up to 5) must match at least one searchable column: `%word%`, or `word%` for
prefix columns. `%`, `_` and the escape character are escaped (`ESCAPE '!'`, the same in MySQL and
SQLite). Matches are wrapped in `<mark>` in searchable text cells.

Indexes: transactions search `wallet_transactions.reference` (unique index), `users.email` (unique
index, prefix match) and `users.first_name`, `users.last_name`, `campaigns.title`,
`wallet_transactions.description` with `%word%`, which cannot use an index. On a large ledger,
add a full-text index (or a search column) on those text columns before relying on contains search;
ids and references stay fast.

## Sorting

Header buttons cycle ascending, descending, then the default. Only columns with `sortable()` are
accepted; the key is always added as a tie-breaker in the same direction, so pages never shuffle.
Relation sorts use a correlated subquery through belongsTo hops (no joins, so selected columns and
keys stay the base table's); `count:relation` uses `withCount`.

## Filters

1 to 3 filters sit in the toolbar from 640px; more, or any phone, use the Filters button and side
sheet (count badge = active filters). Active values show as tokens (one per chosen option for multi
filters); filters at their default are not listed. Clear all resets to the defaults.

## Pagination

Length-aware by default. The count query runs separately, and only when needed: never in simple
mode, and not on a first page that is already shorter than the page size. A page past the end
falls back to the last page.

## Selection and bulk actions

Row checkboxes select rows on the current page; the header checkbox selects or clears the page
(indeterminate when some are). The bulk bar then offers "Select all 1,284", which stores the
criteria, not ids. Before any bulk action the selection is re-scoped through `tableQuery()` (ids
the query cannot reach are ignored); select-all-matching runs on the current search and filters.
Destructive actions confirm first, and the server refuses them unconfirmed.

## Exports

Exports are not in the package yet: the CSV and PDF pipeline (`WithExports`, the export writer,
Dompdf, queued jobs) stays in the app that owns it (see [exports.md](exports.md)). The table
side is ready for it: `tableExportColumns()` maps visible columns to `ExportColumn`s and an app
trait such as NewBanger's `WithDataTableExports` wires it up. With that trait the
toolbar shows `x-ui.export-menu`; Current view exports the current search and filters; visible
columns are the picker's defaults (a saved column choice wins); ability-gated columns are never
offered or accepted; Selected rows exports the selection re-scoped through `tableQuery()` (with
every matching row selected, the ids are read from the criteria at export time). Rows are written
in id order: the export writer reads `lazyById`, which cannot keep an arbitrary sort (a known limitation).

## Authorization

The page ability (`tableAbility()`) is checked on mount, on every later request and before every
action. Column, filter and action abilities hide them from the markup and are enforced again on the
server (sorting, searching, filtering and exporting a column the user may not see is impossible).
In NewBanger, for example, the fee split column needs the super admin wildcard (`*`).

## Focus

Livewire re-renders never drop focus to the body. The rules, checked in the browser on the gallery:

1. Sort, search, filter, page size and density: focus stays on the control used (the nodes are kept
   by `wire:key`, and the search, filter, column and density controls are rendered once inside
   `wire:ignore`, so typing never loses the caret). The result count and sort are announced in a
   polite live region ("1,284 results, sorted by date descending").
2. Removing a filter token: the next token, then the previous one, then the search box.
3. A row removed (row action or bulk action): the next row's first control, then the previous row,
   then the table heading.
4. The bulk bar: focus is never moved into it; the count is announced ("3 selected"). Clear returns
   focus to the select-all checkbox.
5. Row menus and confirm dialogs: focus returns to the row's actions button; if the row is gone,
   rule 3.
6. Pagination is the one exception to rule 1: focus moves to the table heading (visually hidden h2
   "Sample payments, 317 results, page 2 of 13"), so screen reader users start at the new results.
7. Controls that stay focused while loading are never `disabled` (pagination uses `aria-disabled`).
8. Every row, header cell and control has a stable `wire:key`.

The controller (`window.uiDataTable`, in `assets.blade.php`) runs from a Livewire `morphed`
hook in the same task as the morph, before the kit's 60ms focus settle in `resources/js/ui.js`
(which then finds focus placed and leaves it). Its focus state lives on the root element because a
morph can leave the root with a cloned Alpine scope. Migrate its `focusNear` and intent handling to
the kit's `focusAfter` / `returnFocus` helpers once they settle.

## Layout, states and look

- Toolbar: search (full width on phones), filters, then the result count in meta, the Columns menu,
  the density toggle (icon-only segmented) and Export on the right.
- Header: label role, sticky inside the table's own scroll box, `scope="col"`, `aria-sort`; the
  active sort shows a chevron in ink; inactive sortable columns show a muted double chevron only on
  hover or focus. Numeric columns are right-aligned.
- Body: no zebra; 1px `line` rules; hover fill `panel-footer`; selected rows tinted `primary` at 5%
  with the checkbox; two-line cells (meta second line); every cell wraps and the row grows (no ellipsis; numbers stay on one line);
  `<mark>` (primary at 18% on white, ink text) for search matches.
- Scroll layout: the table scrolls inside its bordered box (a focusable named region while it
  scrolls); the checkbox and first column stay pinned. Stack layout: below 640px each row is a card
  (title, then label and value pairs, checkbox and actions pinned); explicit table roles keep the
  semantics. Gallery: scroll by default, stack on its switch. Transactions: stack.
- States: first load skeleton rows at the final row height (one status, "Loading payments");
  refresh keeps rows and uses `x-ui.loading-overlay` (`aria-busy`, spinner chip, no fading, as the
  kit rules require); empty dataset one line plus an optional action; no results "No results for
  'xyz'" plus "Clear search and filters"; error an inline danger alert with Retry.
- Footer: rows per page, "Showing 1–25 of 1,284" in meta, pagination.
- Bulk bar: sticky to the bottom; it measures its own height (`--dt-bulk-h`, several lines when it
  wraps at 320px) and the table box and the page take that as bottom scroll padding while rows are
  selected, so the bar never covers a focused row or control, in the scroll and stacked layouts.
- Motion: opacity and transform only; none under reduced motion.

## Performance

- Eager loads come only from visible columns' `with()`; row actions must not touch relations that
  might not be loaded. Query count is constant: page, count (when needed), one per relation
  (measured in NewBanger: 5 queries at 10 and at 100 rendered rows).
- Public properties are scalars and flat arrays.
- Responses are kept small: rows are printed by `RowRenderer` as compact HTML (no Blade
  indentation or block markers), per-cell classes and stacked labels are carried once on the header
  and applied in the browser after each morph, the row menu and confirm dialogs are rendered once,
  and the toolbar, sheet, column menu, export menu and footer select are rendered once inside
  `wire:ignore`. A sort or search round trip at 25 rows stays under 40 KB
  (tested).

# Exports

> **Package scope.** The kit ships the export UI (`x-ui.export-menu`, `x-ui.dropdown.sub`,
> `x-ui.export-columns`) and the pure definition classes `TallUiKit\Support\Exports\ExportColumn` and
> `ExportRange`. The pipeline below (`WithExports`, the writer, Dompdf, jobs) is NewBanger app code,
> documented here as the reference implementation an app provides. Settings the kit reads:
> `tall-ui-kit.exports.timezone`, `pdf_landscape_after`, `pdf_suggest_csv_after`.

CSV and PDF exports for Livewire pages: a multi-level export menu, a column picker, and the
`WithExports` trait behind them (app side, see the note above).

## Components

### `x-ui.export-menu`

Trigger: `x-ui.button variant="outline" size="sm"` with a download icon, "Export" and a chevron;
`loading="export"` keeps its width and shows a spinner while the export runs.

| Level | Items |
|---|---|
| CSV | Current view (filtered), All records, Selected rows (only with a selection) |
| PDF | Summary report, Detailed report |
| Date range | All dates, This month, Last month, Last 90 days, Custom… (dialog with two dates) |
| Choose columns… | Opens the picker; hint "8 of 12 columns" |

Props: `name` (dialog prefix), `columns` (`exportColumnOptions()`; `true` fetches them on first
open so the page's queries stay unchanged; `null` hides the picker), `rows` and `total` (hints),
`selection` (name of the Livewire array property of selected ids), `formats`, `ranges`, `range`
(initial preset), `align`, `label`. Items call `$wire.export(format, scope, range, columns)`.
The server toasts "Export ready", or "We'll email you when it's ready" when queued.

### `x-ui.dropdown.sub` (submenus)

`<x-ui.dropdown.sub label="CSV" hint="...">items</x-ui.dropdown.sub>` inside `x-ui.dropdown`.
A `menuitem` with `aria-haspopup="menu"` and `aria-expanded`, a right chevron, and a nested
`role="menu"` labelled by the trigger.

- Desktop: flies out to the right and flips left near the viewport edge. Opens on hover, click,
  Enter, Space or ArrowRight, with focus on its first item. ArrowLeft goes back to the trigger.
- Below 640px: drills down inside the same panel. Siblings hide and a Back row leads up.
- Escape closes every level and returns focus to the trigger. Arrow keys, Home and End move
  within the current level. Focus is roving (tabindex 0 on the focused item only).

The existing dropdown API is unchanged. `x-ui.dropdown.item` adds `hint` (meta text on the
right, also a named slot), `role` (`menuitemradio` or `menuitemcheckbox` with `aria-checked`) and
`data-keep-open`. Item rows are at least 32px and wrap (long labels are never cut).

### `x-ui.export-columns`

An `x-ui.dialog` titled "Columns to export". A checkbox list: heading in body-strong, optional
meta description, required columns checked, disabled and tagged "Required". Select all shows an
indeterminate state. Drag handles reorder, and Alt+ArrowUp/ArrowDown on a focused handle moves the
column, announced in an `aria-live` region. A search box appears above 12 columns. Footer: "Reset to
default" (ghost) on the left; CSV/PDF `x-ui.segmented size="sm"`, Cancel and Export on the right.
Export stays disabled until a column is chosen (caption hint). PDF captions: above 8 columns the
PDF is landscape; above 12 it suggests CSV. Text only.

## App side: `WithExports` (reference implementation)

```php
use WithExports;

protected function exportQuery(string $scope): Builder;   // filtered | all | selected (base for ids)
protected function exportColumns(): array;                // list<ExportColumn>
protected function exportTitle(): string;
protected function exportAbility(): string;               // capability checked first
// optional: exportResource(), exportDateColumn(), exportFilters(), exportCurrencyKey(), exportSelection()
```

`ExportColumn::make('amount', 'Amount')->money()->total()->description('...')->pdfWidth(9)`.
Types are text, number, money and date. Modifiers: `required()`, `hidden()` (not default visible),
`ability('*')` (only users with it may request the column), `group()` and `total()` (summary
report), `value(fn ($row) => ...)` (formatter).

`export(string $format, string $scope, ?array $range = null, ?array $columns = null)`:

1. `Gate::authorize('capability', exportAbility())`.
2. Validate the format (`csv` with `filtered`, `all` or `selected`; `pdf` with `summary` or
   `detailed`), the range, and the columns. Unknown or forbidden keys are rejected. Required columns
   are always added. Passed columns are saved per user per resource in `user_preferences`
   (`exports.columns.{resource}`). Without columns, the saved choice or the defaults are used.
3. Selected ids are re-scoped with `exportQuery('selected')->whereKey($ids)`.
4. Above `config('exports.sync_row_limit')` (5000) rows, `GenerateExport` is queued. It writes to
   `storage/app/private/exports/{user}/{token}/` and emails a signed link (`exports.download`) that
   expires after 24 hours and opens only for that user. `PruneExpiredExports` runs hourly and
   deletes older files.
5. Otherwise a `streamDownload` is returned.

Every export is logged in the activity log with the user, resource, format, scope, row count and
whether it was queued. Contents are never logged.

CSV: UTF-8 BOM, `fputcsv`, rows read with `lazyById(1000)`. Money is a plain number with a currency
column, and dates are ISO 8601 in Africa/Lagos. Text cells starting with `=`, `+`, `-`, `@`, tab or
CR get a leading `'`. Genuine numbers from money and number columns are written as numbers, so
negative amounts stay numeric. Filename: `{app}-{resource}-{scope}-{YYYY-MM-DD}.{csv|pdf}`.

PDF (`barryvdh/laravel-dompdf`, `resources/views/exports/pdf.blade.php`): inline print CSS
mirroring the kit. It uses Helvetica, the type scale, 1px `line` rules, monospace numerals and the
logo, with `#BC4530` only on the rule under the title. The header shows the title, filters, date
range, generation time and user. The footer shows "Page X of Y". The detailed report is the table,
in landscape above 8 columns. The summary report shows totals and grouped figures, split by
currency so naira and dollars are never added together.
