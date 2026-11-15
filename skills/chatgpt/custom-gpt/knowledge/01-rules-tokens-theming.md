# Rules

These rules are the kit. A page built from kit components that breaks one of them is still wrong. Check every new screen against this page before showing it.

## The quick list

1. **No AI look.** Banned: thick or coloured edge borders (accent bars on alerts, toasts, cards or tabs), icon-in-a-tile empty states, centred hero empty states, coloured icon badges, `rounded-2xl` or larger, heavy, stacked or coloured shadows, glows, glassmorphism or backdrop blur, gradients, KPI card grids with icons and sparklines, donut, pie, gauge and progress-ring charts, gradient fills under lines, delta chips with arrows in tinted pills, leaderboard medals, filler copy ("Glad to see you again", "Insights", "at a glance"), emoji and em dashes in user-facing copy. Wanted: dense, flat, professional (Stripe, Linear, Vercel), hierarchy from the `type-*` roles, no unnecessary space, full-width data pages.
2. **Borders are 1px.** The 2px focus outline is the only thicker line.
3. **Focus is always visible and never lost.** `focus-ring` (2px `focus` outline, 2px offset) on every control, `:focus-visible` only; focus never falls to `<body>` (see [accessibility.md](accessibility.md)).
4. **Contrast: 4.5:1 for text, 3:1 for boundaries and marks, in every state, no exemptions.** Disabled controls never fade with opacity.
5. **Every dropdown is custom.** Never a native `<select>`: use `x-ui.select` (or `x-ui.combobox` to filter by typing).
6. **Popups open below their trigger, never flipped above.** `x-ui.select`, `x-ui.dropdown`, `x-ui.combobox` and `x-ui.popover` use `x-anchor.bottom-*.noflip` plus `$revealBelow` (the page scrolls to make room). Menus size to their longest label. Only tooltips may flip.
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
# Tokens, type roles and states

Values below are the NewBanger theme; the neutral theme uses the same token names with its own values (see [theming.md](theming.md)). `composer contrast` prints every measured pair for both themes.

## Colour tokens

Ratios are against white unless stated.

| Token | Value | Use | Contrast |
|---|---|---|---|
| `ink` / `secondary` | `#212322` | Titles, strong text, checked controls | 15.9:1 |
| `ink-body` | `#373938` | Body text | 11.64:1 |
| `ink-muted` | `#646564` | Meta, captions, labels, placeholders, inactive nav | 5.86:1 (5.46:1 on `#F6F7F9`, 5.47:1 on `page-muted`) |
| `primary` | `#FF614D` | Accent fill; carries `ink` text | ink on it 5.32:1 (white would be 2.97:1); fill 2.97:1 on white |
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
| Error text | `red-700` | Field errors, late counts, inline load errors, falling deltas | 6.42:1 (Tailwind 4 `#c10007` as rendered) |
| Success text | `emerald-700` | Rising deltas, inflows, footnote lead-ins | 5.36:1 (`#007a55`) |

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

Dark admin sidebar (`#212322`): items `white/70` (8.40:1; 7.35:1 on hover), section labels and counts `white/65` (7.44:1), icons `white/60` (6.56:1), active item `white` on `white/10` (11.63:1) with a `white/40` inset ring (3.72:1 against the sidebar). Light sidebars (`panel-footer`): items `ink-muted` (5.61:1), active `ink` on `black/6` (13.26:1) with a `control-hover` inset ring (4.17:1 on the active fill, 4.76:1 on the sidebar).

