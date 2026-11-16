---
name: tall-ui-kit
description: Build Laravel Livewire pages and components with the private TALL UI kit (olotintemitope/tall-ui-kit, used by NewBanger) - x-ui.* Blade components, x-data-table, WithDataTable, server-rendered charts, Tailwind 4 tokens and type-* roles, neutral and NewBanger themes, each with light and dark colour schemes. Use whenever writing or reviewing Blade/Livewire UI in an app that has x-ui components or the tall-ui-kit package, including admin pages, forms, tables, dialogs, toasts, charts and sidebars, and when checking a screen against the kit rules (no AI look, contrast in light and dark, focus, no native select, below-only popups, tables never scroll the page, one-line pills), or adding dark mode and the theme toggle.
---

# TALL UI kit

A flat, dense, accessible kit for Laravel 12 and 13, Livewire 4.4, Alpine and Tailwind 4. Components are anonymous Blade components named `x-ui.*` and `x-data-table.*`. Look and feel: Stripe, Linear, Vercel. Hierarchy comes from the `type-*` roles, never from boxes, fills or icons.

## Before writing UI

1. Check the app uses the kit: `vendor/olotintemitope/tall-ui-kit` exists, or `resources/views/components/ui/` exists in the app (NewBanger still carries its in-repo copy). Use whichever is there; the names and props are the same.
2. Read the props of the component you need in [references/components.md](references/components.md) (the full table) or the component's own Blade file: every file starts with a `{{-- --}}` comment listing its props and slots. Never guess a prop. In the kit repo the docs site has a page per component with live examples, generated props, keyboard map and Do/Don't (see Docs site below).
3. Build only from kit components, tokens and type roles. If something is missing, compose it from kit parts; do not hand-write a new look.

## Hard rules (a screen that breaks one is wrong)

