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

See [Charts](components/data-display.md) for the marks. Hand-checked contrast against white: `chart-accent` 5.21:1, `chart-1` 15.81:1, `chart-2` 5.85:1, `chart-3` 3.16:1. On the `surface-muted` track of horizontal bars `chart-2` is 5.05:1 but `chart-3` only 2.73:1, so horizontal bars use `chart-1` or `chart-2`. Stacked segments sit on white gaps, so each is measured against white.

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
- Controls: one size scale for inputs, selects, the combobox, number and tag inputs, colour, date pickers, copy controls and buttons: `sm` 32px (`h-8`, dense toolbars), `default` 36px (`h-9`; `md` is an alias) and `lg` 40px (`h-10`), from `TallUiKit\Support\FormControl::size()` and `::height()`. Segmented controls, the theme toggle and tabs have `sm` and `default`. Radii: checkboxes and segments inside a segmented track 4px (`rounded-sm`), controls 6px (`rounded-md`), panels, menus and dialogs 8px (`rounded-lg`), badges full.
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
