# Navigation

## Tabs

`x-ui.tabs` switches between views of one subject. One look: the active tab is ink text with a 1px ink underline (15.9:1) over the 1px `line` rule; inactive tabs are ink-muted and darken on hover. No boxed, pill or filled tabs and no orange.

- **Text** (`key => label`): the default.
- **With icons** (`key => ['label' => , 'icon' => ]`): a 16px icon left of the label, 6px gap, the icon follows the text colour. For areas whose words need a cue (Inbox, Wallet, Team). Tabs are never icon-only; use `x-ui.segmented` for that. Either every tab has an icon or none does (throws in local and testing).
- **With counts** (`'count' => 12`): a numeric-role count after the label in ink-muted, e.g. "Pending 12". Add `'badge' => 'danger'` (or `neutral`) only when the count needs attention, e.g. late items; it renders an `x-ui.badge size="sm"`.
- `'disabled' => true` greys a tab out; the arrow keys skip it.
- **Links** (`'href' => ` on every tab): navigation between pages, rendered as a `<nav>` of links; the active one (`active`, or the tab whose href is the current URL) has `aria-current="page"`. Mixing link and plain tabs throws in local and testing.
- **Tablist** (no href): `role="tablist"`, `tab` and `tabpanel` with `aria-selected`, `aria-controls` and `aria-labelledby`; roving tabindex; Left and Right move, Home and End jump, and the focused tab is selected (automatic activation). Bind the active key with `wire:model` / `x-model`; put `x-ui.tabs.panel name=""` elements in the slot.
- Props: `tabs`, `active`, `size` (default h-10; `sm` h-9 for card headers), `label` (accessible name).
- Narrow screens: the row scrolls sideways with no fade, arrows or "more" menu, and the active tab scrolls into view.

Use tabs to switch content in place or between sibling pages; use `x-ui.segmented` to switch a view or period of the same data (table or chart, 7d or 30d).

## Sidebar

`x-ui.sidebar` and its parts (`resources/views/components/ui/sidebar/*`). Class strings per variant and the active-link rule live in `TallUiKit\Support\SidebarTones` (`tones($variant)`, `isActive($href)`; hrefs listed in `tall-ui-kit.sidebar.exact` match exactly, others also match their sub-pages). The item lists are app data: keep one source for each area's items (NewBanger's `App\Support\Navigation`) so the sidebar, the mobile drawer and a Cmd+K palette never drift. Logo: `tall-ui-kit.brand` (`logo_light`, `logo_dark`, `name`) or the header's `logo` slot.

| Component | Props and slots |
|---|---|
| `x-ui.sidebar` | `variant` dark, light, inset; `collapsible` (true); `area` (persists the collapse as `localStorage.<prefix>sidebar_<area>`, applied before paint by `<x-ui.head :area="...">`); `fixed` (true: the shell rail plus a mobile `<dialog>` drawer; false: a static block for previews); `collapsed` (static previews); `label`. Slots `header`, `nav`, `footer` |
| `x-ui.sidebar.header` | `home` (logo link), `search` (the Cmd+K trigger, true, dispatches `open-command-palette`), `searchLabel`; slot `logo`. Logo from `tall-ui-kit.brand` (name as text when no logo is set), collapse toggle (rail) or Close menu (drawer) |
| `x-ui.sidebar.section` | `label` (label role, small caps), `key`, `collapsible` (a disclosure with a turning chevron and `x-collapse`, open state persisted as `<prefix>sidebar_<area>_<key>`), `open` |
| `x-ui.sidebar.item` | `href`, `icon`, `label` (or slot), `active` (null: `SidebarTones::isActive`), `count` (muted mono number), `badge` + `badgeVariant` (an `x-ui.badge`), `new` (a "New" text tag), `disabled` + `reason`, `nested`, `external` (new tab with `rel="noopener noreferrer"`, a 14px arrow and "(opens in new tab)", never active, left out of the palette: WhatsApp support) |
| `x-ui.sidebar.group` | `label`, `icon`, `active` (null: a child in the slot is current), `open` (follows active), `count`. One level deep |
| `x-ui.sidebar.account` | `name`, `email`, `role`, `initials` (or `user`: any object with `name` or `first_name`/`last_name`, `email`, `initials()`), `areas` (list of `key`, `label`, `href`; a switcher shows with more than one), `current`, `settingsHref` (null hides Settings), `logoutAction` (null: `route('logout')` when it exists) |

