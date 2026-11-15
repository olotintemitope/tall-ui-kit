# Forms and form layouts

Companion to [the components index](README.md); field-level controls (`x-ui.field`, `x-ui.input`, `x-ui.select`, ...) are in its Components table and in [html-elements.md](html-elements.md). The workbench `/forms` page shows fields, selects, choices and a form page. The layouts below were built on NewBanger's forms; their "Example" notes describe those screens. Tests: `tests/Feature/FormLayoutsTest.php`.

## Shared behaviour

| Behaviour | How |
|---|---|
| Elevation | Forms sit on raised `x-ui.panel`s over the tinted page: give the layout `surface="muted"` (for example a layout prop your shell passes through) or wrap in `x-ui.form-page`. Filter toolbars are the exception: flat on the white data page |
| Unsaved changes | `x-ui.form guard`. While the form is dirty, reloading or closing the tab shows the browser's own prompt (`beforeunload`), and a `wire:navigate` link opens the kit confirm "Leave without saving?" (Cancel keeps you, "Leave page" goes on). Dirty is mirrored as `data-dirty`. `dirty` keeps the form dirty from the start and after a successful submit, for values that live only in the page (the wizard between steps) |
| Error summary | `x-ui.error-summary :fields="[name => label]"`, shown when more than `min` (3) fields have errors; each item links to its field (`#field-…`) |
| Focus on the first error | Any `x-ui.form` submit (`resources/js/ui.js`): the error summary when it shows, otherwise the first `aria-invalid` field, scrolled clear of the top bar and the sticky actions. Success leaves focus on the submit button and a toast confirms |
| Client checks | `:user-invalid` borders and the `aria-invalid` fallback from `docs/components/html-elements.md` flag a malformed email or URL on blur, before the server runs |
| Choices | `x-ui.select` (custom listbox; no native `<select>` anywhere, `PopupPlacementTest` guards it), `x-ui.combobox` when the list needs typing to filter, `x-ui.radio-group` for up to five visible choices. The select's list opens below and is never clipped by a sheet or dialog. Workbench: `/forms` |
| Dates and times | `x-ui.date`, `x-ui.datetime`, `x-ui.time-input`, `x-ui.month`, `x-ui.week`, `x-ui.date-range`: custom pickers, never the browser's (see Dates and times below; `DatePickerTest` guards the views). Workbench: `/dates` |
| Mobile (390) | One column; side columns (summary rail, preview, section nav) move below or above the form; panel and footer buttons go full width, side by side (`max-sm:[&>*]:flex-1`); toolbars wrap |
| Ids | Do not put an `id` attribute on a component that wraps fields (`x-ui.form-section`, `x-ui.panel`): `@aware` hands it to the controls inside. Put anchor ids on a plain wrapping element |

## Layouts

| # | Layout | Use | Build it with |
|---|---|---|---|
| 1 | Settings | Account and admin settings: several short groups | `x-ui.form-section` (title and description in a 280px column, panel up to 640px), `x-ui.form-actions sticky` (shows while dirty) |
| 2 | Single column | One task with a few fields (request a payout) | `x-ui.form-layout.single` |
| 3 | Wizard | A long task with natural steps (create a campaign) | `x-ui.form-layout.wizard` |
| 4 | Split with summary | A purchase with a live total | `x-ui.form-layout.split variant="summary"` + an app summary rail (NewBanger: `x-pricing.summary-rail`) |
| 5 | Split with live preview | Something the user designs (a smart link) | `x-ui.form-layout.split variant="preview"` |
| 6 | Long form with section nav | Many sections on one page (curator profile) | `x-ui.form-layout.section-nav` + `x-ui.form-section`s |
| 7 | Inline / horizontal | Dense settings of short controls | `x-ui.form-layout.inline` + `x-ui.field inline` |
| 8 | Modal | A short edit of one record without leaving the page | `x-ui.dialog.form` ([the components index](components/README.md), Dialogs) |
| 9 | Side sheet | A form that needs the page behind it for context | `x-ui.sheet` with a `<form id>` and a footer submit (`form="…"`) |
| 10 | Filter toolbar | Narrowing a data page | Flat row of `size="sm"` controls; tables use `x-data-table` (`docs/components/data-table.md`) |

### `x-ui.form-layout.single`

| Prop or slot | Default | Description |
|---|---|---|
| `title` | null | Heading above the panel (heading role) |
| `description` | null | Meta line under it |
| `headingLevel` | 2 | Outline level of the heading |
| slot | | The fields (16px apart) |
| slot `footer` | | The panel action bar |

Wrap it in `x-ui.form`. Width: `max-w-[640px]`, centred.

### `x-ui.form-layout.wizard`

