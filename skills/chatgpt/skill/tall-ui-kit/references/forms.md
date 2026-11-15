# Forms and form layouts

Companion to [the components index](components.md); field-level controls (`x-ui.field`, `x-ui.input`, `x-ui.select`, ...) are in its Components table and in [html-elements.md](html-elements.md). The workbench `/forms` page shows fields, selects, choices and a form page. The layouts below were built on NewBanger's forms; their "Example" notes describe those screens. Tests: `tests/Feature/FormLayoutsTest.php`.

## Shared behaviour

| Behaviour | How |
|---|---|
| Elevation | Forms sit on raised `x-ui.panel`s over the tinted page: give the layout `surface="muted"` (for example a layout prop your shell passes through) or wrap in `x-ui.form-page`. Filter toolbars are the exception: flat on the white data page |
| Unsaved changes | `x-ui.form guard`. While the form is dirty, reloading or closing the tab shows the browser's own prompt (`beforeunload`), and a `wire:navigate` link opens the kit confirm "Leave without saving?" (Cancel keeps you, "Leave page" goes on). Dirty is mirrored as `data-dirty`. `dirty` keeps the form dirty from the start and after a successful submit, for values that live only in the page (the wizard between steps) |
| Error summary | `x-ui.error-summary :fields="[name => label]"`, shown when more than `min` (3) fields have errors; each item links to its field (`#field-…`) |
| Focus on the first error | Any `x-ui.form` submit (`resources/js/ui.js`): the error summary when it shows, otherwise the first `aria-invalid` field, scrolled clear of the top bar and the sticky actions. Success leaves focus on the submit button and a toast confirms |
| Client checks | `:user-invalid` borders and the `aria-invalid` fallback from `docs/components/html-elements.md` flag a malformed email or URL on blur, before the server runs |
| Choices | `x-ui.select` (custom listbox; no native `<select>` anywhere, `PopupPlacementTest` guards it), `x-ui.combobox` when the list needs typing to filter, `x-ui.radio-group` for up to five visible choices. The select's list opens below and is never clipped by a sheet or dialog. Workbench: `/forms` |
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
| 8 | Modal | A short edit of one record without leaving the page | `x-ui.dialog.form` ([the components index](components.md), Dialogs) |
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

### Modal and side sheet

`x-ui.dialog.form` (Enter submits; busy save; errors stay inside; closing with edits asks "Discard changes?"; returns `false` or a string to stay open) and `x-ui.sheet` (480px or 640px from the right, full width on phones; put a `<form id>` in the body and a `type="submit" form="…"` button in the footer). Both are documented in [the components index](components.md) under Dialogs and confirmations. Never put an `x-ui.panel` inside either.

### Filter toolbar

A `<form role="search">` row of `size="sm"` (32px) controls on the white page, applied as they change (no submit), with Clear when anything is set; it wraps on phones. For tables use `x-data-table`'s toolbar (search, up to three inline filters, a sheet for more, tokens, URL state).
