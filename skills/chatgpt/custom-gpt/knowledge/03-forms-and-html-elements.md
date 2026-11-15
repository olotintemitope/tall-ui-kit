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

Header: "1 Song · 2 Targets · 3 Details · 4 Review · 5 Pay" as an `<ol>`; the current step ink at 500 with `aria-current="step"`, completed steps followed by "Done" (success-text) as buttons that go back to them, later steps ink-muted. Below `sm`: "Step 2 of 5". No bars, circles or fills. One step per screen; Continue submits the form, so the server validates that step only and the kit's focus rule applies. Back never clears values. After a step change, a polite region says "Step 2 of 5: Targets" and focus moves to the step heading (keyed per step, focused after the kit's submit settle). The component's `restoreDraft` must re-validate every value (the gallery's does: types, lengths, allowed ids, dates; `rights` is never restored).

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

`x-ui.dialog.form` (Enter submits; busy save; errors stay inside; closing with edits asks "Discard changes?"; returns `false` or a string to stay open) and `x-ui.sheet` (480px or 640px from the right, full width on phones; put a `<form id>` in the body and a `type="submit" form="…"` button in the footer). Both are documented in [the components index](components.md) under Dialogs and confirmations. Never put an `x-ui.panel` inside either.

### Filter toolbar

A `<form role="search">` row of `size="sm"` (32px) controls on the white page, applied as they change (no submit), with Clear when anything is set; it wraps on phones. For tables use `x-data-table`'s toolbar (search, up to three inline filters, a sheet for more, tokens, URL state).
# UI kit: HTML5 and media elements

Companion to [the components index](components.md). Native elements first, styled with kit tokens; behaviour that HTML cannot give lives in `resources/js/forms.js` (imported by `resources/js/ui.js`). Gallery: the workbench gallery, sections "Native inputs", "Disclosure, popover, progress and meter", "Text and structure" and "Media", each with a properties table. Tests: `tests/Feature/HtmlElementsTest.php`, `tests/Unit/EmbedTest.php`.

Every control is `h-9` (36px), or `h-8` with `size="sm"`, has a 1px `control` border (3.65:1), the 2px focus treatment, and the kit's disabled look (dashed `control` border on `surface-subtle`, `ink-muted` text, never opacity; `tests/Feature/AdminComponentsTest.php` rejects `opacity-` behind any disabled variant).

## Browser defaults removed (`tall-ui-kit.css`, base layer)

| Default | Kit |
|---|---|
| Autofill yellow | `:autofill` and `:-webkit-autofill` (separate rules) paint white with an inset shadow; text and caret ink; the border stays the control's |
| Number spin buttons | hidden (`::-webkit-inner-spin-button`, `appearance: textfield`); use `x-ui.number-input` |
| Search cancel button | hidden; `x-ui.search` has its own clear button |
| Date and time picker icon | the WebKit indicator is a Lucide calendar or clock mask in `ink-muted` (5.86:1); Firefox and Safari keep their own button |
| UA accent blue | `accent-color: ink` on `:root` |
| Selection | ink on `primary` at 25% (12.05:1) |

## States

| State | How |
|---|---|
| Client invalid | `:user-invalid` on the control turns its `[data-control-box]` border `danger`, only after the user edits or submits. Fallback for every browser and for screen readers: on blur, a kit control that fails its own constraints (`type=email`, `url`, `pattern`, `min`/`max`) gets `aria-invalid="true"` (`data-client-invalid`), removed once valid. Server errors (`aria-invalid` from `$errors`) are never removed by it |
| Read-only | `[data-control-box]` with a read-only kit input: `panel-footer` fill, default cursor, border unchanged. `x-ui.copy-field` stays white |
| Print | data pages print without the sidebar, top bar, progress bar, toasts or anything with `data-print-hide`; links in `main` print their address after the text (`data-print-no-url` opts out); table headers repeat; panels lose their shadow; privacy-mode money stays blurred |

## Inputs