- **No AI look.** Banned: thick or coloured edge borders (accent bars on alerts, cards, tabs, toasts), icon-in-a-tile or centred hero empty states, coloured icon badges, `rounded-2xl`+, heavy, stacked or coloured shadows, glows, blur or glass, gradients, KPI card grids with icons, donut/pie/gauge/progress-ring charts, gradient area fills, delta chips with arrows in tinted pills, medals, emoji, filler copy ("Insights", "at a glance", "Glad to see you again"), em dashes in user-facing copy (use commas, colons or full stops).
- **Borders are 1px.** Only the 2px focus outline is thicker.
- **Tokens only:** colours `surface` (the page), `card` (panels), `popover` (menus, dialogs, toasts), `ink`, `ink-body`, `ink-muted`, `primary`, `primary-text`, `on-primary`, `secondary`, `on-secondary`, `line`, `control`, `focus`, `surface-subtle`, `surface-muted`, `page-muted`, `panel-border`, `panel-footer`, `chart-*`; status colours via kit components or the status tokens (`danger-text`, `success-text`, `{danger,success,warning,info}-{strong,tint,edge}`). No hex, no `gray-*`, no `bg-white`/`text-white`/`bg-black/NN`, no Tailwind `red-*`/`emerald-*`/`amber-*`/`blue-*`, no arbitrary shadows or radii. Every token has a dark value, so token-only screens work in dark mode with no `dark:` classes. Text uses `type-title|heading|subheading|label|body|body-strong|field-label|meta|caption|figure|numeric`.
- **Contrast:** text 4.5:1, boundaries and marks 3:1, every state, in light and dark, no exemptions. Orange/accent text is `primary-text`, never `primary`. Disabled never uses opacity.
- **Dark mode:** `<x-ui.head />` goes before the stylesheet (it sets `html[data-color-scheme]` before first paint: no flash); the switch is `x-ui.theme-toggle` (Light / Dark / System), never a hand-made one. Dark is a calm near-black with 1px borders carrying edges: no glow, neon, gradients or heavy shadows. Logos: `brand.logo_dark` or `x-ui.logo`. Emails and PDFs stay light ([references/theming.md](references/theming.md), Dark mode).
- **Focus:** `focus-ring` on custom controls; focus never falls to `<body>`; after deletes move focus to the next item, then the previous, then the list heading.
- **No native `<select>`.** Use `x-ui.select` (or `x-ui.combobox` to filter by typing).
- **No native date or time inputs.** Never `type="date|time|month|week|datetime-local"` (not even on `x-ui.input`): use `x-ui.date`, `x-ui.datetime`, `x-ui.time-input`, `x-ui.month`, `x-ui.week` or `x-ui.date-range`. Values stay ISO (Y-m-d, H:i, Y-m, 2026-W47).
- **Popups open below** their trigger, never flipped above (`x-ui.select`, `x-ui.dropdown`, `x-ui.combobox`, `x-ui.popover` and the date pickers already do; do not add `top` placements except deliberately, e.g. an account menu at the foot of a sidebar).
- **Tables never push the page sideways** at any width. List pages: `x-data-table` with `tableLayout()` = `'stack'`. Plain `x-ui.table` scrolls inside its own box. Check 1440, 1024, 768, 390, 320.
- **Text is never cut off:** no `truncate`, `text-ellipsis`, `line-clamp`, fixed heights on text; long strings use `wrap-anywhere` (or `wrap-break-word` in cells). File names above all: they wrap, never ellipsis.
- **Pills** (`x-ui.badge`, the only `rounded-full`) are status badges only: one line, sized to the label, never wrapped or clipped.
- **Toasts sit above modals** automatically; place `x-ui.toaster` once in the layout. Toasts never take focus.
- **Forms are elevated:** tinted page (`x-ui.form-page` or a `page-muted` surface) with white `x-ui.panel`s. Data pages are flat white with 1px rules. Never a panel on plain white or inside a dialog; never nest cards.
- **URLs use UUIDs, people see short references.** Build web links with `route('name', $model)` bound by `{model:uuid}`; show records by a short reference (`TXN-9F3C21AB`) in the numeric/mono role. Never put numeric ids in web URLs.
- **Motion comes from the kit.** Put `wire:transition.navigate` on the layout's `<main>` only (the main region cross-fades on `wire:navigate`, the shell holds still); charts draw in once by themselves (`motion="none"` opts out). Never add page, chart or idle animations of your own; only transform and opacity animate, and reduced motion is instant ([references/motion.md](references/motion.md)).
- **Shell pages change with `wire:navigate`.** The sidebar's items, logo and Settings navigate in place by default; give the shell's `x-ui.sidebar` a `persist` key that changes with its items, so it survives page changes. Plain `wire:navigate`, never `.hover` (prefetch storms). External links, area switches, log out, downloads and payment hand-offs stay full loads (`reload` on an item). Page scripts must work after `livewire:navigated`: never hold `<body>` or a page element in module code ([references/navigation.md](references/navigation.md), Page changes).
- **Files are data, and user input.** File names are escaped and wrap; every id, name and folder id from the browser is checked on the server; previews load only URLs the app serves (never fetched on the server); infected or blocked files never offer Download.
- **User text is escaped, then linked.** Chat messages, posts and comments go through `TallUiKit\Support\RichText` (the components do it): never `{!! !!}` user text. Sends, replies, votes and follows are one Livewire call each and idempotent on the server; return `false` to roll an optimistic change back.
- **Livewire performance:** public properties hold ids and scalars; `wire:key` on every looped row; `wire:model.live.debounce.300ms` only where results update live; no `wire:poll` without a reason (30s+, `.visible`); no Alpine component per item in long lists.

## Shared prop names and sizes

- Sizes are one scale on every control and button: `size="sm"` 32px, default 36px (`md` is an alias), `lg` 40px. Use the same size for an input and the button beside it.
- Status colour: `variant` and `tone` are interchangeable on `x-ui.badge`, `x-ui.alert` and `x-ui.toast` (and the toast store); `danger` and `error` mean the same tone. Alerts have `info`.
- `label`, `hint`, `error`, `disabled` mean the same everywhere: `x-ui.checkbox` takes `hint` (alias of `description`) and `error`; `x-ui.radio-group` takes `error`; `x-ui.fieldset` takes `label`/`hideLabel` (aliases of `legend`/`hideLegend`). Heading levels: `headingLevel` (also on `x-ui.notification-group`), `x-ui.page-header` `level` (1 by default; 2 when the page already has its h1). Tooltips take `placement` (alias of `position`).
- Old names keep working; prefer the shared names in new code.