| Prop or slot | Default | Description |
|---|---|---|
| `steps` | [] | Step labels, e.g. Song, Targets, Details, Review, Pay |
| `current` | 1 | The current step (1-based) |
| `model` | `step` | The Livewire step property (for the announcement) |
| `flow` | null | Draft key: the `draftModel` array is kept in sessionStorage as `<prefix>draft_<flow>` and offered back once to `restoreDraft(array)` |
| `draftModel` | `wizard` | The Livewire array holding the flow's values |
| `title` | null | Names the step list (`<nav aria-label="… steps">`) |
| `backAction`, `nextAction`, `gotoAction` | back, next, goTo | Livewire methods |
| `finishLabel` | Continue | The last step's button ("Pay ₦68,000") |
| `guard` | true | Unsaved-changes guard |
| `dirty` | false | Keep the guard on after an accepted step |
| `errors` | [] | `x-ui.error-summary` fields for the current step |
| `stepper` | inline | The step list is `x-ui.stepper`: `inline` (the text list below) or `default` (its markers; they stack on narrow panels). See [workflow](workflow.md) |
| slot | | The current step's fields |
| slot `aside` | | A 360px column from lg (below the panel on phones) |

Header: "1 Song · 2 Targets · 3 Details · 4 Review · 5 Pay" as an `<ol>`; the current step ink at 500 with `aria-current="step"`, completed steps followed by "Done" (emerald-700) as buttons that go back to them, later steps ink-muted. Below `sm`: "Step 2 of 5". No bars, circles or fills. One step per screen; Continue submits the form, so the server validates that step only and the kit's focus rule applies. Back never clears values. After a step change, a polite region says "Step 2 of 5: Targets" and focus moves to the step heading (keyed per step, focused after the kit's submit settle). The component's `restoreDraft` must re-validate every value (the gallery's does: types, lengths, allowed ids, dates; `rights` is never restored).

Example (NewBanger): a full playlist campaign. Song (link checked by `TallUiKit\Support\Embed`, title, genre combobox, the embed facade), Targets (`x-marketplace.song-context`, `x-marketplace.playlist-card`s, an inline `x-marketplace.selection-tray` beside them), Details (date, email, pitch), Review (`x-ui.dl`, the pitch, a rights checkbox), Pay (`x-pricing.breakdown` from `CheckoutQuote::build`; no money moves).

### `x-ui.form-layout.split`

| Prop or slot | Default | Description |
|---|---|---|
| `variant` | summary | `summary` (360px column for an app summary rail (NewBanger: `x-pricing.summary-rail`), which is sticky from lg and a bottom bar below it) or `preview` (400px sticky column, a named region) |
| `previewLabel` | Preview | Region name for the preview |
| slot | | The form's panels |
| slot `aside` | | The rail or the preview |

Live preview: bind fields with deferred `wire:model`; `$wire` holds each keystroke in the browser, so the preview reads `$wire.linkTitle` and updates with no request. Saving validates on the server.

### `x-ui.form-layout.section-nav`

| Prop or slot | Default | Description |
|---|---|---|
| `sections` | [] | id => label |
| `label` | Sections | `<nav>` name |
| slot | | The sections (anchor ids on wrapping elements) |

A sticky 200px list from lg (`top-20`); the link of the section under a reading line 120px below the top bar (or the last section at the end of the page) has `aria-current="true"` and ink text, found with `IntersectionObserver` (no scroll listener). A click scrolls to the section and moves focus to its heading. Below lg the list is plain links above the form.

### `x-ui.form-layout.inline` and `x-ui.field inline`

`x-ui.field inline`: from `sm` the label and hint sit in a 220px column, the control and its error on the right, centred on the control; stacked on phones. Wiring (`for`, `id`, `aria-describedby`) is the same as a stacked field. `x-ui.form-layout.inline` divides the rows with 1px `line` rules inside a panel.

## Dates and times

Every date or time control is a text field plus a calendar (or clock) button and a popup below the field. No native `<input type="date|time|month|week|datetime-local">` anywhere: the browser's picker cannot be kept below its field, styled, or made to match the kit (`DatePickerTest` fails on one in any view). One engine, `x-ui.date-picker`, behind six components; behaviour in `resources/js/datepicker.js`, parsing and formats in `resources/js/date-core.js` (tested under `node --test`), server side in `TallUiKit\Support\DateInput`.

| Component | Value (what the server receives) | Field shows | Popup |
|---|---|---|---|
| `x-ui.date` | `2026-11-15` (Y-m-d) | 15 Nov 2026 | Month grid |
| `x-ui.datetime` | `2026-11-15T18:05` | 15 Nov 2026, 18:05 | Month grid, hour and minute columns beside it (below it on phones), Done |
| `x-ui.time-input` | `18:05` (H:i, also with `hours="12"`) | 18:05, or 6:05 pm | Hour and minute columns (and am/pm) |
| `x-ui.month` | `2026-11` | November 2026 | Year header, 12 months |
| `x-ui.week` | `2026-W47` (ISO week, Monday first) | Week 47, 2026 | Month grid with week numbers; a row is a week |
| `x-ui.date-range` | two Y-m-d dates | 1 Nov 2026 – 15 Nov 2026 | Preset buttons, then the month grid: first click the start, second the end, the days between preview on hover |