Behaviour: rows are at least 32px and grow when a label wraps; the active item has a subtle fill, full-strength text at 500 and a 1px inset ring, never an accent bar; a group parent shows active (full-strength text) when a child is, and takes the fill in the icon rail; disabled items stay legible (no opacity), lose the link, get a dashed underline and `aria-describedby` to their reason; the nav scrolls inside while the header and account block stay pinned; Up and Down move between items (Home and End jump); collapse animates the width (150ms); the icon rail hides labels, names each link with `aria-label` and shows a tooltip on hover and focus; below `lg` the drawer is a native `<dialog>` (focus trap, Escape, scrim, slides in) opened by the top bar's menu button (`open-sidebar-drawer`); focus mode hides the sidebar from `lg`. The `rail:` variant styles the collapsed state (shell: `html[data-sidebar=collapsed]` from `lg`; static: its own `data-collapsed`).

Contrast, measured per state by `TallUiKit\Support\SidebarContrast` (shown in the gallery, checked by `tests/Feature/SidebarTest.php`):

| State | Dark `#212322` | Light `panel-footer` | Inset `page-muted` |
|---|---|---|---|
| Item text | 8.40 | 5.61 | 5.47 |
| Item text, hover (on its fill) | 13.29 | 13.87 | 13.51 |
| Active item text (on its fill) | 11.64 | 13.26 | 12.91 |
| Active ring against the sidebar (3:1) | 3.72 | 4.76 | 4.64 |
| Section label, count, disabled text, account role | 7.44 | 5.61 | 5.47 |
| Icon (3:1) | 6.56 | 5.61 | 5.47 |
| "New" tag | 15.81 | 15.15 | 14.77 |

## Focus mode