## Building a page

Data page (flat, white):

```blade
<x-ui.page-header title="Payouts" description="Requests waiting for review.">
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('open-dialog', 'export')">Export</x-ui.button>
        <x-ui.button wire:click="approveAll" loading="approveAll">Approve all</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.stat-strip :columns="3">
    <x-ui.stat label="Waiting" :value="$waiting" />
    <x-ui.stat label="Value" :value="$value" money />
    <x-ui.stat label="Oldest" :value="$oldest" sub="days" />
</x-ui.stat-strip>

<x-data-table :table="$table" />
```

Form page (tinted, panels):

```blade
<x-ui.form-page>
    <x-ui.form wire:submit="save" guard>
        <x-ui.form-section title="Payouts" description="Where and when we pay you.">
            <x-ui.panel>
                <x-ui.field label="Payout day" name="payoutDay" required>
                    <x-ui.select wire:model="payoutDay" :options="['mon' => 'Monday', 'fri' => 'Friday']" />
                </x-ui.field>
                <x-slot:footer>
                    <x-ui.form-actions>
                        <x-ui.button variant="ghost" type="button" x-on:click="$dispatch('form-reset')">Discard</x-ui.button>
                        <x-ui.button type="submit" loading="save">Save changes</x-ui.button>
                    </x-ui.form-actions>
                </x-slot:footer>
            </x-ui.panel>
        </x-ui.form-section>
    </x-ui.form>
</x-ui.form-page>
```

Then: success toast from the server (`$this->dispatch('toast', variant: 'success', title: 'Saved.')`); confirmations with `x-ui.confirm` / `x-ui.confirm.destructive` and an `action` method (verbs, never Yes/OK); empty states are one meta line plus an optional action (`x-ui.empty-state`); loading uses the matching skeleton preset, `x-ui.loading-overlay` for refreshes, the button's `loading` for actions. Never two loaders on one region.

More recipes (list page with filters, detail page, dashboard section, dialog form, wizard, settings): [references/patterns.md](references/patterns.md).

## Which component