Status badges (text on fill, re-measured with Tailwind 4's rendered palette): success emerald-800/50 7.23, warning amber-800/50 6.84, danger red-700/50 5.87, info blue-700/50 6.28, neutral ink/`surface-muted` 13.63, accent `#BC4530` on `primary/10` 4.67. Alerts and toasts: red-800 on red-50 7.64, amber-900 on amber-50 8.73, emerald-800 on emerald-50 7.23, ink on `panel-footer` 15.15.

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
| Destructive | `red-600`, white 4.77:1 (boundary 4.77:1) | `red-700`, white 6.42:1 | `red-800`, white 8.36:1 | same | same | white spinner |
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

Import Tailwind, the kit, then **exactly one** theme. Switching themes is changing that one line:

```css
@import 'tailwindcss';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/tall-ui-kit.css';
@import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/neutral.css';
/* @import '../../vendor/olotintemitope/tall-ui-kit/resources/css/themes/newbanger.css'; */
```

## Shipped themes

| Theme | Accent | Fonts | Lowest text pair | Lowest non-text pair |
|---|---|---|---|---|
| `neutral` (default) | Muted slate: `primary` `#AFC0D1` fill carrying ink text (8.77:1), `primary-text` `#3D5A78` (7.16:1), focus `#5B7A99` (4.48:1 on white, 3.64:1 on the dark sidebar) | System UI stack, nothing to load | 4.77:1 | 3.29:1 |
| `newbanger` | `#FF614D` fill carrying ink text (5.32:1), `primary-text` `#BC4530` (5.21:1), focus `#DB4C37` | Space Grotesk (titles, figures), Inter (everything else); load them in your layout | 4.62:1 | 3.16:1 |

Run `composer contrast` (or `composer contrast neutral`) to print all 86 measured pairs per theme: every text pair must be at least 4.5:1 and every boundary, mark and icon at least 3:1, in every state, on every surface they sit on (white, the tinted page, the panel footer, the dark sidebar, hover and pressed fills). The script exits 1 on any failure; `tests/Feature/UiKitContrastTest.php` runs the same check in `composer test`.

## Tokens a theme must define

Every theme defines the same colour tokens (a test compares them):

| Token | Role |
|---|---|
| `primary` | The one accent: the primary button fill. It carries `ink` text, so it must be light enough for 4.5:1 |
| `primary-hover` | The primary button's edge (3:1 on white, because a light fill alone is under 3:1) |
| `primary-fill-hover`, `primary-fill-active` | Hovered and pressed primary fills (ink on them 4.5:1) |
| `primary-text` | Accent text and links (4.5:1 on white and on `primary/10`) |
| `secondary`, `secondary-light`, `neutral-900` | Dark fill: secondary button (white text), checked controls, the dark sidebar, pressed secondary |
| `ink`, `ink-body`, `ink-muted` | Text: titles, body, meta (ink-muted 4.5:1 on white, `page-muted`, `panel-footer` and `surface-subtle`) |
| `line`, `line-strong` | Decorative rules and tag edges (no contrast requirement; never a control border) |
| `surface-subtle`, `surface-muted` | Hover and pressed fills, disabled fill, neutral badge |
| `page-muted`, `panel-border`, `panel-footer` | Elevation: tinted page, panel and popover borders, footers and table headers |
| `control`, `control-hover` | Borders of every input, checkbox, radio, switch, outline button and segmented edge (3:1 on white and on `surface-subtle`) |
| `focus` | The focus outline (3:1 on white and on `secondary`) |
| `scrim` | Behind modals (no blur) |
| `card`, `card-foreground`, `popover`, `popover-foreground` | Older shadcn-style names a few class strings use |
| `chart-accent`, `chart-1`, `chart-2`, `chart-3` | Series colours, each 3:1 on white (`chart-2` also on `surface-muted`) |
| `chart-grid`, `chart-axis`, `chart-tick` | Gridlines, axis, tick text (`chart-tick` 4.5:1) |

Fonts: `--font-sans`, `--font-heading` (titles and figures), `--font-body`.

Status colours (danger, warning, success, info) are Tailwind's red, amber, emerald and blue at fixed steps and are the same in every theme.

## Writing your own theme

1. Copy `themes/neutral.css` into your app and change the values.
2. Measure it: `php vendor/olotintemitope/tall-ui-kit/scripts/contrast.php /absolute/path/to/your-theme.css`, from a checkout of the package with its dev dependencies, or copy the script. Fix every failing pair.
3. Import your file instead of a shipped theme.

## Brand slots

`config/tall-ui-kit.php` (`php artisan vendor:publish --tag=tall-ui-kit-config`):

```php
'brand' => [
    'name' => env('APP_NAME'),
    'logo_light' => '/images/logo-black.png', // on light sidebars
    'logo_dark' => '/images/logo-white.png',  // on the dark sidebar
],
```

With no logo the brand name prints as text in the heading role. `x-ui.sidebar.header` also takes a `logo` slot for an inline SVG.

## Storage keys

The kit keeps a few choices in the browser (sidebar collapse, focus mode, privacy mode, wizard drafts) under `config('tall-ui-kit.storage_prefix')` (default `tallui_`). `x-ui.head` prints the prefix on `<html>`; an app that already has keys can keep them by setting the prefix (and `privacyKey`) to match.