Shell feature (the toggle button and palette command live in your shell; the `focusMode` store, the shortcut and the CSS are the kit's). From `lg`, a 32px icon button in the top bar (tooltip "Focus mode" / "Exit focus mode" with the shortcut in `x-ui.kbd`, `aria-pressed`), Cmd+. / Ctrl+. and a "Focus mode" command in the Cmd+K palette toggle it. When on: the sidebar is hidden, the top bar is 44px with only the breadcrumb, the exit button and the account control, regions marked `data-focus-hide` are hidden, and the main content takes the full width. Escape exits when no dialog, menu or listbox is open and focus is not in a text field. Exiting or entering moves focus to the toggle and announces the change in a polite live region.

- State: `localStorage.<prefix>focus_<area>` (`on` / `off`), read and written in try/catch, applied before first paint by `<x-ui.head :area="...">` as `html[data-focus="on"]`, then managed by the `focusMode` Alpine store in `resources/js/ui.js`. Off by default; it never turns on by itself.
- Hidden below `lg`: the button, the palette command and the shortcut do nothing there, and the CSS only applies from `lg`.
- Motion: 150ms width and padding transitions, none under `prefers-reduced-motion`. No dimming or overlay.
- Pages: put `data-focus-hide` on secondary regions (the admin overview marks its people and money columns). Collapse their grid with the `focus-mode` variant, e.g. `lg:focus-mode:block` on the grid and `lg:focus-mode:border-r-0` on the remaining column.

## Shells

The kit has no shell layout: the app composes one. NewBanger's three shells (a dark admin sidebar, light curator and musician sidebars) share a 56px top bar with a breadcrumb, a focus mode toggle (from `lg`), a privacy toggle, the notifications bell and an account control (32px icon buttons); `x-ui.sidebar` fed by one navigation source; and a Cmd+K / Ctrl+K / `/` command palette over the same destinations. A shell layout should: print `<x-ui.head :area="...">` in `<head>`, load the kit CSS and JS, place `x-ui.progress-bar` under the top bar and `x-ui.toaster` once at the end of `<body>`, give `main` `id="main"` and `tabindex="-1"` with a "Skip to content" link, and use the tinted `page-muted` surface for whole form pages.
# Dialogs, confirmations, menus and popovers

## Dialogs and confirmations

All modals are the native `<dialog>` opened with `showModal()` (`resources/views/components/ui/dialog/base.blade.php`, behaviour `Alpine.data('uiDialog')` in `resources/js/dialogs.js`): the top layer, an inert page and Escape come from the browser; the page does not scroll behind (`html:has(dialog[open])`); a 40% scrim with no blur; header and footer stay put while the body scrolls; `wire:ignore.self` keeps the open state through Livewire morphs.

| Component | Props |
|---|---|
| `x-ui.dialog` | `name`, `title`, `description`, `size` (or the older `width`) sm 400, md 560, lg 720, xl 960; `dismissible` (the backdrop closes it; simple dialogs only). Slots `header`, default, `footer` |
| `x-ui.dialog.form` | `action` (Livewire method), `submitLabel`, `cancelLabel`, `submitVariant`, `size`. A real `<form>` (Enter submits); a busy save; closing with edits asks "Discard changes?" (the one allowed nested modal) |
| `x-ui.dialog.steps` | `steps`, `action`, `submitLabel`; "Step 1 of 2" and Back / Continue; children marked `data-dialog-step="n"` |
| `x-ui.dialog.fullscreen` | edge to edge below 640px |
| `x-ui.sheet` | right side, `size` md 480 or lg 640, full width on phones |
| `x-ui.confirm` | `title` (a question), `description` (one consequence), `confirmLabel` (a verb, never Yes or OK), `action`, `params`, `variant` |
| `x-ui.confirm.destructive` | a red verb button, focus on Cancel, optional `confirmText` to type first |
| `x-ui.confirm.reason` | `reasons` (value => label; Other adds a required text field); the action receives the reason |
| `x-ui.alert-dialog` | one acknowledge button (`acknowledgeLabel`) |
| `x-ui.confirm-dialog` | the older name, same props and events, destructive by default |

Events: `open-dialog` / `close-dialog` with the name (or `{ name }` from `$this->dispatch('open-dialog', name: ...)`); confirmations also answer `open-confirm` / `close-confirm`; without an action they dispatch `confirmed` (`{ name, reason }` for reasons), forms and steps `dialog-submitted`, a discarded form `dialog-discarded`, the alert `acknowledged`.

`action` calls that method on the Livewire component that owns the confirm (`run('method', ...params)`; `.reason` passes the reason as the last argument). Fixed 2026-11-15: the Confirm button used to render `run()` with no method (a `Collection::implode` over `Js` objects), so every confirm with an `action` did nothing; `tests/Feature/ConfirmActionTest.php` pins it. Pages that worked around it with a `confirmed` listener can use `action` again.

Async: the action runs with the dialog busy (its button `aria-busy` with a spinner; Escape and × ignored). It closes when the method returns anything but `false` or a string; a string is shown inline in an `x-ui.alert` and focused; a form also stays open while a field is `aria-invalid`. A thrown error shows "That did not work. Try again." inline. A confirm for fields on the page (Record refund) that fails validation there closes and moves focus to the first invalid field. Success toasts come from the server method. Server actions re-check permissions; the dialog is only the UI.

Focus: the first field on open, else the first button (never ×); Cancel for destructive confirmations; on close the browser returns focus to the trigger, and `uiFocus.focusAfter` falls back to the page heading when it is gone. No nested modals except the discard confirm over a form dialog.
# Loading, toasts, notifications and checklists

## Loading

| Situation | Use | Tells assistive tech |
|---|---|---|
| First load of a region | A skeleton preset of the same height | The preset's one `role="status"` ("Loading ...") |
| Refresh while content is on screen | `x-ui.loading-overlay` | `aria-busy` on the region and one polite status from its spinner |
| An inline action | The button's `loading` state | `aria-busy` on the button |
| A background or long task, page navigation | `x-ui.progress-bar` (automatic), then a toast when done | The toast (polite) |
| A long list | `x-ui.load-more` | Its "Showing 20 of 134" status |

Never put two loaders on one region (no spinner on top of a skeleton, no overlay on a loading button's section).

- `x-ui.spinner`: `size` xs 12, sm 14, default 16, lg 20; a 1.5px ring, `ink-muted` track at 25%, `ink` arc (15.8:1), one turn every 0.7s; `tone="inverse"` (white) on dark fills; `role="status"` with a hidden `label` ("Loading"), or `:announce="false"` inside something that already says it is busy. Reduced motion: a slow opacity pulse.
- `x-ui.loading-inline`: spinner and a meta-role message ("Loading transactions") as one status.
- `x-ui.loading-overlay`: `target`, `label`, `busy`. Content stays untouched (no fading, no veil, no blur); after 150ms (`wire:loading.delay.short`) the region gets `aria-busy` and a 14px spinner on a small white chip (1px `line` border) pinned top-right.
- `x-ui.progress-bar`: in every shell under the top bar. A 2px `chart-1` bar (15.8:1), indeterminate, shown only after 200ms of a Livewire request (`Livewire.hook('request')`) or a `wire:navigate` visit, through the `progress` store in `resources/js/ui.js`. Livewire's own navigate bar is turned off (`config/livewire.php`). Forced colours: `CanvasText`. Reduced motion: a still bar that pulses.
- `x-ui.load-more`: `shown`, `total`, `action` (loadMore), `label`, `noun`, `auto` (also loads when the button scrolls into view, Alpine Intersect; the button stays as the keyboard and fallback path).
- Skeletons (`x-ui.skeleton.*`): `surface-muted` blocks with Tailwind's `animate-pulse`, none under reduced motion, no shimmer. Built from `x-ui.skeleton.line` (a bar in a line box of the role's line height) so each preset is the real component's height, checked in the browser side by side on the workbench gallery: `.text` (body lines), `.avatar` (36px avatar, name and meta, as the profile card), `.table` (36px header, 40px rows), `.stat` and `.stats` (a stat with its sub line), `.card` (the summary card), `.chart` (header, figure, plot at 160 or 120px; `x-ui.chart loading` uses the same blocks), `.grid` (summary cards in a grid), `.list` (`x-ui.card.row` rows).

## Toasts

`x-ui.toaster` is placed once in your base layout, after the page content. Store and API: `resources/js/toasts.js`.

- `x-ui.toaster`: `position` bottom-right (default), bottom-left, top-right; always bottom-centre on phones. `max` 3: the rest collapse behind an "N more" button. `duration` 4000ms.
- `x-ui.toast`: `variant` success, error, warning, info, neutral, loading; `title`, `description`, `action` (`['label' => 'Undo', 'method' => 'undo']` calls the dispatching Livewire component, `['label' => 'View', 'url' => '/admin/payouts']` navigates), `dismissible` (true), `duration` (0 sticky). `bind` renders the store-bound copy the toaster uses.
- Livewire: `$this->dispatch('toast', variant: 'success', title: 'Saved.', action: [...])`. Alpine: `$toast('Saved.')`, `$toast({...})`, `$toast.promise(promise, { loading, success, error })`. One store (`$store.toasts`): pushing an `id` that is shown updates it, which is how a loading toast resolves. Older calls (`type`, `message`, `$store.toasts.push`, `$dispatch('toast', {...})`, a `toast` session flash) keep working.
- Errors and loading toasts are sticky; timers pause on hover and focus. Success, info, warning, neutral and loading toasts are in a polite live region, errors in an assertive one. Toasts never take focus; Alt+T reaches the newest and focus returns when it closes (`resources/js/ui.js`).
- Over modals: every modal is in the top layer and makes the page inert, so while one is open the toaster's node moves into the topmost open modal `<dialog>` (nested modals: the last opened) and back to its placeholder when the last closes (`resources/js/toaster-layer.js`, the `uiToaster` Alpine data). Same node, moved in `Alpine.mutateDom`: queue, timers and live regions carry over; Livewire morphs keep it (`morph.removing` / `morph.updating`), and a dialog morphed away sends it home first. Not `popover`: a popover outside the modal is still inert. Inside a modal it stays `position: fixed` (moved in after the open transition, so the dialog is not its containing block) and keeps clear of the footer buttons: the configured corner if clear, else beside the dialog (desktop sheets), else the top (phones, full-height sheets). Check with `tests/Browser/toast-over-modal.mjs`.
- Look: a 1px tinted border, light fill and tone text (emerald-800 7.23, red-800 7.64, amber-900 8.73, blue-800 8.43, ink 15.15 on their fills), `shadow-popover`, no icon tile, no edge bar. Loading shows the kit spinner.

## Notifications

The kit ships the notification components only; the inbox (data, polling, marking read) is app code. In NewBanger a `NotificationService` feeds them and Livewire `Bell`, `Panel` and `Index` components render them; the props below are what any backend must supply.

| Component | Props |
|---|---|
| `x-ui.notification-bell` | `count`: a pill capped at "9+" (aria-hidden), name "Notifications, 3 unread" |
| `x-ui.notification-panel` | about 380px: heading, Mark all as read, All and Unread tabs, the list (a skeleton until loaded), View all. `tab`, `loaded`, `unread`, `viewAll` (null hides), `markAll`, `model` |
| `x-ui.notification-item` | `id`, `title` (body-strong; 600 while unread), `message`, `time` (relative, full timestamp in the `<time>` title and datetime), `read` (weight, a `primary-text` dot and "Unread:" for screen readers), `url`, `type`, `open` / `toggle` / `delete` (Livewire methods, "" hides), `selectable` |
| `x-ui.notification-group` | `label`, `level` |

## Checklist

`x-ui.checklist` (with `x-ui.checklist.item`) lists the steps of any onboarding, setup, verification, KYC or launch flow. It is generic: the page passes the steps, their state and their actions as data, and the kit knows nothing about the app's models, roles or routes. Gallery: workbench `/checklist`, both themes.

```blade
<x-ui.checklist
    title="Set up your workspace"
    description="A few steps before your team can start."
    name="workspace"
    persist="server"
    collapsible
    dismissible
    :items="[
        ['key' => 'account', 'label' => 'Create your account', 'done' => true],
        ['key' => 'invite', 'label' => 'Invite your team', 'hint' => 'Teammates get an email.', 'done' => $invited, 'action' => 'invite', 'actionLabel' => 'Invite'],
        ['key' => 'billing', 'label' => 'Add a payment method', 'done' => $hasCard, 'href' => route('billing'), 'actionLabel' => 'Add card'],
        ['key' => 'import', 'label' => 'Import existing data', 'optional' => true, 'done' => false, 'href' => route('import'), 'actionLabel' => 'Import'],
    ]"
/>
```

| Prop | Meaning |
|---|---|
| `title`, `description` | Header text (heading and meta roles). `headingLevel` (2) sets the tag |
| `items` | List of steps: `label`, `hint`, `done` (bool), `optional` (bool), `href` or `action` (a Livewire call, e.g. `"complete('invite')"`; `wire:click` is accepted as the key too) with `actionLabel`, `key` (stable, for `wire:key` and ids). Instead of `items`, put `x-ui.checklist.item` rows in the slot and pass `completed`, `total` and `next` on the right row |
| `name` | Persistence key: letters, digits, `_`, `:`, `-`, up to 64 characters (anything else throws) |
| `collapsible`, `collapsed` | A collapse button (`aria-expanded`, `aria-controls`, focus stays on it); `collapsed` sets the start state and overrides the store |
| `dismissible`, `dismissed` | Dismiss is offered only when every required step is done; a dismissed checklist renders nothing. `dismissed` counts only while every required step is done, so adding a required step brings it back |
| `persist` | `local` (default with a `name`): localStorage `<prefix>checklist_<name>`. `server`: the bound `ChecklistStore`, read on render and written through `$wire` by `WithChecklist` (outside Livewire it falls back to localStorage). `none`: nothing kept |
| `loading` | A skeleton of the same shape with one `role="status"` |
| `completeText`, `dismissLabel` | "You're all set." and "Dismiss" |

**Look.** A flat card (1px `line` border, 8px radius, no shadow). Header: title, description, "2 of 5 done" (meta) and `x-ui.progress` (4px track). Steps: an ordered list on 1px rules. Not done: an empty 16px circle in `control`. Done: a plain 16px check in ink, label in `ink-muted`, never crossed out (strikethrough hurts reading), and no action. Next (the first required step not done): a `surface-subtle` tint and a body-strong label, `aria-current="step"`. The action is a link button on the right, below the text on phones. All required steps done: a footer line ("You're all set.") and, with `dismissible`, an outline Dismiss button. Optional steps say "(Optional)" and never count. No icon tiles, gradients, glow, confetti or celebration. Contrast: the `Checklist` rows in `KitContrast` (next step's action `primary-text` on the tint 4.82:1 in the NewBanger theme, the circle on the tint 3.38:1).

**Accessibility.** A `<section>` named by its title; the list is `aria-describedby` the progress line; each step starts with a hidden "Done:" or "Not done:"; the action is described by its step's label. After a Livewire update turns a step done, a polite status names it ("Invite your team: done.", plus the complete line when the last required step lands), focus stays in that row (the row takes focus if its action, now gone, had it), and nothing jumps to the next step. After Dismiss, focus moves to the next control on the page, else the page heading, never `<body>`.

**Motion.** Only when a step turns done after a morph: the check scales in from 0.6 and fades in (`--motion-sm`, `ease-motion-out`, transform and opacity), and the bar's fill follows `x-ui.progress`'s width transition (`--motion-md`, its stable id keeps the element through the morph). Never on first render; instant under reduced motion and the gallery's reduced preview.

**Persistence contract.** `TallUiKit\Support\Checklist\ChecklistStore`:

```php
interface ChecklistStore
{
    public const KEY_PATTERN = '/^[A-Za-z0-9_:-]{1,64}$/';
    public function collapsed(string $key): bool;
    public function dismissed(string $key): bool;
    public function setCollapsed(string $key, bool $collapsed): void;
    public function dismiss(string $key): void;
}
```

The kit binds `SessionChecklistStore` (the session, no table) with `bindIf`, so an app's own binding wins. Keep it per user, for example in a preferences table:

```php
// AppServiceProvider::register()
$this->app->bind(ChecklistStore::class, UserPreferenceChecklistStore::class);
```

With `persist="server"`, add `TallUiKit\Livewire\Concerns\WithChecklist` to the Livewire component that renders the checklist. Its `checklistCollapse(key, collapsed)` and `checklistDismiss(key)` are renderless (a few hundred bytes per round trip). The key comes from the browser, so it is checked against `KEY_PATTERN` and, when you override `checklistKeys()`, against that list (422 otherwise). Override `checklistCanDismiss($key)` to refuse a dismissal the server does not agree with, e.g. while a required step is open (403). Apps that store state another way listen for the bubbling `ui-checklist` event, `detail: { name, action: 'collapse' | 'dismiss', collapsed }`.

**Performance.** Row markup is compact (no whitespace text nodes); one Alpine component per checklist, none per row. A round trip that flips a step re-renders only the component that owns it, so put a checklist in a small Livewire component rather than a large page component.
