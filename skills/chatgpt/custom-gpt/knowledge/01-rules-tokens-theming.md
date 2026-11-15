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
11. **Only tokens and type roles.** No raw hex, `gray-*` or arbitrary values in views, and no `bg-white`, `text-white`, `bg-black/NN` or Tailwind status colours (`red-*`, `emerald-*`, `amber-*`, `blue-*`): use `surface`, `card`, `popover`, `on-secondary`, `ink/NN` and the status tokens, so dark mode works. The dark sidebar's own white tones are the one exception.

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
# Tokens, type roles and states

Values below are the NewBanger theme in light; the neutral theme uses the same token names with its own values, and both themes give every token a dark value (see [theming.md](theming.md), Dark mode, and the table at the end of this section). `composer contrast` prints every measured pair for both themes in both schemes.

## Colour tokens

Ratios are against white (`surface` in light) unless stated.

| Token | Value | Use | Contrast |
|---|---|---|---|
| `surface` / `card` / `popover` | `#FFFFFF` | The page, panels, popovers and dialogs. Never `bg-white`: these turn dark in the dark scheme | |
| `ink` / `secondary` | `#212322` | Titles, strong text, checked controls (`on-secondary` white on it) | 15.9:1 |
| `ink-body` | `#373938` | Body text | 11.64:1 |
| `ink-muted` | `#646564` | Meta, captions, labels, placeholders, inactive nav | 5.86:1 (5.46:1 on `#F6F7F9`, 5.47:1 on `page-muted`) |
| `primary` | `#FF614D` | Accent fill; carries `on-primary` text (`ink` in light) | ink on it 5.32:1 (white would be 2.97:1); fill 2.97:1 on white |
| `primary-hover` | `#E5543F` | Primary button border (its 3:1 boundary) | 3.70:1 |
| `primary-fill-hover` | `#FF7462` | Hovered primary button fill | ink on it 5.97:1 |
| `primary-text` | `#BC4530` | Accent text, links. Any orange text uses this, never `primary` | 5.21:1 |
| `control` | `#858685` | Borders of inputs, checkboxes, radios, switches, drop areas, outline buttons, the segmented track and its checked segment | 3.65:1 (3.38:1 on `surface-subtle`) |
| `control-hover` | `#6F706F` | Hovered control border; active item ring on light sidebars | 4.97:1 |
| `focus` | `#DB4C37` | Focus outline and focused input border | 4.12:1 (3.84:1 on `#212322`) |
| `whatsapp` / `whatsapp-hover` | `#008069` / `#00715C` | The floating WhatsApp support button on marketing pages (white label and icon). WhatsApp's `#25D366` carries white at 1.98:1, so it is not used | white on it 4.89:1 (hover 5.97:1); fill 4.89:1 on white, 3.84:1 on the footer |
| `line` | `#E5E6E5` | Dividers, table rules, card borders (decorative) | |
| `line-strong` | `#D2D3D2` | Kbd and outline tag edges (not interactive). Never a control border | |
| `surface-subtle` | `#F6F6F6` | Hover fills | |
| `surface-muted` | `#EEEEEE` | Current page in pagination, avatar fill | |
| `page-muted` | `#F7F7F8` | Level 0 behind form panels | |
| `panel-border` | `#E7E7E9` | Panel, popover and dialog borders | |
| `panel-footer` | `#FAFAFA` | Panel action bar, table header, row hover (`hover:bg-panel-footer`), light sidebars, auth page background | |
| `scrim` | `#212322` at 40% | Behind dialogs, the command palette and the mobile drawer (`bg-scrim`) | |
| `danger-text` | `#C10007` | Field errors, late counts, inline load errors, falling deltas | 6.42:1 |
| `success-text` | `#007A55` | Rising deltas, inflows, footnote lead-ins | 5.36:1 |
| `danger` / `on-danger` | `#E7000B` / white | Destructive button, invalid field edge | 4.77:1 |
| Status tones | `{danger,success,warning,info}-{text,strong,tint,edge}` | Alerts, toasts, banners, badges, progress tones (see [theming.md](theming.md)) | below |

Text links: `x-ui.link-arrow` (ink, accent text on hover) or `x-ui.button variant="link"` (`primary-text`). Orange text is always `primary-text`; `#FF614D` text is 2.97:1 and fails. The primary button is an orange fill with `ink` text.

