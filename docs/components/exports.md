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