The public API of the five older components is unchanged: name, `wire:model` (any modifier) or `x-model`, `min`, `max`, `step`, `disabled`, `required`, `invalid`, `hint` and `error` through `x-ui.field`, and the value format. Extra props: `disabledDates` (a list of Y-m-d dates and `sat`, `sun`, `weekends`; or the name of a global JS function given a Y-m-d date that returns true to disable it), `weekStart` (0 to 6; default the visitor's locale, else Monday; `<html data-ui-week-start>` sets it for a whole app), `minuteStep` (5; or `step` in seconds as before), `hours` (24 or 12, display only), `defaultTime` (09:00, the time a datetime takes when only a day is chosen), `placeholder` (a format hint by default: DD/MM/YYYY), `defer`.

Range binding: two properties, `<x-ui.date-range wire:model.start.live="from" wire:model.end.live="to" />` (one request when the range is chosen, never one per end), or one array property `['start' => , 'end' => ]` with `wire:model`. `presets`: `true` (Last 7 days, Last 30 days, This month, Custom), a list of keys (`today`, `last7`, `last30`, `last90`, `thisMonth`, `lastMonth`, `thisYear`, `custom`), keys to labels, or `['label', 'start', 'end']` entries; `false` hides them. Preset dates are worked out on the server in the app timezone. Plain forms post `name[start]` and `name[end]` (or `startName`, `endName`).

Typing: the field takes text and parses it on Enter or blur, leniently: `15/11/2026`, `15-11-26`, `15 Nov 2026`, `Nov 15, 2026`, `2026-11-15`, `15 Nov` (this year), `today`, `tomorrow`; times `18:05`, `1805`, `6pm`, `6:30 pm`; months `11/2026`, `Nov 2026`; weeks `2026-W47`, `W47 2026`, or any date; ranges `start – end`, `start to end`. Day first; month first only when day first cannot be a date (`11/25/2026`). Text that is not a value, or a value outside `min` and `max` or disabled, leaves the value as it was, marks the field invalid and says why in a polite region ("Enter a date like 15 Nov 2026.", "Choose a date from 1 Jan 2026 to 31 Dec 2027."). Escape in the field puts the last value back.

Keyboard (the WAI-ARIA APG date picker dialog):

| Where | Keys |
|---|---|
| Field | Down (or Alt+Down) opens the popup; Enter commits typed text; Escape undoes typing |
| Day grid | Left/Right a day, Up/Down a week, PageUp/PageDown a month, Shift+PageUp/PageDown a year, Home/End the start and end of the week, Enter or Space choose, Escape closes and returns focus to what opened it |
| Month grid | Left/Right a month, Up/Down a row (3 months), PageUp/PageDown a year, Home/End the row |
| Week grid | Up/Down (Left/Right) a week, PageUp/PageDown a month, Shift a year, Home/End the month's first and last week |
| Month and year buttons | Open a custom listbox below them (never a native `<select>`): Up/Down, Home/End, PageUp/PageDown, Enter or Space choose, Escape returns to the button |
| Time columns | Up/Down move, Enter or Space choose (hour then minute; the last column closes), Left/Right switch column |
| Anywhere in the popup | Tab and Shift+Tab cycle inside it (a modal dialog); Escape closes; in a range with the start chosen, Escape drops the start first |

Semantics: the popup is `role="dialog"` named "Choose date"; the grid a `table role="grid"` labelled by a polite heading that announces the month as it changes; days are cells with `aria-selected`, `aria-disabled`, `aria-current="date"` for today and a full date as the name. Focus moves into the grid (the chosen day, else today, inside min and max) on open and back to the button after a choice. Look: 36px day cells; chosen white on `secondary`, range days ink on `surface-muted`, today underlined, disabled days `ink-muted` with a line through (never opacity); 1px borders; motion is the kit's popup entrance (150ms `ease-motion-out`, instant under reduced motion).

Placement: `x-anchor.bottom-start.noflip.offset.6.fixed` plus `$revealBelow`, so it opens below in tables, dialogs and sheets and is never clipped; near the end of a short page a spacer makes room instead of flipping. The popup is never wider than the screen less 16px (302px for a month, 450px with time columns, which drop below the grid when there is no room), so 390 and 320 have no sideways scroll. Cost: one small Alpine component per picker; the popup is built on first open and each month is one `innerHTML` (no Alpine per cell, events delegated). Put `defer` on pickers in long lists or wrap them in a `data-ui-defer` section.

### Modal and side sheet

`x-ui.dialog.form` (Enter submits; busy save; errors stay inside; closing with edits asks "Discard changes?"; returns `false` or a string to stay open) and `x-ui.sheet` (480px or 640px from the right, full width on phones; put a `<form id>` in the body and a `type="submit" form="…"` button in the footer). Both are documented in [the components index](components/README.md) under Dialogs and confirmations. Never put an `x-ui.panel` inside either.

### Filter toolbar

A `<form role="search">` row of `size="sm"` (32px) controls on the white page, applied as they change (no submit), with Clear when anything is set; it wraps on phones. For tables use `x-data-table`'s toolbar (search, up to three inline filters, a sheet for more, tokens, URL state).