### Chart tokens

Defined in a `@theme static` block so they always reach the stylesheet: chart lines, bars, swatches and segment bars all use `var(--color-chart-*)` (the hover script too). Series pass the token name (`'color' => 'chart-1'`); anything else falls back to `chart-1`.

| Token | Value | Use | Contrast |
|---|---|---|---|
| `chart-accent` | `#BC4530` | Money series (revenue) | 5.21:1 |
| `chart-1` | `#212322` | First series; bar hover | 15.9:1 |
| `chart-2` | `#646564` | Second series; ranked share bars | 5.86:1 |
| `chart-3` | `#909190` | Third series | 3.16:1 |
| `chart-grid` | `#F0F1F1` | Gridlines (decorative) | |
| `chart-axis` | `#E5E6E5` | Axis line, crosshair, tooltip border (same as `line`) | |
| `chart-tick` | `#646564` | Tick and tooltip date text (same as `ink-muted`, caption and meta sizes) | 5.86:1 |

See [Charts](data-display.md) for the marks. Hand-checked contrast against white: `chart-accent` 5.21:1, `chart-1` 15.81:1, `chart-2` 5.85:1, `chart-3` 3.16:1. On the `surface-muted` track of horizontal bars `chart-2` is 5.05:1 but `chart-3` only 2.73:1, so horizontal bars use `chart-1` or `chart-2`. Stacked segments sit on white gaps, so each is measured against white.

Dark admin sidebar (`sidebar`, `#212322`; stays dark in the dark scheme): items `white/70` (8.40:1; 7.35:1 on hover), section labels and counts `white/65` (7.44:1), icons `white/60` (6.56:1), active item `white` on `white/10` (11.63:1) with a `white/40` inset ring (3.72:1 against the sidebar). Light sidebars (`panel-footer`): items `ink-muted` (5.61:1), active `ink` on `ink/6` (13.26:1) with a `control-hover` inset ring (4.17:1 on the active fill, 4.76:1 on the sidebar).

Status badges (text on tint): success `success-strong` 7.23, warning `warning-text` 6.84, danger `danger-text` 5.87, info `info-text` 6.28, neutral ink/`surface-muted` 13.63, accent `#BC4530` on `primary/10` 4.67. Alerts and toasts: `danger-strong` on `danger-tint` 7.64, warning 8.73, success 7.23, ink on `panel-footer` 15.15.

### Dark values

The same names in the dark scheme (neutral / NewBanger). Every pair is measured again; the lowest text pair is 4.66:1 and the lowest non-text 3.21 / 3.04:1 (`composer contrast`).

| Token | Neutral dark | NewBanger dark |
|---|---|---|
| `surface` (page) / `card` / `popover` | `#111213` / `#18191B` / `#1C1D20` | `#121312` / `#191A19` / `#1D1E1D` |
| `page-muted` / `panel-footer` / `sidebar` | `#0C0D0E` / `#16171A` / `#17181A` | `#0D0E0D` / `#171817` / `#181918` |
| `ink` / `ink-body` / `ink-muted` | `#ECEDEE` / `#CFD0D2` / `#A0A2A6` | `#EDEEED` / `#D0D1D0` / `#A2A3A2` |
| `line` / `line-strong` / `panel-border` | `#27282B` / `#34363A` / `#2A2B2E` | `#282928` / `#353735` / `#2B2C2B` |
| `surface-subtle` / `surface-muted` / `skeleton` | `#1F2023` / `#28292C` / `#232427` | `#202120` / `#292A29` / `#242524` |
| `control` / `control-hover` / `focus` | `#6F7276` / `#8A8D91` / `#6383A1` | `#707270` / `#8B8D8B` / `#E0563F` |
| `primary` / `primary-text` / `on-primary` | `#AFC0D1` / `#A9BED3` / `#111213` | `#FF614D` / `#FF614D` / `#121312` |
| `secondary` / `on-secondary` | `#E6E7E8` / `#111213` | `#E6E7E6` / `#121312` |
| `danger` / `danger-text` / `danger-tint` | `#D9343B` / `#FF8D8A` / `#2A1517` | same |
| `success-text` / `warning-text` / `info-text` | `#4CC38A` / `#E9A23B` / `#79A6FF` | same |
| `chart-accent` / `chart-1` / `chart-2` / `chart-3` | `#8FAACB` / `#D5D7DA` / `#A0A2A6` / `#75787C` | `#FF614D` / `#D6D7D6` / `#A2A3A2` / `#767876` |
| `scrim` | black at 60% | black at 60% |