| Component | Props | Notes |
|---|---|---|
| `x-ui.input type="email\|url\|tel\|search\|number"` | as `x-ui.input` | Adds `inputmode`, `autocomplete`, `autocapitalize="none"` and `spellcheck="false"` per type; any attribute passed wins |
| `x-ui.tel` | `country` ("+234"), `size`, `width`, `invalid` | The code sits inside the border as a prefix and is read as a description ("Country code +234"); `autocomplete` becomes `tel-national` |
| `x-ui.password` | `autocomplete` (current-password), `strength` (false), `size`, `width` (full) | Show/hide is a button with `aria-pressed` and the fixed name "Show password". `strength` adds a polite caption: "Too short", "Weak", "Fair", "Strong password." Text only, never blocks submit. Promoted from `x-auth.password-input`, which is now an alias |
| `x-ui.otp` | `length` (6), `label`, `id`, `error` | Fieldset and legend; one string through `x-modelable`; paste and one-time-code autofill spread across the boxes; `otp-complete` event. Promoted from `x-auth.otp-input` (alias). The auth pages use the kit names |
| `x-ui.number-input` | `min`, `max`, `step` (1), `format` (number, money, percent), `currency`, `decimals`, `noun`, `size`, `width`, `invalid`, `label`, `value` | `role="spinbutton"` on a text input (so money can be grouped), two 32px buttons inside the border, each divided by a 1px `control` rule. Up/Down step, Page Up/Down x10, Home/End to the bounds; the buttons are `tabindex="-1"` (the keyboard steps on the field) and `aria-disabled` at a bound. Plain digits while editing, `₦150,000` after blur; clamped and rounded to `step`. Bind on the component |
| `x-ui.range` | `label`, `min`, `max`, `step`, `steps`, `dual`, `format`, `currency`, `unit`, `openEnded`, `value`, `minModel`, `maxModel`, `disabled`, `hideLabel`, `id` | Native ranges. Track 4px `control` (3.65:1), chosen part ink, thumb 24px (the target) with a 1px `control` edge and `shadow-thumb`; focus ring on the thumb. `<output>` shows the value; each thumb has `aria-valuetext`. `x-marketplace.range` is now this component with Livewire models |
| `x-ui.color` | `value`, `size`, `width`, `label`, `swatchLabel` | Native swatch (28px, opens the system picker) and a hex field; #RGB expands; a bad hex is `aria-invalid` until fixed |
| `x-ui.date`, `x-ui.time-input`, `x-ui.datetime`, `x-ui.month`, `x-ui.week`, `x-ui.date-range` | `size`, `width`, `invalid`, `min`, `max`, `step`, `disabledDates`, `weekStart`, `hours`, `minuteStep`, `presets` (range) | Custom pickers, not native inputs: a typed text field plus a popup below it, the same in every browser. Values as the native inputs posted (Y-m-d, H:i, Y-m, 2026-W47, Y-m-d\TH:i). See [forms.md](forms.md), Dates and times |
| `x-ui.combobox` | `options` (value => label, or `value`, `label`, `hint`, `disabled`), `value`, `placeholder`, `allowCustom`, `empty`, `native`, `size`, `width`, `invalid`, `label` | ARIA 1.2 combobox: `role="combobox"` input, `aria-expanded`, `aria-controls`, `aria-activedescendant`; `role="listbox"` popup named by the field label. Type-ahead filter with a polite count; Down/Up, Alt+Down, Enter, Escape (twice clears), Tab keeps the choice. `native` renders `<input list>` and `<datalist>` for free text with suggestions where the browser's unstyled popup is acceptable |
| `x-ui.checkbox indeterminate` | | The native mixed state (announced as "mixed"), an ink box with a white dash; bind `x-effect="$el.indeterminate = ..."` for a live value |
| `x-ui.fieldset` | `legend`, `description`, `size` (field, subheading), `hideLegend`, `disabled`, `id` | No box: the legend and 16px spacing group the controls; the description is `aria-describedby` |
| `x-ui.output` | `for`, `label`, `size` (default, figure), `name` | A computed value, polite |
| `x-ui.select multiple` | `multiple`, `rows` (kept, unused) | A custom multi-select listbox with checkmarks (see docs/, Select); a string key whose value is an array is a labelled group; `selected` may be a list; one hidden input per value |
| `x-ui.file variant="images"` | `variant` (list, images) | Thumbnails (square, 8px radius, file name as alt) in a 3 or 4 column grid with a 24px Remove button that rebuilds the input's files and fires `change` (so `wire:model` uploads follow); focus goes to the next Remove, else the input |

## Interactive

