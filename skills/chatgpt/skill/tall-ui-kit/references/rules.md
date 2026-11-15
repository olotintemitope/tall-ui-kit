# Rules

These rules are the kit. A page built from kit components that breaks one of them is still wrong. Check every new screen against this page before showing it.

## The quick list

1. **No AI look.** Banned: thick or coloured edge borders (accent bars on alerts, toasts, cards or tabs), icon-in-a-tile empty states, centred hero empty states, coloured icon badges, `rounded-2xl` or larger, heavy, stacked or coloured shadows, glows, glassmorphism or backdrop blur, gradients, KPI card grids with icons and sparklines, donut, pie, gauge and progress-ring charts, gradient fills under lines, delta chips with arrows in tinted pills, leaderboard medals, filler copy ("Glad to see you again", "Insights", "at a glance"), emoji and em dashes in user-facing copy. Wanted: dense, flat, professional (Stripe, Linear, Vercel), hierarchy from the `type-*` roles, no unnecessary space, full-width data pages.
2. **Borders are 1px.** The 2px focus outline is the only thicker line.
3. **Focus is always visible and never lost.** `focus-ring` (2px `focus` outline, 2px offset) on every control, `:focus-visible` only; focus never falls to `<body>` (see [accessibility.md](accessibility.md)).
4. **Contrast: 4.5:1 for text, 3:1 for boundaries and marks, in every state, no exemptions.** Disabled controls never fade with opacity.
5. **Every dropdown is custom.** Never a native `<select>`: use `x-ui.select` (or `x-ui.combobox` to filter by typing). Never a native date or time input either (`type="date|time|month|week|datetime-local"`): use `x-ui.date`, `x-ui.datetime`, `x-ui.time-input`, `x-ui.month`, `x-ui.week` or `x-ui.date-range`.
6. **Popups open below their trigger, never flipped above.** `x-ui.select`, `x-ui.dropdown`, `x-ui.combobox`, `x-ui.popover` and the date pickers use `x-anchor.bottom-*.noflip` plus `$revealBelow` (the page scrolls to make room). Menus size to their longest label. Only tooltips may flip.
7. **Tables never push the page sideways** at any width (check 1440, 1024, 768, 390 and 320). List pages use `x-data-table` with the `stack` layout (cards below 640px, and below 1024px when the columns do not fit); a plain `x-ui.table` scrolls inside its own focusable region.
8. **Toasts sit above modals.** `x-ui.toaster` is placed once; while a modal `<dialog>` is open its node moves into the top-most dialog so toasts stay visible and clickable (`resources/js/toaster-layer.js`). Toasts never take focus.
9. **Text is never cut off.** No `truncate`, ellipsis or line clamp; things wrap and grow (rules below).
10. **Pills are one line**, sized to the label, never wrapped or clipped.
11. **Only tokens and type roles.** No raw hex, `gray-*` or arbitrary values in views.

## App guidance (not enforced by the kit)

- **Short references for people, UUIDs for URLs.** Show records by a short public reference (`TXN-9F3C21AB`, mono role, copyable with `x-ui.copy-text`), and build every web URL and query string from the model's public `uuid`, never its numeric id. Bind web routes explicitly (`{campaign:uuid}`) and build links with `route('name', $model)`. `WithDataTable::tableReferenceSearch()` is the hook for looking rows up by reference.
- Shared logic: Livewire components call the same services as any API.
- Performance budgets: see [performance.md](performance.md).

## Detailed rules

- One accent (`primary`; `#FF614D` in the NewBanger theme, a muted slate in the neutral theme). Everything else is a neutral grey (`secondary`/`ink` and the ramp below it).
- Data pages are flat: white background, 1px `line` borders and rules, no shadows.
- Every border is 1px on every side. No thick or coloured edges (`border-2`, `border-l-4`, `border-[3px]`, `divide-y-2`, inset-shadow edges). The 2px focus outline is the one exception: an outline shown only on focus, not a border.
- Form contexts are elevated: tinted page (`page-muted`) with white `x-ui.panel`s. A panel never sits on plain white and never inside a dialog. Filter toolbars on data pages stay flat.
- Pills (`rounded-full`) are for status badges only, and a pill is **one line, sized to its label, never wrapped or clipped** (`whitespace-nowrap shrink-0`, no `max-width`). Controls use 6px radii (`rounded-md`), menus, panels and dialogs 8px (`rounded-lg`).
- Hierarchy comes from size, weight and colour, never from boxes, colour fills or icons.
- Banned: icon-in-tile empty states, gradients, glows, glassmorphism or backdrop blur, `rounded-2xl` or larger, heavy or stacked shadows, coloured shadows, emoji, coloured icon badges, filler or marketing copy, em dashes in user-facing copy.
- Contrast: text 4.5:1 minimum everywhere (placeholders included); meaningful non-text UI 3:1. Every bordered control (inputs, checkboxes, radios, switches, drop areas, outline buttons, segmented tracks) is edged in `control` or darker; the primary button's fill is 2.97:1, so it is edged in `primary-hover` (3.70:1). Ghost and link buttons are text controls identified by their label. Nothing is exempt: disabled controls never fade with opacity; they keep `ink-muted` text (5.42:1 on their `surface-subtle` fill), a dashed `control` border (3.38:1) and `cursor-not-allowed` (see [States](tokens.md)). Every pair in use is listed in `TallUiKit\Support\KitContrast` (and `SidebarContrast`), measured for every theme by `composer contrast` and `tests/Feature/UiKitContrastTest.php`.
- **Text is never cut off.** No text or component is truncated, clipped or hidden by overflow; it wraps.
  - No `truncate`, `text-ellipsis`, `line-clamp-*`, and no `overflow-hidden` on a text container that could clip its text. `x-ui.text` has no one-line or clamp option.
  - No fixed heights on anything that holds text: buttons, menu items, sidebar rows and table rows use minimum heights (`min-h-9`, a `td` height) and grow.
  - Long unbroken strings (emails, URLs, references, ids, long Nigerian names) break: `wrap-anywhere` (`overflow-wrap: anywhere`) in flex and block text, `wrap-break-word` in table cells (it keeps a column's minimum width, so a very long value widens the table, which scrolls in its own box, instead of squeezing every column).
  - `whitespace-nowrap` only for short fixed-width tokens (amounts, dates, status pills, kbd, one-word buttons) inside a container that can grow or scroll; never where it would clip. Selects are as wide as their longest option (`x-ui.select` `width="auto"`, the default), and a chosen label that does not fit wraps inside the trigger.
  - Tables: cells wrap and rows grow; prefer fewer visible columns or the stacked layout; the sideways scroll box is the fallback for many columns, never an ellipsis.
  - Never clipped: focus rings (use padding or `focus-ring-inset`), icons, badges, and dropdowns, tooltips and popovers (they use `fixed` anchoring or the top layer). Sticky bars never cover focused or read text (`scroll-padding`).
  - Images may crop (`object-cover`); their alt text and captions never are.
  - Guard: `tests/Feature/NoCutOffTest.php` rejects `truncate`, `text-ellipsis` and `line-clamp-` in the kit's views; apps should add the same guard for theirs. Browser check: the overflow audit in [accessibility.md](accessibility.md).
- Only tokens: no hex, `gray-*`, faded `secondary/NN`, arbitrary shadows or radii, and no `font-heading` outside the type roles. Apps should guard their own views with a test that greps for these (NewBanger's `AdminComponentsTest` does, with a short documented allowlist).