## Type roles

Use the `type-*` utility (font, size, line-height, weight, colour). A colour utility after it (`type-body text-white/70`) overrides the role colour, which is how the dark sidebar and tones are written. Kit form controls may still pair `font-body` with a bare `text-<role>` size where the role's colour does not apply. Weights: 400, 500, 600 only. Nothing under 11px; 11px is for `label` only.

| Utility | Font | Size / line | Weight | Colour | Use |
|---|---|---|---|---|---|
| `type-title` | Space Grotesk | 22/28, -0.01em | 600 | ink | Page h1 |
| `type-heading` | Inter | 15/22 | 600 | ink | Section, card, dialog titles |
| `type-subheading` | Inter | 14/20 | 600 | ink | Sub-sections inside a card or section, h3 in prose |
| `type-label` | Inter | 11/16, uppercase, 0.08em | 600 | ink-muted | Section labels, table headers |
| `type-body` | Inter | 14/20 | 400 | ink-body | Running text, cells |
| `type-body-strong` | Inter | 14/20 | 500 | ink | Names, primary cell text, option labels |
| `type-field-label` | Inter | 13/18 | 500 | ink | Form field labels |
| `type-meta` | Inter | 13/18 | 400 | ink-muted | Secondary lines, timestamps, descriptions |
| `type-caption` | Inter | 12/16 | 400 | ink-muted | Hints, errors, footnotes |
| `type-figure` | Space Grotesk | 22/26, tabular | 600 | ink | Stat values |
| `type-numeric` | system mono | 13/18, tabular | 400 | inherit | Amounts, ids, counts |

## Spacing, radii, elevation, focus

- 4px grid. Label to control 6px; control to hint or error 4px; stacked fields 16px; field-row column gap 12px; between sections or panels 24px; page header to content 16px; panel padding 16px (20px from `sm`).
- Controls `h-9` (36px), `sm` `h-8` for dense toolbars. Radii: checkboxes and segments inside a segmented track 4px (`rounded-sm`), controls 6px (`rounded-md`), panels, menus and dialogs 8px (`rounded-lg`), badges full.
- Elevation: 0 `bg-page-muted`; 1 `shadow-panel` + `border-panel-border` (`x-ui.panel`); 2 `shadow-popover` + border (dropdowns, toasts); 3 `shadow-dialog` + border (dialogs, command palette). Two component shadows: `shadow-thumb` (switch thumb) and `shadow-bar` (top edge of the sticky `x-ui.form-actions`).
- Focus: `focus-ring` utility, a 2px `focus` outline with 2px offset. Inputs: border turns `focus` plus a 2px `primary/20` ring.
- Motion: see Motion below. Colour changes 100ms; enter and move 150 to 200ms; count-ups 300ms.

## States

Defined once in `tall-ui-kit.css` as variants that cover both the real pseudo-class and a forced `data-force` attribute, so the States matrix on the workbench gallery draws exactly what users see: `state-hover:` (`:hover`, never on disabled or busy controls), `state-active:` (`:active`), `state-disabled:` (`:disabled` or `aria-disabled`, except while busy), and the `focus-ring` / `focus-ring-inset` utilities (`:focus-visible` only, never a mouse click). No state lifts, scales, casts a shadow or fades.

