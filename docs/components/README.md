# Components

Every `x-ui.*` component with its props and slots. Families: [forms](forms.md), [HTML elements](html-elements.md), [navigation](navigation.md), [overlays](overlays.md), [feedback](feedback.md), [data display](data-display.md), [data table](data-table.md), [exports](exports.md), [copy and icons](copy-and-icons.md), [workflow and progress](workflow.md), [chat and community](community.md), [files](files.md).

## Components

Form controls take `wire:model` / `x-model` and other attributes through to the control, and wire `id`, `aria-invalid`, `aria-describedby` and `aria-required` from the surrounding `x-ui.field` (or from `name` / `wire:model` and `$errors` on their own).

| Component | Props and slots |
|---|---|
| `x-ui.head` | Once in `<head>`, before the stylesheet: storage prefix, and colour scheme, privacy, sidebar and focus state applied before first paint. `area` (the shell's sidebar area), `privacyKey`, `scheme` (a server-decided light, dark or system) |
| `x-ui.theme-toggle` | Light / Dark / System radiogroup (the segmented look, sun, moon and monitor icons). `label` (Colour scheme), `size` (default, sm), `iconOnly`. Announces the change; keyboard per the radio group pattern. Renders nothing when only one scheme is configured. See [Dark mode](../theming.md) |
| `x-ui.logo` | `brand.logo_light`, swapped for `brand.logo_dark` in the dark scheme; the brand name as text with no logo. `href`, `height` (22) |
| `x-ui.button` | `variant` default (alias `primary`), secondary, outline, ghost, destructive, link; `size` default (alias `md`), sm, lg, icon, icon-sm, icon-lg; `href`; `type`; `loading` (action name or true: spinner, same width, `aria-busy`); `busy`; `disabled`; `pressed` (toggle). See [States](../tokens.md) |
| `x-ui.badge` | `variant` (alias `tone`) neutral, success, warning, danger (or `error`), info, accent, outline (tag); `size` default, sm; `lean` (one `.ui-badge` class and data attributes, about 60 bytes, for table cells). One line always: sized to the label, never wrapped, shrunk or clipped |
| `x-ui.field` | `label`, `name`, `id`, `hint`, `error`, `required` (muted ` *`), `width` |
| `x-ui.field-row` | `cols` 2 or 3; controls fill their column |
| `x-ui.input` | `type`, `size` (sm 32px, default 36px, lg 40px; the same scale on every control), `width` full, lg, md (default), sm, xs; `invalid`; slots `prefix`, `suffix` (`plain` attribute drops the divider) |
| `x-ui.textarea` | `rows` (min 3), `width` (default full), `invalid` |
| `x-ui.select` | Custom listbox, never a native `<select>` (see [Select](../accessibility.md)). `options` (value => label, or a list of `['value', 'label', 'disabled']`; a string key with an array value is a group), `placeholder` (an empty first option), `selected` (a list with `multiple`), `size` (36px or `sm` 32px), `width` (default `auto`: as wide as the longest option or placeholder plus the chevron, at least 6rem, at most its container; `full`, `lg`, `md`, `sm`, `xs`), `invalid`, `multiple`, `rows` (kept for the old API); `<option>`s in the slot are parsed; `wire:model` (any modifier) or `x-model` on the component |
| `x-ui.date` | Custom date picker (never native; see [Dates and times](forms.md)). `size`, `width` (default `w-40`), `invalid`, `min`, `max`, `disabledDates`, `weekStart`, `placeholder`, `defer`; value Y-m-d |
| `x-ui.date-range` | Two dates in one field and one popup with preset buttons. `wire:model.start` + `wire:model.end`, or one array `wire:model`; `presets`, `min`, `max`, `disabledDates`, `start`, `end`, `startName`, `endName`; width `w-64` |
| `x-ui.search` | `size`, `width` (default md), `placeholder`, `shortPlaceholder` (shown when the full placeholder does not fit the box, then "Search"; measured on resize by `resources/js/forms.js`; the accessible name stays the full text); clear button |
| `x-ui.checkbox` | `label`, `description` (alias `hint`), `error`, `invalid`, `indeterminate`; whole row is the target |
| `x-ui.radio-group` | `name`, `label`, `hint`, `options`, `selected`, `variant` list or cards, `orientation`, `required`, `invalid`; a checked radio is an ink fill with a white dot; a selected card has a 1px `primary-text` border (5.21:1) and a `primary/5` tint, an unselected one a 1px `control` border (3.65:1); the 3:1 state difference is carried by the radio (ink fill 14.97:1 on the tint vs an empty ring), not the tint (1.06:1) |
| `x-ui.switch` | `label`, `description`, `checked`, `size`, `name`; `role="switch"` |
| `x-ui.file` | `multiple`, `accept`, `hint`, `invalid`, `variant` (list, images: thumbnail previews with Remove); slot `files`; works with `wire:model` uploads. Additive (v0.9): `maxSize`, `maxFiles`, `maxTotalSize`, `validate` (per-file errors, a hint written from `accept` and the limits), `reorder` + `reorderAction`, `removeAction`, `paste`, `capture`, `chunked` + `chunkSize`. See [forms](forms.md) "File" |
| `x-ui.file-manager` | Grid or list of folders and files with breadcrumbs, sort, selection, bulk bar, inline rename, Move, delete, new folder, drag-and-drop upload, Load more. See [files](files.md) |
| `x-ui.file-preview` | Image (zoom), PDF, audio, video, text and fallback viewers, details, Download, Open in new tab, Previous / Next; `variant` panel, dialog, sheet. See [files](files.md) |
| `x-ui.file-card`, `x-ui.attachment-list` | One file as a row, card or compact link with a type label, status pill and actions; a named list of them. See [files](files.md) |
| `x-ui.form` | `<form>` with `dirty` tracking and focus on the first invalid field after submit |
| `x-ui.form-section` | `title`, `description`; slot `footer`; 280px text column plus panel |
| `x-ui.form-page` | tinted region for forms inside a white page |
| `x-ui.form-actions` | `sticky` (shows only while the form is dirty) |
| `x-ui.error-summary` | `fields` (name => label), `min` (3) |
| `x-ui.panel` | slot `footer` |
| `x-ui.tabs`, `x-ui.tabs.panel` | `tabs` (key => label, or key => `label`, `icon`, `count`, `badge`, `disabled`, `href`), `active`, `size` (default h-10, sm h-9), `label`; panel `name`. See [Tabs](navigation.md) |
| `x-ui.segmented` | `options`, `value`, `size` (default 36px overall with 30px segments, `sm` 32px with 26px, matching buttons and inputs), `label`, `responsive` (an `x-ui.select` with the same binding below `sm`); three forms: **text** (`value => label`) for periods and short choices; **icon with label** (`value => ['label', 'icon']`, 16px icon, 6px gap) for views whose words need a cue; **icon only** (`['label', 'icon', 'iconOnly' => true]`, square segment, label as `aria-label` and tooltip) only for well-known views such as table vs chart. Any option can be `'disabled' => true`. Never mix icon and plain segments (throws in local and testing). Never wraps. Look and states: see States. `data-segmented-indicator` marks the selected segment for a later sliding indicator |
| `x-ui.table`, `.th`, `.row`, `.td` | table `flush`, `captionVisible`, `sticky`, `maxHeight`, slots `colgroup` and `foot` (see `docs/components/html-elements.md`), th `row` and `scope`, `caption` (also names the scroll region: a table that scrolls sideways becomes a focusable `region` so keyboard users can scroll it), slot `head` (its attributes go on the `<thead>`); th `numeric`; td `numeric`, `strong`, `muted`, `mono` (every cell wraps and the row grows; numbers stay on one line), `wrap` (kept for old call sites) |
| `x-ui.stat-strip`, `x-ui.stat` | strip `columns` (2 to 6 at `xl`; 2 per row on phones, 3 from `sm`), top and bottom `line` borders; stat cells draw right and row divider borders; stat `label`, `value`, `sub`, `money`, `currency`, slot value; null shows a muted dash |
| `x-ui.delta` | `current`, `previous`, `period`, `upIsGood`; words in the meta role, only the percentage in the numeric role; success-text, danger-text or ink-muted |
| `x-ui.kv-list`, `x-ui.kv` | kv `label`, `sub`, slot `subline`, slot value (numeric, right-aligned) |
| `x-ui.segment-bar` | `segments` (label, value, chart token `color`), `label`, `empty`; 8px bar plus legend |
| `x-ui.ranked-list`, `x-ui.ranked-item` | list `empty`; item `label`, `sub`, `rank`, `value`, `share` (0 to 1: compact bar row), `href` (the label links) |
| `x-ui.alert` | `tone` (alias `variant`) danger (or `error`), warning, success, info, neutral; flat inline notice: 1px border all round in a light status tone (`danger-edge`, `warning-edge`, `success-edge`, `line`), a very light fill (`-50`, `panel-footer`) and AA tone text, measured on their fills: danger-strong 7.64:1, warning-strong 8.73:1, success-strong 7.23:1, ink 15.15:1; no edge, no icon. Toasts and the error summary use the same look |
| `x-ui.page-header` | `title` (wraps; long unbroken titles break), `eyebrow`, `description`, `level` (1); slot `actions` (wrap on narrow screens) |
| `x-ui.section-label` | `as` |
| `x-ui.link-arrow` | `href` |
| `x-ui.pagination` | `paginator`, `elements`, `livewire`, `scrollTo`, `aria-label` (default "Pagination"; name it when a page has two); default for Livewire and plain paginators |
| `x-ui.dropdown`, `.item`, `.separator`, `.label` | `align`, `placement`, `width`; item `href`, `type`, `variant` danger |
| `x-ui.dialog`, `.form`, `.steps`, `.fullscreen`, `x-ui.sheet` | See [Dialogs and confirmations](overlays.md) |
| `x-ui.confirm`, `.destructive`, `.reason`, `x-ui.alert-dialog`, `x-ui.confirm-dialog` (older name) | See [Dialogs and confirmations](overlays.md) |
| `x-ui.toaster`, `x-ui.toast` | See [Toasts](feedback.md) |
| `x-ui.sidebar`, `.header`, `.section`, `.item`, `.group`, `.account` | See [Sidebar](navigation.md) |
| `x-ui.notification-bell`, `-panel`, `-item`, `-group` | See [Notifications](feedback.md) |
| `x-ui.tooltip` | `text`, `position` (alias `placement`); slot `content` replaces `text` (e.g. a label with `x-ui.kbd tone="dark"`) |
| `x-ui.card` (+ header, title, description, content, footer) | `title`, `description`, `flush`, `as`; slots `badge`, `actions`, `footer`. See [Cards](data-display.md) for the typed cards |
| `x-ui.chart`, `x-ui.chart.sparkline` | See [Charts](data-display.md) |
| `x-ui.filter-token` | `label`, `removeLabel`. See Filter tokens |
| `x-ui.spinner`, `x-ui.loading-inline`, `x-ui.loading-overlay`, `x-ui.progress-bar`, `x-ui.load-more` | See [Loading](feedback.md) |
| `x-ui.empty-state` | `title`, `description`, `actionLabel`, `actionHref`, slot `action` |
| `x-ui.checklist`, `.item` | Steps of any onboarding, setup, verification or launch flow: `title`, `description`, `items` (`label`, `hint`, `done`, `optional`, `href` or `action`, `actionLabel`, `key`), `name`, `collapsible`, `collapsed`, `dismissible`, `dismissed`, `persist` (local, server, none), `loading`, `completeText`, `dismissLabel`, `headingLevel`. See `docs/components/feedback.md` "Checklist" |
| `x-ui.skeleton`, `.line`, `.text`, `.avatar`, `.stat`, `.stats`, `.table`, `.card`, `.chart`, `.grid`, `.list` | `count`, `rows`; `.stat` is one `x-ui.stat` cell (a dt/dd pair, since it sits in the strip's `<dl>`), `.stats` a whole strip; `.grid` bordered card blocks (`count`, `columns` 2 to 4 from lg, `label`), `.list` avatar, title and value rows (`rows`, `label`, `flush`); both are one `role="status"` with the blocks hidden |
| `x-ui.avatar` | `name`, `initials`, `size` xs, sm, default, lg; `tone` light, dark; `src` (a photo: fixed size, `object-cover`, lazy, `decoding="async"`, alt text from `name`; the initials show if it fails to load), `alt` ("" when the name is printed beside it) |
| `x-ui.kbd` | `tone` light, dark |
| `x-ui.money` | `value`, `currency`, `sign`, `lean` (one span, no Alpine, for table cells; privacy mode still blurs it); blurs in privacy mode |
| `x-ui.icon` | `name` (any Lucide name, see Icons below), `strokeWidth` (2); `aria-hidden` by default |
| `x-ui.heading`, `x-ui.text`, `x-ui.link`, `x-ui.code`, `x-ui.code-block`, `x-ui.blockquote`, `x-ui.list`, `x-ui.number`, `x-ui.prose` | See Typography components |
| `x-ui.tel`, `x-ui.password`, `x-ui.otp`, `x-ui.number-input`, `x-ui.range`, `x-ui.color`, `x-ui.time-input`, `x-ui.datetime`, `x-ui.month`, `x-ui.week` (custom pickers), `x-ui.combobox`, `x-ui.fieldset`, `x-ui.output` | Native inputs: see `docs/components/html-elements.md` |
| `x-ui.disclosure`, `x-ui.accordion`, `.item`, `x-ui.popover`, `x-ui.progress`, `x-ui.meter` | Interactive elements, and the Popover API decision: `docs/components/html-elements.md` |
| `x-ui.time`, `x-ui.dl`, `.item`, `x-ui.breadcrumb` | Text and structure: `docs/components/html-elements.md` |
| `x-ui.audio`, `x-ui.video`, `x-ui.embed`, `x-ui.figure`, `x-ui.lightbox` | Media and embed providers: `docs/components/html-elements.md`. Prices are app components |
| `x-ui.form-layout.*` | Form layouts: `docs/components/forms.md` |
| `x-ui.stepper`, `x-ui.timeline` (+ `.item`, `.day`), `x-ui.tag-input`, `x-ui.avatar-group`, `x-ui.sortable` (+ `.item`), `x-ui.slider`, `x-ui.banner`, `x-ui.tree` | Workflow components: `steps`/`current`/`variant`/`orientation`/`clickable`; `items`/`groupByDay`/`limit`; array `wire:model`, `suggestions`, `max`, `pattern`; `people`/`max`; `items`/`action` (one call with the new order); single or `dual`, `format`; `tone`, `dismissible`, `name`, `persist` (the `DismissStore`); `nodes`, `selectable`/`checkable`, `lazy`. See [workflow](workflow.md) |
| `x-ui.progress` (sizes, tones, `labelPosition`, `format`, `buffer`), `x-ui.progress-ring`, `x-ui.progress-steps`, `x-ui.upload-progress` (+ `.list`) | Progress family: see [workflow](workflow.md) "Progress" |
| `x-ui.chat` (+ `.message`, `.day`, `.typing`, `.composer`, `.inbox`), `x-ui.post`, `x-ui.comment-thread`, `x-ui.reactions`, `x-ui.member-card`, `x-ui.profile-header`, `x-ui.follow-button`, `x-ui.poll`, `x-ui.channel-list`, `x-ui.leaderboard` | Chat and community: `messages`/`olderAction`/`sendAction` (one call with the text and an idempotency key); `conversations`; `likeAction` (optimistic, rolled back on `false`); `comments`/`depth`/`replyAction`; `reactions`/`options`/`action`; `following`/`followAction`; `options`/`multiple`/`voted`/`action`; `sections`; `rows` with `change`/`current`. See [chat and community](community.md) |

## Typography components

Use these instead of hand-written tags when the role or behaviour matters; plain elements with a `type-*` class are still fine for one-off lines.

- `x-ui.heading`: `level` 1 to 6 picks the tag (document outline); `size` (title, heading, subheading, label) picks the look and is independent. Defaults by level: h1 title, h2 heading, h3 subheading, h4 to h6 label. Use `size` when the outline and the look disagree, e.g. a dialog title that is an h2 in the outline but sits inside a dense card (`:level="2" size="subheading"`).
- `x-ui.text`: `as` (p), `role` body, body-strong, meta, caption; `tone` default (the role's colour), muted (ink-muted 5.86:1), danger (danger-text 6.42:1), success (success-text 5.36:1). It always wraps and breaks long unbroken strings; there is no one-line or clamp option. Use tones for state text only, never for emphasis.
- `x-ui.link`: inline link inside running text, `primary-text` (5.21:1), always underlined 3px below the baseline, ink on hover, inherits the surrounding size. `target="_blank"` adds `rel="noopener"` (merged with any `rel` you pass), a 14px arrow and screen-reader text "(opens in new tab)". Use `x-ui.link-arrow` for a "View all" link beside a section and `x-ui.button variant="link"` for actions.
- `x-ui.code`: inline code or identifiers, mono 13px (numeric role), ink on `surface-muted` (13.6:1), `rounded-sm`.
- `x-ui.code-block`: payloads, config and logs. Mono 13/20, `panel-footer` fill, 1px `line` border, 8px radius, horizontal scroll (the scroll area is focusable). Props `label` (caption header), `copy` (a Copy button that toasts "Copied to clipboard."). Print the content with `{{ }}` in the slot so it is escaped.
- `x-ui.blockquote`: a quotation with a 16px indent in body text; `attribution` in meta below, `cite` URL. No side bar, no fill, no italics.
- `x-ui.list`: `type` ul or ol, 6px between items, markers in ink-muted; `variant="plain"` drops markers and indent for stacked lines that are not a sentence list.
- `x-ui.number`: `value`, `format` plain (1,204), compact (1.2k, full value as a `title`), percent (a ratio: 0.124 is 12.4%), `precision`. Numeric role. Money is always `x-ui.money`.
- `TallUiKit\Support\Format`: `number($v, $decimals)`, `compact($v, $precision = 1)` (950, 1.2k, 12k, 3.4M, 2.1B; trailing `.0` dropped, 999,950 is 1M), `percent($ratio, $precision = 1)`, `money($v, $currency)` (delegates to `TallUiKit\Support\Money`, so it always matches `x-ui.money`). Negatives use the minus sign (U+2212) as `x-ui.delta` does; a missing value is an en dash.
- `x-ui.prose`: long user- or admin-authored content (help articles, campaign briefs, ticket messages), styled by the scoped `.prose-kit` class in `tall-ui-kit.css` (the Tailwind typography plugin is not used). 68ch measure, blocks 12px apart, h2 heading, h3 subheading, h4 label, lists with 6px gaps and ink-muted markers, links as `x-ui.link`, inline code and `pre` as `x-ui.code` and `x-ui.code-block`, blockquote with a 16px indent, tables in the `x-ui.table` style, `hr` as a 1px `line`, images with an 8px radius, `figcaption` in caption, `strong` at 600 in ink. `size="sm"` is the dense 13/20 variant for ticket threads. **User HTML must be sanitised on the server before it is printed with `{!! !!}`.** No HTML purifier is installed (`mews/purifier` is not in `composer.json`); add one, or render Markdown with a safe converter, before showing real user HTML. The gallery samples are fixed strings.

## Filter tokens

`x-ui.filter-token`: a removable active filter. 1px `control` border (3.65:1), 6px radius (`rounded-md`, never a pill: pills are status badges), 28px tall, label in body text (ink), and a 24px × button named "Remove filter: <label>" (`removeLabel` overrides). Click handlers (`wire:click`, `x-on:click`) go on the button; other attributes on the token. List tokens in a `ul` after a visually hidden "Active filters" heading.

## Icons

`x-ui.icon` draws every Lucide icon from the `mallardduck/blade-lucide-icons` package, pinned at `1.23.0` (Lucide `0.412.0`). There is no hand-copied path map any more: `TallUiKit\Support\LucideIcons::inner()` reads the package's SVG file once per request and the kit keeps its own `<svg>` root (24px box, `currentColor`, `stroke-width`). Names follow the React app (`lucide-react@0.436.0`); `LucideIcons::ALIASES` points the renamed chart icons at the package's older files, and only two of them draw differently from React: `chart-line` (`line-chart`) and `chart-column` (`bar-chart-3`) have a square axis corner where 0.436 rounds it by 2px. An unknown name throws in local and testing; `tests/Feature/UiKitIconsTest.php` fails on any name in the views that does not resolve.

- Production: `php artisan icons:cache` (also run by `php artisan optimize`) writes the manifest to `bootstrap/cache/blade-icons.php`; `icons:clear` removes it. The package's per-icon `<x-lucide-*>` components are switched off in `config/blade-icons.php`.
- Upgrading: `1.24.0` jumps to Lucide `0.553.0` and redraws 27 icons in use. Compare each used icon with the React app before moving the pin. Details: `docs/components/copy-and-icons.md`.