| Component | Props | Notes |
|---|---|---|
| `x-ui.disclosure` | `summary`, `open`, `meta`, `bordered`, `name` | `<details>`/`<summary>`: works without JavaScript, find-in-page opens it. With Alpine the summary click is intercepted so `x-collapse` animates the height (150ms; instant under reduced motion) and the chevron turns 90°. Focus stays on the summary |
| `x-ui.accordion`, `.item` | `exclusive`, `name`, `label`; item `summary`, `open`, `meta` | Items divided by 1px `line` rules. `exclusive` closes the others (give each exclusive accordion a unique `name`) |
| `x-ui.popover` | `label`, `title`, `placement`, `width`; slot `trigger` | See the decision below |
| `x-ui.progress` | `label`, `value`, `max`, `valueText`, `hideLabel`, `id`; `size`, `tone`, `labelPosition`, `format`, `buffer` ([workflow](workflow.md) "Progress") | Native `<progress>` for the semantics, drawn as a `surface-muted` track (4, 8 or 12px) with a fill that scales to the value (`chart-1` 15.8:1, 13.6:1 on the track; or a status tone), label and percent. No `value`: indeterminate, a 30% segment slides (transform only, no stripes); reduced motion holds it and pulses. Not the page-level `x-ui.progress-bar` |
| `x-ui.meter` | `label`, `value`, `min`, `max`, `low`, `high`, `optimum`, `format`, `valueText`, `words` | Native `<meter>`; the browser picks the bar: optimum `success-text`, next `warning-text`, far `danger-text` (5.36, 5.03, 6.42 on white; 4.62, 4.34, 5.53 on the track). The value and "Good", "Fair" or "Poor" are printed, so colour is never the only cue |

### Decision: Popover API

`x-ui.popover` uses the Popover API (`popover="auto"` and `popovertarget` on the trigger), in every browser we support since spring 2024 (Chrome 114, Safari 17, Firefox 125). It gives light dismiss, Escape, the top layer (never clipped by an `overflow` parent) and focus return for free; Alpine only anchors it (`x-anchor`, since CSS anchor positioning is not yet everywhere) and mirrors `aria-expanded`. Where the API is missing, `uiPopover` drops the attribute and shows the same panel with Alpine, handling Escape and a click outside. It fades with `@starting-style` and `allow-discrete`, like dialogs.

Kept on Alpine: `x-ui.dropdown` (menus need roving focus, typeahead and submenus, and a menu is not a popover role) and `x-ui.tooltip` (hover and focus hints: `popover="hint"` is Chromium only, and a tooltip never takes focus). Revisit both when `popover="hint"` and `interestfor` ship in Safari and Firefox.

## Text and structure

Styled once in the base layer, so they look right anywhere (and inside `x-ui.prose`): `mark` (ink on `primary` 18%, 13.03:1), `abbr[title]` (dotted `ink-muted` underline, help cursor), `small` (caption size), `sub`, `sup`, `data` (tabular), `address`, `cite` (ink, 500), `q` (curly quotes), `samp` and `var` (mono, ink), `bdi` (isolates right-to-left names), `wbr`. No italics anywhere (`cite`, `var`, `address`, `dfn`, `em` are upright).

| Component | Props | Notes |
|---|---|---|
| `x-ui.time` | `value`, `mode` (absolute, relative), `withTime`, `format`, `tz` (Africa/Lagos), `now` | Always `<time datetime="ISO 8601">`. Absolute "14 Nov 2026" (with time "14 Nov 2026, 10:00"); relative "Just now", "5m ago", "2h ago", "Yesterday", "3 days ago", "in 2h", "Tomorrow" (`Format::relative`), the full date in `title`. Null prints an en dash. Use it wherever a date renders; the notification item already prints a `<time>` the same way |
| `x-ui.dl`, `x-ui.dl.item` | `items`, `layout` (stacked, inline, grid), `dense`; item `label`, `numeric` | Labels in meta, values in body or numeric |
| `x-ui.breadcrumb` | `items` (`label`, `href`; the last is current), `label`, `collapse` | `<nav aria-label>` and `<ol>`; current page `aria-current="page"`; separators hidden from assistive tech; below `sm` only the parent and current page |
| `x-ui.table` | adds `captionVisible`, `sticky`, `maxHeight`; slots `colgroup`, `foot` | Visible caption in the label role; `<colgroup>`; `<tfoot>` totals over a 1px rule; the header row sticks inside the table's own scroll box (`max-h-[32rem]` by default: a sideways scroller cannot also stick to the page) |
| `x-ui.table.th` | adds `row`, `scope` | `row`: a body or foot row header (`scope="row"`, body-strong). `scope` overrides (colgroup, rowgroup) |