| Button | Default | Hover | Pressed | Focus | Disabled | Loading |
|---|---|---|---|---|---|---|
| Primary | `primary` fill, ink 5.32:1, `primary-hover` edge 3.70:1 | `primary-fill-hover` `#FF7462`, ink 5.97:1 | `primary-fill-active` `#F0573F`, ink 4.62:1 (fill 3.42:1 on white) | 2px `focus` outline, 2px offset, 4.12:1 | `surface-subtle` fill, dashed `control` edge 3.38:1, `ink-muted` 5.42:1 | variant colours kept, ink spinner (15.8:1 on white, 5.3:1 on the fill), `aria-busy` |
| Secondary | ink fill, white 15.81:1 | `secondary-light`, white 13.14:1 | `neutral-900`, white 18.78:1 | same | same | white spinner |
| Outline | white, `control` edge 3.65:1, ink 15.81:1 | `surface-subtle`, `control-hover` edge 4.60:1, ink 14.63:1 | `surface-muted`, ink 13.63:1 | same | same | ink spinner |
| Ghost | no fill, `ink-body` 11.64:1 | `surface-subtle`, ink 14.63:1 | `surface-muted`, ink 13.63:1 | same | same | ink spinner |
| Destructive | `danger`, `on-danger` 4.77:1 (boundary 4.77:1) | `danger-hover`, 6.42:1 | `danger-active`, 8.36:1 | same | same | white spinner |
| Link | `primary-text` 5.21:1 | underlined | ink 15.81:1, underlined | same | `ink-muted` 5.85:1, no underline | ink spinner |

- Toggle buttons (`pressed` prop or `x-bind:aria-pressed`): on is a `surface-muted` fill, ink text and icon (13.63:1) and a 1px `control` border (3.65:1), so on differs from off by a border as well as the fill. Icons may also swap (focus mode).
- Loading (`loading` for a Livewire action, `busy` for a static state): the label stays in the accessibility tree (drawn transparent) so the button keeps its name, a silent spinner replaces it at the same width, `aria-busy="true"` is set at once and `aria-disabled` 50ms later (never `disabled`, which would drop focus), with a click guard; the spinner only appears after 150ms (`wire:loading.delay.short`), so quick requests show nothing.
- `disabled` renders `aria-disabled="true"` with a click guard, so a button that becomes unavailable while focused keeps focus. Disabled links render as `<a aria-disabled="true" role="link">` without `href`.
- Icons keep the 6px gap and 16px size in every state, left or right of the label.

| Tabs | Look | Ratio |
|---|---|---|
| Inactive | `ink-muted` text | 5.85:1 |
| Hover | ink text, no box | 15.81:1 |
| Selected or current | ink text and a 1px ink underline; hovering it changes nothing | 15.81:1 (underline 15.81:1) |
| Focus | 2px `focus` outline drawn 4px inside the tab (never clipped by the scrolling row), shown with the underline when the selected tab has focus | 4.12:1 |
| Disabled | `ink-muted` text with a dashed `control` underline on the label, `aria-disabled`, skipped by the arrow keys | 5.85:1, underline 3.65:1 |
| Count | follows the tab's text colour; a badge count keeps its own colours | as above |

Segmented control: a `surface-subtle` track with a decorative 1px `line` edge; unselected `ink-muted` (5.42:1 on the track); hover ink text (14.63:1), never a box; selected white with a 1px `control` border (3.38:1 on the track, 3.65:1 on white) and ink; focus the 2px ring; disabled `ink-muted` (5.42:1) with a dashed `control` underline (a dashed `control` border on icon-only segments), `aria-disabled`, no hover. Decision: the selected segment carries the 3:1 boundary; a `control` border on the track as well was measured (3.65:1) but drew a box inside a box, so the track keeps `line`. No shadow.

Form controls disabled: inputs, selects, textareas and the file drop area take a `surface-subtle` fill, a dashed `control` border and `ink-muted` text; checkboxes and radios a dashed border on `surface-subtle`, and when checked an `ink-muted` fill (5.85:1); switches a dashed border on `surface-subtle`; their labels turn `ink-muted`. Dropdown items: `ink-muted` and `cursor-not-allowed`, no hover fill. Pagination's unavailable previous or next: `ink-muted` (5.85:1).

Force a state in the gallery with `data-force="hover|active|focus"` on a button, or `'force' => 'hover'` on a tab or segment option.
# Theming

The kit's CSS is split in two:

- `resources/css/tall-ui-kit.css`: structure. Variants (`state-hover:`, `rail:`, `focus-mode:`), the type scale and `type-*` roles, elevation shadows, motion tokens, the base layer and every component layer. No colours, no fonts.
- `resources/css/themes/<theme>.css`: colours and fonts, as Tailwind 4 `@theme` tokens, plus the chart tokens in `@theme static` (always emitted, because charts read them through `var()` at runtime).

