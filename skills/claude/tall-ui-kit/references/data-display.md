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