## Media

| Component | Props | Notes |
|---|---|---|
| `x-ui.audio` | `src`, `title`, `artist`, `duration`, `type`, `compact` | Song previews. `preload="none"`. 32px play/pause toggle (`aria-pressed`, "Play Second Wind"), title and artist, a seek bar on the kit range (`aria-valuetext` "0:12 of 0:30"), elapsed / total in numeric, mute toggle. One player at a time (`ui-audio-play` window event). Space or Enter on play toggles; Space on the seek bar too; Left/Right on play or the bar move 5s. Loading: the kit spinner in the button and `aria-busy`. Error: "Preview unavailable." and the button `aria-disabled`. No waveform |
| `x-ui.video` | `src`, `poster`, `type`, `aspect`, `title`, `preload`; slot `<track>` | Native controls, fixed aspect box (no shift), 8px radius, never autoplays |
| `x-ui.embed` | `url`, `title`, `artwork`, `autoload` | Click-to-load facade (nothing from the provider loads, no cookies, until "Load Spotify player"); the iframe then takes the facade's exact size and focus, with `title`, `loading="lazy"`, `referrerpolicy`, the provider's `allow` list and a `sandbox`. Links with no player render "Open on Audiomack" (new tab) |
| `x-ui.lightbox` | `name`, `src`, `thumb`, `alt`, `title`, `description`, `width`, `height`, `size` (96), `fullUrl` | A thumbnail button ("View <alt>", `object-cover`, lazy) that opens the full image in an `x-ui.dialog` (xl): `x-ui.figure` with fit contain at the image's aspect, capped to 55vh, "Open full size" and Close. Promoted from the support ticket attachments. `data-*` attributes go on the thumbnail button |
| `x-ui.figure` | `src`, `alt`, `decorative`, `caption`, `aspect`, `srcset`, `sizes`, `sources`, `width`, `height`, `eager`, `fit` | `<figure>` with `<picture>`: `sources` for WebP or AVIF first, `srcset`/`sizes` on the img; fixed aspect; `loading="lazy"` and `decoding="async"` (`eager` sets `fetchpriority="high"`). `alt` is required; empty only with `decorative` (throws in local and testing) |
| Prices | | Not in the kit: prices are app components (NewBanger: `x-pricing.price`); there is no `x-ui.price` |

### Embed providers (`TallUiKit\Support\Embed`)

Ported from the React resolvers (`src/components/ui/TrackEmbed.tsx`, `src/components/admin/SocialVideoEmbed.tsx`). The host is parsed and must be on `Embed::HOSTS` (exact host; no user info or port), so a URL that only mentions a provider in its path or query never produces an iframe.

| Provider | Hosts | Player |
|---|---|---|
| Spotify | `open.spotify.com` | `/embed/{track,album,playlist,episode,show,artist}/{id}`; 152px, 352px for collections |
| YouTube | `youtube.com`, `www.`, `m.`, `music.`, `youtu.be` | `youtube-nocookie.com/embed/{id}` from watch, short links, shorts, live; 16:9 |
| Apple Music | `music.apple.com` | `embed.music.apple.com` with the same path (only `?i=` kept); 175px song, 450px collection |
| SoundCloud | `soundcloud.com`, `www.`, `m.` | `w.soundcloud.com/player/?url=` the canonical track or set; 166px |
| TikTok | `tiktok.com`, `www.`, `m.`, `vm.` | `tiktok.com/embed/v2/{id}` from `/@user/video/{id}`; 9:16. `vm.` short links carry no id: link only |
| Audiomack | `audiomack.com`, `www.` | Link only. React links out too, and no official embed URL format could be verified |
| Boomplay | `boomplay.com`, `www.` | Link only, as React |

Instagram (also in `SocialVideoEmbed.tsx`) is not ported: no gallery or page needs it yet.

Sample media for the gallery: `public/samples` (a 12s tone, a 4s clip with captions, flat cover images), generated locally.