| Need | Use |
|---|---|
| Page title and actions | `x-ui.page-header` |
| Numbers in a row | `x-ui.stat-strip` + `x-ui.stat` (3+ metrics); `x-ui.card.summary` for one |
| A list of records with search, sort, filters, bulk actions | `x-data-table` + `WithDataTable` ([references/data-table.md](references/data-table.md)) |
| A small static table | `x-ui.table` (+ `.th`, `.row`, `.td`) |
| One record's fields | `x-ui.card.detail`, `x-ui.dl`, `x-ui.kv-list` |
| Switch views of one subject | `x-ui.tabs`; period or table/chart of the same data: `x-ui.segmented` |
| Choices | `x-ui.select`, `x-ui.combobox`, `x-ui.radio-group` (5 or fewer visible), `x-ui.checkbox`, `x-ui.switch` |
| Dates and times | `x-ui.date`, `x-ui.datetime`, `x-ui.time-input`, `x-ui.month`, `x-ui.week`; a period: `x-ui.date-range wire:model.start.live="from" wire:model.end.live="to"` (presets built in). Typed text is parsed; keyboard per the APG date picker ([references/forms.md](references/forms.md), Dates and times) |
| Menu of actions | `x-ui.dropdown` + `.item`, `.separator`, `.label` |
| Extra detail on demand | `x-ui.popover`; hint text: `x-ui.tooltip` |
| Modal work | `x-ui.dialog`, `x-ui.dialog.form`, `x-ui.dialog.steps`, `x-ui.sheet` |
| Feedback | `x-ui.alert` inline, toasts for results, `x-ui.error-summary` in forms |
| Onboarding, setup, verification or launch steps | `x-ui.checklist` with `items` as data (label, hint, done, optional, href or action, actionLabel); `name` + `persist` (local, or server via the bound `ChecklistStore` and `WithChecklist`); `collapsible`, `dismissible` ([references/feedback.md](references/feedback.md) "Checklist") |
| Steps by name (onboarding, a wizard header, a verification flow) | `x-ui.stepper` (`steps`, `current`, states complete, current, upcoming, error; `clickable`, `action`; horizontal wraps to vertical); a compact "Step 3 of 5": `x-ui.progress-steps` |
| Activity feed | `x-ui.timeline` (`items` with time, actor, text, status, body; `groupByDay`, `limit` for "Show more") |
| Several short values in one field | `x-ui.tag-input` with `wire:model` on an array (`suggestions`, `max`, `pattern`) |
| People on a record | `x-ui.avatar-group` (`people`, `max`; the name lists everyone) |
| Let users reorder | `x-ui.sortable` (`items`, `action`: one Livewire call with the new keys; drag, touch and keyboard) |
| A custom range, money or two thumbs | `x-ui.slider` (`dual`, `format`); a plain native range: `x-ui.range` |
| A page-wide announcement | `x-ui.banner` (`tone`, `dismissible`, `name` + `persist` via `DismissStore` / `WithDismissals`); never a toast for this |
| Nested folders or categories | `x-ui.tree` (`nodes`, `selectable` or `checkable`, `lazy` children through a renderless Livewire method) |
| Progress | `x-ui.progress` (`size`, `tone`, `format` percent, count or bytes, `buffer`), `x-ui.progress-ring` (where a bar does not fit; never a KPI), `x-ui.upload-progress` (+ `.list` for Livewire uploads) |
| Charts | `x-ui.chart` / `x-ui.card.chart`: line, bar, stacked-bar, bar-horizontal, sparkline only; up to 3 series |
| Money and numbers | `x-ui.money` (privacy-aware), `x-ui.number`, `x-ui.delta`; `TallUiKit\Support\Format` in PHP |
| Icons | `x-ui.icon name="..."` (Lucide names); icon-only buttons need `aria-label` |
| A conversation, a message box, an inbox | `x-ui.chat` (`messages` as data, `sendAction` gets the text and an idempotency key, `olderAction`, `typing`), `x-ui.chat.composer`, `x-ui.chat.inbox` ([references/community.md](references/community.md)) |
| Feed, comments, reactions, polls | `x-ui.post` (optimistic `likeAction`, `more` dropdown slot), `x-ui.comment-thread` (`depth`, `replyAction`), `x-ui.reactions` (picker below), `x-ui.poll` (results as progress bars) |
| People, channels, rankings | `x-ui.member-card`, `x-ui.profile-header` (Follow toggle), `x-ui.channel-list`, `x-ui.leaderboard` (rank change in words, your row highlighted; no medals or avatars) |
| Browse and manage files and folders | `x-ui.file-manager` (`items` as data, `breadcrumbs`, `folders` for Move; `openAction`, `renameAction`, `moveAction`, `deleteAction`, `createFolderAction`, `downloadAction`, `sortAction`, `loadMoreAction`; `upload` for drag-and-drop; grid or list kept per user; one listbox, no Alpine per item, toolbar and dialogs sent once) ([references/files.md](references/files.md)) |
| Show one file | `x-ui.file-preview` (`file` with the app's URLs; image zoom, PDF, audio, video, text with a cap, fallback; `variant` panel, dialog, sheet; `files` + `navigateAction` for Previous / Next) |
| A file row, card or chip; a list of attachments | `x-ui.file-card` (type label, wrapping name, size, status pill uploading/done/failed/infected/blocked, Download/Cancel/Retry/Remove), `x-ui.attachment-list` (`items`, `variant` row/card/compact, action names called with the key) |
| Choose files | `x-ui.file` (`wire:model`; additive `maxSize`, `maxFiles`, `maxTotalSize` with per-file errors and a written hint, `reorder` + `reorderAction`, `paste`, `capture`, `chunked` for very large files through `TallUiKit\Uploads\ChunkedUploads::routes()`, resumable); progress `x-ui.upload-progress.list` |
| Light / dark switch | `x-ui.theme-toggle` (`label`, `size`, `iconOnly`); `WithColorScheme` + a bound `ColorSchemeStore` to keep it per user; the logo: `x-ui.logo` |

## Docs site and audit rules

- In the kit repo, `composer docs:build` renders `site/` from the workbench (served live at `/docs` by `composer serve`): Getting started, Tokens (colours with contrast in all four theme and mode sets, type, spacing, motion), Patterns (forms, tables, dialogs, onboarding checklist, dashboards), Rules, Audit rules, Changelog, and one page per component family (`workbench/app/Docs/Catalog.php` maps every Blade file to a page; examples in `workbench/resources/views/docs/examples/<slug>/`, notes in `workbench/resources/docs/<slug>.php`). A new component needs a Catalog entry, at least one example and a notes file, or `DocsSiteTest` fails. Browser check: `node scripts/docs-check.mjs`.
- Audit rules every component is held to ([references/audit.md](references/audit.md)): the one size scale; spacing on the 4px grid; radii sm/md/lg/full only; 1px borders; type roles; 16px icons with a 2px stroke; focus on `:focus-visible`; disabled never by opacity; loading keeps the name and sets `aria-busy`; invalid is a `danger` edge plus `aria-invalid` and a caption linked by `aria-describedby`; empty states are one meta line; Escape inside a popup never closes the dialog around it (`preventDefault` as well as stop); tooltips describe the control, stay while hovered and close on Escape (WCAG 1.4.13); visible text is in the accessible name (2.5.3); every drag has buttons as an alternative (2.5.7); focus never falls to `<body>`. Breaking API changes are deferred, never made in a minor: add an alias instead.

## References (load when needed)

- [references/rules.md](references/rules.md): every rule in detail (borders, radii, cut-off text, contrast, states).
- [references/components.md](references/components.md): every component with props and slots, typography components, icons.
- [references/tokens.md](references/tokens.md): colour tokens with ratios (light and dark values), type roles, spacing, radii, elevation, states matrix.
- [references/forms.md](references/forms.md) and [references/html-elements.md](references/html-elements.md): form layouts, native inputs, media, embeds.
- [references/data-table.md](references/data-table.md): `WithDataTable`, `Column`, `Filter`, `Action`, layouts, focus, performance.
- [references/overlays.md](references/overlays.md), [references/feedback.md](references/feedback.md), [references/navigation.md](references/navigation.md), [references/data-display.md](references/data-display.md): dialogs, loading, toasts, notifications, tabs, sidebar, cards, charts.
- [references/workflow.md](references/workflow.md): stepper, timeline, tag input, avatar group, sortable, slider, banner (DismissStore), tree, and the progress family.
- [references/community.md](references/community.md): chat, composer, inbox, post, comment thread, reactions, member card and profile header, poll, channel list, leaderboard; `RichText` (escape, then link).
- [references/files.md](references/files.md): file manager, file preview, file card and attachment list; [references/forms.md](references/forms.md) "File" for the upgraded `x-ui.file` and chunked uploads (`ChunkedUploads`, the endpoint contract, `find($id)->store()`).
- [references/accessibility.md](references/accessibility.md), [references/performance.md](references/performance.md), [references/motion.md](references/motion.md), [references/theming.md](references/theming.md), [references/javascript.md](references/javascript.md).
- [references/patterns.md](references/patterns.md): page recipes.
- [references/audit.md](references/audit.md): the November 2026 audit: findings, fixes, prop aliases, deferred breaking changes and the audit rules.

## Before you hand over

Check the screen against the hard rules above, then: no console errors; no page-level sideways scroll at 1440, 1024, 768, 390, 320; axe-core clean (WCAG 2.2 AA + best-practice); keyboard path works (Tab order, Escape closes, focus returns); long names, emails and titles wrap; empty, loading and error states exist. Check dark too: the screen in the dark scheme has no hard-coded light colour and no console errors. In the kit repo: `composer test`, `composer contrast` (four sets: each theme in light and dark), `npm run a11y` (both schemes), `composer docs:build` and `node scripts/docs-check.mjs` (axe on every docs page).