Import Tailwind, the kit, then **exactly one** theme. Switching themes is changing that one line; each theme carries its light and dark schemes ([Dark mode](#dark-mode)):

```css
@import 'tailwindcss';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/tall-ui-kit.css';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/neutral.css';
/* @import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/newbanger.css'; */
```

## Shipped themes

| Theme | Accent | Fonts | Lowest text pair (light / dark) | Lowest non-text pair (light / dark) |
|---|---|---|---|---|
| `neutral` (default) | Muted slate: `primary` `#AFC0D1` fill carrying `on-primary` text (8.77:1), `primary-text` `#3D5A78` (7.16:1; `#A9BED3` in dark), focus `#5B7A99` (`#6383A1` in dark) | System UI stack, nothing to load | 4.77 / 4.66:1 | 3.29 / 3.21:1 |
| `newbanger` | `#FF614D` fill carrying `on-primary` text (5.32:1), `primary-text` `#BC4530` (5.21:1; `#FF614D` itself in dark, 5.44:1 at its lowest), focus `#DB4C37` (`#E0563F` in dark) | Space Grotesk (titles, figures), Inter (everything else); load them in your layout | 4.62 / 4.66:1 | 3.16 / 3.04:1 |

Run `composer contrast` to print all 190 measured pairs for each theme in both colour schemes (four sets), `composer contrast neutral` for one theme, or `composer contrast neutral:dark` for one set. Every text pair must be at least 4.5:1 and every boundary, mark and icon at least 3:1, in every state, on every surface they sit on (the page, panels, popovers, the tinted page, the panel footer, the dark sidebar, hover and pressed fills, status tints). The script exits 1 on any failure, or when a light colour token has no dark value; `tests/Feature/UiKitContrastTest.php` runs the same checks in `composer test`.

## Tokens a theme must define

Every theme defines the same colour tokens (a test compares them), and every one of them again in its dark block (a second test diffs the two lists):

| Token | Role |
|---|---|
| `surface` | The page: data pages, inputs, table cells, the sticky action bars. White in light |
| `card`, `card-foreground` | Panels and cards (`x-ui.panel`, `x-ui.card`, skeleton cards). White in light, one step above the page in dark |
| `popover`, `popover-foreground` | Menus, listboxes, popovers, dialogs, sheets, chart tooltips, the date picker popup. White in light, two steps above the page in dark |
| `primary` | The one accent: the primary button fill. It carries `on-primary` text |
| `primary-hover` | The primary button's edge (3:1 on the page) |
| `primary-fill-hover`, `primary-fill-active` | Hovered and pressed primary fills (`on-primary` on them 4.5:1) |
| `primary-text` | Accent text and links (4.5:1 on every surface and on `primary/10`) |
| `on-primary` | Text and spinners on the primary fill (`ink` in light) |
| `secondary`, `secondary-light`, `neutral-900` | The strong fill: secondary button, checked controls, chosen days, the slider range, tooltips. Near-black in light, near-white in dark |
| `on-secondary` | Text, check marks and radio dots on `secondary` (white in light, near-black in dark) |
| `sidebar` | The dark sidebar's surface: `secondary` in light, a dark grey in dark (it never inverts) |
| `ink`, `ink-body`, `ink-muted` | Text: titles, body, meta (ink-muted 4.5:1 on every surface, `page-muted`, `panel-footer` and `surface-subtle`) |
| `line`, `line-strong` | Decorative rules and tag edges (no contrast requirement; never a control border) |
| `surface-subtle`, `surface-muted` | Hover and pressed fills, disabled fill, neutral badge, progress tracks |
| `page-muted`, `panel-border`, `panel-footer` | Elevation: tinted page, panel and popover borders, footers and table headers |
| `control`, `control-hover` | Borders of every input, checkbox, radio, switch, outline button and segmented edge (3:1 on every surface and on `surface-subtle`) |
| `focus` | The focus outline (3:1 on every surface, on `secondary` and on the sidebar) |
| `scrim` | Behind modals and the native `::backdrop` (no blur) |
| `skeleton` | Skeleton bars |
| `danger`, `danger-hover`, `danger-active`, `on-danger` | The destructive button and invalid field edges |
| `danger-text`, `success-text`, `warning-text`, `info-text` | Status text and marks on the page: field errors, deltas, stepper error, progress and meter tones, info badge |
| `danger-strong`, `success-strong`, `warning-strong`, `info-strong` | Status text on its tint: alerts, toasts, banners, success badge |
| `danger-tint`, `success-tint`, `warning-tint`, `info-tint` | Status fills (alerts, toasts, banners, badges, the destructive menu item hover) |
| `danger-edge`, `success-edge`, `warning-edge`, `info-edge` | The 1px edges of tinted notices (decorative) |
| `chart-accent`, `chart-1`, `chart-2`, `chart-3` | Series colours, each 3:1 on the page and on a card (`chart-2` also on `surface-muted`) |
| `chart-grid`, `chart-axis`, `chart-tick` | Gridlines, axis, tick text (`chart-tick` 4.5:1) |

Fonts: `--font-sans`, `--font-heading` (titles and figures), `--font-body`.

Status tones used to be Tailwind's red, amber, emerald and blue at fixed steps. They are tokens now, with the same light values in both themes (Tailwind 4's rendering of red-600/700/800/50/200, emerald-700/800/50/200, amber-800/900/50/200, blue-700/800/50/200), so dark mode can give them dark tints and lighter text.

## Dark mode

Each theme file carries both schemes: the light tokens in its `@theme` blocks, and a dark block at the end that overrides every colour token (plus the shadows) through the kit's `dark` variant:

```css
@layer theme {
    :root {
        @variant dark {
            color-scheme: dark;
            --color-surface: #111213;
            /* ...every colour token again... */
            --shadow-panel: none;
        }
    }
}
```

**How the scheme is chosen.** `html[data-color-scheme]` is `light`, `dark` or `system`. `system` (and a page with no attribute) follows `prefers-color-scheme`. The `dark` custom variant in `tall-ui-kit.css` matches `[data-color-scheme="dark"]`, or `system` while the OS asks for dark, on screen only: print, and with it "Save as PDF", always uses light. Apps may use `dark:` for their own rare exceptions; kit components never need it, because every colour is a token.

**No flash.** `x-ui.head` sets the attribute in an inline script before the stylesheet, so the first paint is already in the right scheme. Put it in `<head>` before the CSS link. The order it checks: its `scheme` prop (a value the server decided), the bound `ColorSchemeStore` when `persist` is `server`, `localStorage` `<prefix>color_scheme`, then `config('tall-ui-kit.color_scheme.default')` (`system`). Values outside `schemes` are ignored.

**The toggle.** `x-ui.theme-toggle` is a Light / Dark / System segmented control (sun, moon, monitor) with the `x-ui.segmented` look: a radiogroup with an accessible name (`label`, "Colour scheme"), arrow keys that move and choose, one tab stop, the kit focus ring, and a polite live region that announces the change ("Colour scheme: Dark", or "System (Dark)"). It changes the scheme at once, writes `localStorage` (other tabs follow through the `storage` event) and dispatches `ui-color-scheme` on `window`. `iconOnly` keeps the words as accessible names for tight headers. In JS: `window.uiColorScheme.get()`, `.set('dark')`, `.resolved()`.

**Config** (`config/tall-ui-kit.php`):

```php
'color_scheme' => [
    'default' => env('TALL_UI_COLOR_SCHEME', 'system'), // light | dark | system
    'schemes' => ['light', 'dark', 'system'],            // ['light'] keeps the app light: stored choices ignored, no toggle
    'persist' => 'local',                                // or 'server'
],
```

**Server persistence** (the `DismissStore` pattern). With `persist` set to `server`, use `TallUiKit\Livewire\Concerns\WithColorScheme` on the Livewire component around the toggle: the toggle calls `setColorScheme()`, which checks the value (422 otherwise) and writes the bound `TallUiKit\Support\ColorScheme\ColorSchemeStore`; `x-ui.head` prints the stored value on the next page and copies it to `localStorage`. The kit binds a session store; bind your own to keep it per user:

```php
$this->app->bind(ColorSchemeStore::class, UserColorSchemeStore::class); // get(): ?string, put(string): void
```

**What changes in dark.** The page is a calm near-black (neutral `#111213`, NewBanger `#121312`, never `#000`); panels (`card`) and popovers one and two steps lighter, so elevation reads from the surface; 1px borders carry every edge (`shadow-panel`, `shadow-thumb` and `shadow-bar` are off, popover and dialog shadows are faint black). `secondary` turns near-white, so checked controls, chosen days, the slider range and tooltips still stand out, with `on-secondary` marks; the dark sidebar keeps its own `sidebar` surface and gains a 1px `line` edge so it never melts into the page. Status tints are dark and their text light. Scrollbars, form controls and `::backdrop` follow through `color-scheme: dark` and `scrim`. No glow, gradient or neon anywhere.

**Lowest measured pairs in dark:** neutral text 4.66:1 (white on the destructive fill), non-text 3.21:1 (the focus outline beside a chosen day); NewBanger text 4.66:1 (same), non-text 3.04:1 (same).

**Always light:**

- **Email.** Mail clients ignore the kit's CSS and most ignore `prefers-color-scheme`; build emails with inline light colours (the light hex values in the theme file) and never rely on tokens.
- **PDFs and printed pages.** The dark variant is screen only, so printing a page uses light tokens. Server PDFs (Dompdf in NewBanger's export pipeline, [exports](exports.md)) render their own light stylesheet; keep them light.
- **Third-party embeds.** Spotify, YouTube and Apple Music players inside `x-ui.embed` keep the provider's own look; the facade around them follows the scheme.

**Logos and images.** `brand.logo_light` shows on light grounds, `brand.logo_dark` on dark ones: the dark sidebar, and in the dark scheme the light and inset sidebars and `x-ui.logo` too (both images are printed and CSS shows one, so switching needs no reload). Photos and avatars are untouched.

**Your own theme.** Copy a shipped theme including its dark block. A theme with no dark block still works: it stays light in every scheme (`composer contrast` says "light only"), but then set `schemes` to `['light']` so no toggle is offered.

### Enabling it in NewBanger (later)

NewBanger still carries its in-repo copy of the kit, so nothing changes there until it opts in:

1. Move to the package (or copy `themes/newbanger.css` with its dark block, `tall-ui-kit.css` with the `dark` variant, `color-scheme.js`, `head.blade.php`, `theme-toggle.blade.php` and `logo.blade.php`).
2. Replace raw colours in app views the same way the kit did (`bg-white` to `bg-surface`, `bg-card` or `bg-popover`; `text-white` on dark fills to `text-on-secondary`; Tailwind status colours to the status tokens), and extend `AdminComponentsTest` to grep for them.
3. Put `<x-ui.head />` before the stylesheet in every layout, set `brand.logo_dark`, and add `<x-ui.theme-toggle />` to the account menu or settings. Start with `'schemes' => ['light']` to ship the code dark-off, then open it up.
4. Optional: bind a `ColorSchemeStore` on the users table and set `persist` to `server`.
5. Keep emails and Dompdf exports light; run `composer contrast` and the axe audit in both schemes.

## Writing your own theme

1. Copy `themes/neutral.css` into your app and change the values, light and dark.
2. Measure it: `php vendor/olotintemitope/tall-ui-kit/scripts/contrast.php /absolute/path/to/your-theme.css`, from a checkout of the package with its dev dependencies, or copy the script. Fix every failing pair.
3. Import your file instead of a shipped theme.

## Brand slots

`config/tall-ui-kit.php` (`php artisan vendor:publish --tag=tall-ui-kit-config`):

```php
'brand' => [
    'name' => env('APP_NAME'),
    'logo_light' => '/images/logo-black.png', // on light grounds
    'logo_dark' => '/images/logo-white.png',  // on the dark sidebar and everywhere in the dark scheme
],
```

With no logo the brand name prints as text in the heading role. `x-ui.sidebar.header` also takes a `logo` slot for an inline SVG (draw it in `currentColor` and it follows the scheme). `x-ui.logo` (`href`, `height`) prints the right logo outside the sidebar.

## Storage keys

The kit keeps a few choices in the browser (colour scheme, sidebar collapse, focus mode, privacy mode, wizard drafts) under `config('tall-ui-kit.storage_prefix')` (default `tallui_`). `x-ui.head` prints the prefix on `<html>`; an app that already has keys can keep them by setting the prefix (and `privacyKey`) to match.
