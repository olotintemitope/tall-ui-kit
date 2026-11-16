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
| `x-ui.sidebar` | `variant` dark, light, inset; `collapsible` (true); `area` (persists the collapse as `localStorage.<prefix>sidebar_<area>`, applied before paint by `<x-ui.head :area="...">`); `fixed` (true: the shell rail plus a mobile `<dialog>` drawer; false: a static block for previews); `collapsed` (static previews); `label`; `navigate` (true: `wire:navigate` on the items, the logo and Settings); `persist` (a key, or true for `sidebar-<area>`: kept across `wire:navigate`, see Page changes); `exact` (hrefs active only on an exact match, default `tall-ui-kit.sidebar.exact`). Slots `header`, `nav`, `footer` |
| `x-ui.sidebar.header` | `home` (logo link), `search` (the Cmd+K trigger, true, dispatches `open-command-palette`), `searchLabel`; slot `logo`. Logo from `tall-ui-kit.brand` (name as text when no logo is set), collapse toggle (rail) or Close menu (drawer) |
| `x-ui.sidebar.section` | `label` (label role, small caps), `key`, `collapsible` (a disclosure with a turning chevron and `x-collapse`, open state persisted as `<prefix>sidebar_<area>_<key>`), `open` |
| `x-ui.sidebar.item` | `href`, `icon`, `label` (or slot), `active` (null: `SidebarTones::isActive`), `count` (muted mono number), `badge` + `badgeVariant` (an `x-ui.badge`), `new` (a "New" text tag), `disabled` + `reason`, `nested`, `external` (new tab with `rel="noopener noreferrer"`, a 14px arrow and "(opens in new tab)", never active, left out of the palette: WhatsApp support), `reload` (an ordinary link, a full page load, for a destination outside the shell's layout) |
| `x-ui.sidebar.group` | `label`, `icon`, `active` (null: a child in the slot is current), `open` (follows active), `count`. One level deep |
| `x-ui.sidebar.account` | `name`, `email`, `role`, `initials` (or `user`: any object with `name` or `first_name`/`last_name`, `email`, `initials()`), `areas` (list of `key`, `label`, `href`; a switcher shows with more than one), `current`, `settingsHref` (null hides Settings), `logoutAction` (null: `route('logout')` when it exists). Settings navigates in place; area switches and Log out are full page loads |

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

### Page changes

With `navigate` (the default) the sidebar's items, its logo and the account menu's Settings use `wire:navigate`: one fetch, `<body>` swapped, no CSS or JS re-parsed, and only `<main>` cross-fades (`wire:transition.navigate`, see [motion.md](motion.md)). The palette (`commandPalette`) goes the same way through `Livewire.navigate`; items with `reload: true` load in full. Use plain `wire:navigate`, not `.hover`: Livewire already starts the fetch on mousedown, and hover prefetch measured 19 full page renders (about 2.6s of server time) for one slow scan down an 18-item admin sidebar, against 0 without it.

- **Persist the shell sidebar.** Pass `persist` a key that changes with the items, e.g. `"sidebar-{$area}-".substr(md5(json_encode($sections)), 0, 12)` (NewBanger's `Navigation::persistKey`). The sidebar is wrapped in a plain `<div x-persist>` (no Alpine of its own: Livewire re-initialises the persisted element's own directives on every visit, which would leave its children bound to a stale scope), so the rail, the drawer, their Alpine state (collapse, open sections, tooltips) and the nav scroll (`wire:navigate:scroll`) survive. A page whose items differ (a new badge count, other capabilities) gets a fresh sidebar.
- **`resources/js/navigate.js`** runs inside Livewire's swap (`onSwap` of `livewire:navigating`, before the view transition's snapshot, so nothing flashes): it puts back the `<html>` `data-*` attributes Livewire drops (collapse, focus mode, privacy, last input, motion preview); re-marks the current item from the URL with `SidebarTones::isActive`'s rule (items with `active` left null carry `data-nav-match`; `data-nav-exact` and `data-nav-tones` on the sidebar root); restores the window scroll on back and forward (Livewire restores `<body>`, which never scrolls in a standards-mode page); and loads a page from another area in full (`[data-sidebar-area]` changed: the sign-in page after the session ended, another shell through history). `window.uiNavigate.syncSidebars()` re-marks by hand.
- **Keep as full page loads:** external links; area switches (another shell and sidebar); log out, impersonation and every form post; marketing pages, public links and file downloads; payment provider hand-offs and their return and callback routes.
- **Page scripts.** Alpine components and Livewire `@script` start again on each page. Module code must not hold `<body>` or page elements: observe `document.documentElement`, re-attach per-page watchers on `livewire:navigated`, and stop observers whose element is detached (the kit's indicator observer, toast focus return, deferred sections and the data table's resize observer do). A heading made focusable by the kit keeps its `tabindex` through morphs. Inline `<script>` in `<body>` runs again on each visit; scripts added to `<head>` stay.

## Shells

The kit has no shell layout: the app composes one. NewBanger's three shells (a dark admin sidebar, light curator and musician sidebars) share a 56px top bar with a breadcrumb, a focus mode toggle (from `lg`), a privacy toggle, the notifications bell and an account control (32px icon buttons); `x-ui.sidebar` fed by one navigation source; and a Cmd+K / Ctrl+K / `/` command palette over the same destinations. A shell layout should: print `<x-ui.head :area="...">` in `<head>`, load the kit CSS and JS, place `x-ui.progress-bar` under the top bar and `x-ui.toaster` once at the end of `<body>`, give `main` `id="main"`, `tabindex="-1"` and `wire:transition.navigate` with a "Skip to content" link, persist the sidebar (`persist`), and use the tinted `page-muted` surface for whole form pages.
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
- Look: a 1px tinted border, light fill and tone text (success-strong 7.23, danger-strong 7.64, warning-strong 8.73, info-strong 8.43, ink 15.15 on their fills), `shadow-popover`, no icon tile, no edge bar. Loading shows the kit spinner.

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

**Motion.** Only when a step turns done after a morph: the check scales in from 0.6 and fades in (`--motion-sm`, `ease-motion-out`, transform and opacity), and the bar's fill follows `x-ui.progress`'s scaleX transition (`--motion-md`, its stable id keeps the element through the morph). Never on first render; instant under reduced motion and the gallery's reduced preview.

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
# Workflow and progress components

Steppers, activity feeds, tag inputs, avatar groups, sortable lists, sliders, banners, trees and the progress family. Every one takes its content as data (or as child rows in the slot), knows nothing about the app's models or routes, and works with Livewire and Alpine. Gallery: workbench `/workflow`, both themes. Tests: `tests/Feature/WorkflowComponentsTest.php`. Contrast: the "Workflow" and "Progress" rows in `TallUiKit\Support\KitContrast` (`composer contrast`).

JavaScript: `resources/js/widgets.js` (imported by `index.js`) registers the tag input, banner, avatar group and upload list, and loads the slider, tree and sortable behaviour lazily (`slider-core.js` 1.4 KB, `sortable-core.js` 2.0 KB, `tree-core.js` 2.5 KB gzip, fetched only when a page has one). No dependency was added: sorting uses native pointer events.

| Need | Use |
|---|---|
| Steps the user sees by name, can go back to, or that can fail | `x-ui.stepper` |
| A compact "Step 3 of 5" with no step names needed | `x-ui.progress-steps` |
| One step per screen in a form | `x-ui.form-layout.wizard` (its header is `x-ui.stepper`) |
| Setup or onboarding tasks done in any order | `x-ui.checklist` |
| Progress of one task | `x-ui.progress`; where a bar does not fit, `x-ui.progress-ring` |
| A file upload | `x-ui.upload-progress` (one row), `x-ui.upload-progress.list` (Livewire uploads) |
| A value in a range, a native control is fine | `x-ui.range` (two native inputs; unchanged) |
| A value in a range with a custom look, money formatting, or two thumbs that must not cross | `x-ui.slider` |
| A message for the whole page that stays | `x-ui.banner`; for one form or section, `x-ui.alert`; for the result of an action, a toast |

## Stepper

`x-ui.stepper`: a `<nav>` (named by `label`) holding an `<ol>`. Steps are strings or `['label', 'description', 'state', 'href', 'action', 'key']`; `state` is complete, current, upcoming or error, and `current` (1-based) fills in states that are not given.

```blade
<x-ui.stepper label="Set up payouts" :current="3" clickable action="goTo" :steps="[
    ['label' => 'Account', 'description' => 'Email and password.'],
    ['label' => 'Bank account', 'state' => 'error', 'description' => 'The name did not match.'],
    ['label' => 'Review'],
    ['label' => 'Go live'],
]" />
```

| Prop | Meaning |
|---|---|
| `steps`, `current` | The steps; the current step (1-based) |
| `variant` | `default` (markers) or `inline` (the wizard's text list "1 Song · 2 Targets Done ·", "Step 2 of 5" below `sm`) |
| `orientation` | `horizontal`: a row once the stepper's own box is 42rem wide (a container query, so it follows the panel, not the window), stacked below that; `vertical`: always stacked |
| `clickable` | `false`; `true` (complete and error steps); `"all"` (every step but the current one) |
| `action` | Livewire method called with the step number (`goTo(2)`); a step's own `href` or `action` wins; without either, a bubbling `ui-step` event, detail `{ step, key }` |
| `label`, `id` | The nav's name ("Progress") |

**Look.** 24px markers: complete a 1px ink ring with an ink check, current an ink disc with a white number (15.81:1), upcoming a 1px `control` ring with an ink-muted number (3.65:1 and 5.85:1), error a 1px danger-text ring with an x, the label and description in danger-text (6.42:1). Labels in body (current in body-strong), descriptions in meta. A 1px `line` rule joins steps. No fills besides the current disc, no progress bar.

**Accessibility.** `aria-current="step"` on the current `<li>`; every label starts with a hidden "Completed:", "Current:", "Upcoming:" or "Error:", so state never rests on colour or the marker. Clickable steps are buttons or links with the focus ring; the rest are text.

Do: keep labels short and say what failed in the error step's description. Don't: make upcoming steps clickable when they depend on earlier ones; use it for unordered tasks (use `x-ui.checklist`).

## Timeline

`x-ui.timeline`: an activity feed. Data mode: `items` of `['key', 'time', 'actor', 'avatar', 'text', 'status', 'body', 'attachment']`, where `status` is a label or `[label, variant]` (an `x-ui.badge` variant) and `attachment` is `['name', 'href', 'meta']`.

```blade
<x-ui.timeline :items="$activity" group-by-day :limit="10" label="Payout activity" />
```

| Prop | Meaning |
|---|---|
| `items` | The entries, newest first |
| `groupByDay` | Day headings: "Today", "Yesterday", "14 Nov 2026" (in `tz`), one `<ol>` per day under an `h3` (`headingLevel`) |
| `limit` | Show the first N and a "Show N more" button (`moreLabel`, `:count` is replaced) that reveals the rest in place, no round trip; focus moves to the first new entry |
| `total`, `moreAction` | Server paging instead: an `x-ui.load-more` row calls the Livewire method |
| `timeMode` | `relative` ("2h ago", the full date in `title`) or `absolute` |
| `tz`, `now` | Time zone (Africa/Lagos); a fixed "now" for tests |
| `label`, `empty`, `id` | The list's name without day headings ("Activity"); "No activity yet." |

Slot mode: `x-ui.timeline.item` rows (props `time`, `actor`, `avatar`, `text`, `status`, `attachment`, `timeMode`, `tz`, `now`, `extra`; the slot is the body, e.g. a comment or a diff), optionally inside `x-ui.timeline.day label="Today"`. Mark rows `extra` and pass `limit` for "Show more".

**Look.** Flat: the actor's avatar (or a small `control` ring) on a 1px `line` rail, "**Actor** text" in body, the status pill on the same line (it drops below on narrow rows, never clipped), the time in meta, the body below. **Accessibility:** the time is `<time datetime>`; the reveal button has `aria-expanded` and `aria-controls`; hidden entries are `display: none` until revealed. One Alpine component per feed (only with `limit`), none per entry; give the feed a stable `label` (its id comes from it, so rows keep their `wire:key` when entries arrive).

Do: print what changed in the text ("approved the payout PAY-9F3C21AB"). Don't: use icons in coloured tiles as markers, or more than one pill per entry.

## Tag input

`x-ui.tag-input`: several short values in one field, bound as an array.

```blade
<x-ui.field label="Genres" name="genres" hint="Up to 5.">
    <x-ui.tag-input wire:model.live="genres" :value="$genres" :max="5" :max-length="24" :suggestions="$allGenres" />
</x-ui.field>
```

| Prop | Meaning |
|---|---|
| `wire:model` / `x-model` | The array (`x-modelable`); plain `wire:model` sends nothing until the next action, `.live` one request per change |
| `value` | The first paint (the binding arrives after Alpine starts) |
| `suggestions` | Strings or `['value', 'hint']`; the input becomes an APG combobox with a listbox below it |
| `max`, `maxLength` (50), `pattern`, `patternMessage`, `allowCustom` (true), `caseSensitive` (false) | Checked before a tag is added; the reason is said in a polite message under the field |
| `separator` (","), `addOnBlur` (true), `placeholder` ("Add a tag"), `size`, `width` (md), `invalid`, `label`, `name` (plain forms post `name[]`), `removeLabel` ("Remove"), `empty` ("No matches") | |

**Keyboard.** Enter or the separator adds; pasting "a, b, c" or one value per line adds each (one message: "Added 2 tags. 1 skipped: ..."); Backspace in the empty input removes the last tag; Left at the start of the input moves to the tags, Left and Right between them, Home and End to the ends, Backspace or Delete removes the focused tag, Right past the last or Escape returns to the input. With suggestions: Down and Up move, Enter picks, Escape closes. After a removal focus moves to the previous tag, else the next, else the input.

**Look.** The `x-ui.input` box with `surface-muted` chips (4px radius, never pills; ink 13.63:1, the x in ink-muted 3:1 or more), "3 of 5" in caption when `max` is set. Long tags wrap inside their chip. **Validate the array again on the server** (count, length, pattern): the browser check is a convenience. The gallery's `FieldsDemo::updatedGenres` does.

## Avatar group

`x-ui.avatar-group`: stacked `x-ui.avatar`s with a "+N" disc.

```blade
<x-ui.avatar-group :people="$reviewers->map(fn ($u) => ['name' => $u->name, 'src' => $u->photo_url])" :max="4" label="Reviewers" />
```

Props: `people` (names, or `['name', 'src']`), `max` (4; the "+N" disc replaces the last slot when two or more are hidden), `size` (xs, sm, default, lg; sm), `label` (prefix for the name), `focusable` (true), `id`.

**Accessibility.** One `role="img"` named after everyone ("Reviewers: Ada Obi, Bola Ade and 5 others" lists all of them), so screen readers hear every name. It takes focus, and hover or focus shows the same list as a tooltip (fixed, so scroll boxes never clip it; Escape hides it, WCAG 1.4.13). No Alpine per group: one delegated listener for the page (`avatar-group.js`), so a table full of groups costs nothing. Set `focusable` false when the group sits inside a link or button that names the people already.

## Sortable

`x-ui.sortable`: a list reordered by dragging or from the keyboard. The new order goes to Livewire in **one call**.

```blade
<x-ui.sortable label="Payment methods" action="reorder" :items="$methods" />
```

```php
public function reorder(array $keys): void
{
    // Keys come from the browser: accept only a permutation of the ones you rendered.
    abort_unless(collect($keys)->sort()->values()->all() === collect($this->order)->sort()->values()->all(), 422);
    $this->order = $keys;
}
```

Props: `items` (`['key', 'label', 'description']`, or `x-ui.sortable.item` rows in the slot: props `key`, `label`, `description`, `help`, `disabled`, slot for rich content), `action`, `label` (the list's name, "Reorder list"), `bordered` (true), `disabled`, `id`. Events: a bubbling `ui-sort` with `{ order, key, from, to }` on every change.

**Pointer and touch.** Press the 32px grip and drag; the row follows the pointer, the others slide aside (transform, `--motion-sm`), the window scrolls near its edges, and the row lands where it was dropped. The grip has `touch-action: none`, so a finger drag never scrolls the page; the rest of the row scrolls as usual. Escape during a drag puts it back.

**Keyboard.** Space or Enter on a grip picks the row up, Up and Down (Home, End) move it, Space or Enter drops it, Escape cancels, Tab drops it. A polite live region says each step ("Picked up Card. Position 1 of 5.", "Card moved to position 3 of 5.", "Card dropped. Position 3 of 5.", "Cancelled. Card is back at position 1 of 5."), and focus stays on the grip.

**Decision: no library.** SortableJS (MIT, about 13 KB gzip) and `@shopify/draggable` were considered; native pointer events cover mouse, pen and touch in about 2 KB, keyboard sorting needed custom code either way, and rows stay plain markup (no Alpine per row; one lazy module with delegated listeners serves every list). Keep long lists paginated; reorder within a page.

Do: give each row a stable `key` and keep `label` (it names the grip and the announcements). Don't: put the whole row under `touch-action: none`, or save after every step of a keyboard move (the kit sends once, on drop).

## Slider

`x-ui.slider`: a custom slider on the APG slider and multi-thumb slider patterns. `x-ui.range` stays as it is.

```blade
<x-ui.slider label="Budget" :min="5000" :max="100000" :step="2500" format="money" wire:model.live="budget" :value="$budget" bounds />
<x-ui.slider label="Followers" dual :min="0" :max="1000000" :step="10000" wire:model.live="followers" :value="$followers" />
```

| Prop | Meaning |
|---|---|
| `wire:model` / `x-model` | A number, or `[low, high]` with `dual`; committed on release and on each key press (one request per drag) |
| `min`, `max`, `step`, `pageStep` | Page Up/Down move `pageStep` (a tenth of the range by default) |
| `format`, `currency`, `decimals`, `unit` | `number`, `money` (NGN or USD, as `x-ui.money`), `percent`; the formatted value is printed and is each thumb's `aria-valuetext` |
| `value`, `bounds`, `disabled`, `hideLabel`, `name`, `minLabel`, `maxLabel`, `id` | First paint; min and max under the track; plain-form inputs (`name` or `name[]`) |

**Keyboard.** Right/Up +step, Left/Down -step, Page Up/Down, Home/End (to the lowest or highest the thumb may take). With two thumbs each one's `aria-valuemin`/`aria-valuemax` stops at the other's value, so they never cross. **Pointer.** Press anywhere on the track to move the nearest thumb there, then drag; `touch-action: none` on the track. **Look.** As `x-ui.range`: a 4px `control` track, the chosen part in ink, 20px white thumbs with a 1px `control` edge, the focus ring on the focused thumb; disabled: dashed track and thumbs, no opacity.

**Which to use.** `x-ui.range` is two native `<input type="range">` over one track: the browser's own keyboard, forms and assistive-tech support for free, uneven `steps` lists and the `minModel`/`maxModel` split binding. `x-ui.slider` is for a formatted value per thumb (money, units), an `[low, high]` array binding, two thumbs that must never cross, Page Up/Down steps you choose, and a click-anywhere track. Both commit once per drag.

## Banner

`x-ui.banner`: a page-level announcement in the page flow (top of `<main>`). Not a toast: it never takes focus, never stacks and stays until dismissed.

```blade
<x-ui.banner tone="warning" title="Payouts pause on 20 November." name="payouts-2026-11" dismissible
    action-label="Read the notice" action-href="{{ route('notices.show', $notice) }}">
    Requests made that day go out on 21 November.
</x-ui.banner>
```

| Prop | Meaning |
|---|---|
| `tone` | info (info-text on info-tint, 6.28:1), success (success-strong on success-tint, 7.23:1), warning (warning-strong on warning-tint, 8.73:1), danger (danger-strong on danger-tint, 7.64:1) |
| `title`, slot, `actionLabel`, `actionHref` | Title in body-strong, message in body, the action an underlined link in the same tone |
| `variant` | `page` (full width, 1px rule below) or `inset` (1px border, 6px radius, inside content) |
| `dismissible`, `name`, `persist`, `dismissed`, `dismissLabel` | See below |
| `label`, `id` | A banner with a title (or `label`) is a named region; without either it is a plain block, so untitled banners never repeat a landmark name |

**Persistence: the same store pattern as `x-ui.checklist`.** With a `name` (letters, digits, `_ : -`, up to 57 characters; put a version in it, "maintenance-2026-11"):

- `persist="local"` (default): localStorage `<prefix>dismiss_banner:<name>`, applied before first paint by a one-line inline script, so it never flashes.
- `persist="server"`: `TallUiKit\Support\Dismiss\DismissStore`, read on render and written through `$wire.dismissNotice('banner:<name>')` by `TallUiKit\Livewire\Concerns\WithDismissals` (renderless). Outside Livewire, or when the server refuses, it falls back to local.
- `persist="none"`: nothing kept.

```php
interface DismissStore
{
    public const KEY_PATTERN = '/^[A-Za-z0-9_:-]{1,64}$/';
    public function dismissed(string $key): bool;
    public function dismiss(string $key): void;
}
interface ChecklistStore extends DismissStore { /* collapsed, setCollapsed */ }
```

`ChecklistStore` now extends `DismissStore` (no change for checklist code or existing implementations, which already have both methods). The kit binds `DismissStore` with `bindIf` to whatever `ChecklistStore` resolves to, so an app's per-user checklist store keeps banners per user too; bind `DismissStore` yourself to store them elsewhere. In the component, override `dismissibleKeys()` (422 for anything else) and `canDismiss($key)` (403). Every dismissal fires a bubbling `ui-dismiss` event, `{ key }`. After Dismiss, focus moves to the next control on the page, else the page heading.

Do: one banner at a time per page; say what happens and when. Don't: use it for a form error (`x-ui.alert`, `x-ui.error-summary`) or a save confirmation (toast), or add an icon or coloured edge.

## Tree

`x-ui.tree`: a nested list on the APG tree view pattern.

```blade
<x-ui.tree label="Files to share" :nodes="$folders" checkable lazy="children" wire:model.live="checked" :value="$checked" />
```

```php
#[Renderless]
public function children(string $id): array
{
    $folder = Folder::whereUuid($id)->firstOrFail();
    $this->authorize('view', $folder); // the id comes from the browser
    return $folder->children->map(fn ($f) => ['id' => $f->uuid, 'label' => $f->name, 'hasChildren' => $f->children_count > 0])->all();
}
```

| Prop | Meaning |
|---|---|
| `nodes` | `['id', 'label', 'children', 'hasChildren', 'expanded', 'href', 'disabled']` |
| `label` | The tree's name (required for screen readers) |
| `selectable` | One row at a time (`aria-selected`); `wire:model` gets its id |
| `checkable` | A check per row (`aria-checked` true, false or mixed); checking a parent checks its loaded children, a parent with some children checked is mixed; `wire:model` gets every checked id |
| `lazy` | Livewire method for children of `hasChildren` nodes, called once with the node's id the first time it opens; return the same shape (at most 500 rows are built) |
| `value`, `expanded`, `id` | First paint; ids open on load |

**Keyboard.** Down/Up, Right (open, then first child), Left (close, then parent), Home/End, Enter (select, follow `href`, or toggle), Space (select or check), `*` (open every sibling), typeahead (500ms buffer). One row in the Tab order. **Lazy loading:** the row is `aria-busy` and says "Loading"; "3 items loaded." is announced; a failure says "Could not load" and Right or a click retries. Rows from the server are built with `textContent`, never HTML. **Morphs:** the `role="tree"` list is `wire:ignore`, so expansion and loaded rows survive re-renders; the binding flows both ways; change the tree's `wire:key` to rebuild it from new data. No Alpine per row.

**Look.** 32px rows in body, 16px indent per level, a chevron that turns (`--motion-sm`), hover `surface-subtle`, selected `surface-muted` (ink 13.63:1) with 500 weight, checks in the `x-ui.checkbox` look, the focus ring inset on the row.

## Progress

### `x-ui.progress` (extended, compatible)

The existing props (`label`, `value`, `max`, `valueText`, `hideLabel`, `id`) work as before. The native `<progress>` still carries the label, value and `aria-valuetext`; the visible fill is now drawn with `transform: scaleX()` so a value change animates (`--motion-md`, `ease-motion-out`, instant under reduced motion) when a Livewire morph keeps the element.

| Prop | Meaning |
|---|---|
| `size` | `sm` 4px (default), `md` 8px, `lg` 12px |
| `tone` | `default` (chart-1), `success` (success-text), `warning` (warning-text), `danger` (danger-text): each 3:1 or more on the `surface-muted` track and on white |
| `labelPosition` | `top` (label left, value right), `end` (the value after the bar on one line), `hidden` (both kept for assistive tech) |
| `format` | `percent`, `count` ("3 of 5"), `bytes` ("2.1 MB of 5 MB", decimal units) |
| `buffer` | A second, lighter fill (`line-strong`) on the same scale, for uploads and streams that load ahead; each tone's fill is 3:1 or more against it |

### `x-ui.progress-ring`

A circular SVG progress for tiles, cells and buttons where a bar does not fit. It is a loading indicator, never a KPI: figures go in `x-ui.stat`. `role="progressbar"`, `aria-valuemin` 0, `aria-valuemax`, `aria-valuenow` (left out when indeterminate), `aria-valuetext`, named by `label`. Props: `label`, `value` (null: indeterminate, a quarter arc turns; under reduced motion it holds and pulses), `max`, `size` (24, 40, 64; strokes 3, 4, 6), `tone` (as above), `showValue` (the percent in the middle at 40 and 64), `valueText`.

### `x-ui.progress-steps`

"Step 3 of 5" as N equal segments, 2px apart. One `role="progressbar"` (`aria-valuetext` "Step 3 of 5"); `showText` prints it above (meta). Optional `labels` sit under their segments (caption, the current one in ink with `aria-current="step"`) and wrap inside their column on phones. Props: `label`, `total`, `current`, `labels`, `size` (sm 4px, md 8px), `tone`, `showText` (true). Use `x-ui.stepper` instead when the steps have names the user needs or can go back to.

### `x-ui.upload-progress` and `x-ui.upload-progress.list`

A file row: the name (body, never cut: it wraps anywhere), size and status in meta ("2.4 MB · Uploading, 45%", "Uploaded", "Failed" in danger-text with the error below), a linear progress (danger tone on failure; label hidden but kept), and the actions for its state: Cancel while waiting or uploading, Retry and Remove on failure, Remove when done, each named with the file ("Cancel upload of report.pdf").

- `x-ui.upload-progress` is server-driven: `name`, `size` (bytes), `progress`, `status` (queued, uploading, done, failed), `error`, `cancelAction`, `retryAction`, `removeAction` (Livewire calls; without them the buttons fire a bubbling `ui-upload` event, `{ action, name }`), `as` (div, li).
- `x-ui.upload-progress.list for="files"` follows Livewire's own upload events from the input bound with `wire:model="files"` (`livewire-upload-start`, `-progress`, `-finish`, `-error`, `-cancel`; livewire.laravel.com/docs/4.x/uploads, checked 2026-11-15) and shows a row per chosen file, plus an overall line and bar with several files ("Uploading 3 files, 45%"). Cancel calls `$wire.cancelUpload`, Retry uploads the same files again (`$wire.upload` / `uploadMultiple`), Remove sets a single file's property to null or calls `removeAction(index)`. Props: `for`, `multiple`, `removeAction`, `label` ("Uploads"). Livewire uploads a multiple selection as one request, so its files share one percent.

```blade
<x-ui.field label="Attachments" name="files">
    <x-ui.file wire:model="files" multiple>
        <x-slot:files>
            <x-ui.upload-progress.list for="files" multiple remove-action="removeFile" />
        </x-slot:files>
    </x-ui.file>
</x-ui.field>
```

Validate files on the server as usual; the list only shows progress.
# Chat and community components

Conversations, an inbox, posts, reactions, comment threads, polls, people, channels and rankings. Every one takes its content as data, knows nothing about the app's models or routes, and talks to Livewire with one call per action (or a bubbling browser event when there is no Livewire method). Gallery: workbench `/chat` and `/community`, both themes, light and dark. Tests: `tests/Feature/CommunityComponentsTest.php`. Contrast: the "Chat and community" rows in `TallUiKit\Support\KitContrast` (`composer contrast`).

JavaScript: `resources/js/chat.js` (the composer, the thread, the inbox keys and a stub for the chat) and `resources/js/community.js` (a few delegated listeners), both imported by `index.js`. The heavy parts load lazily with `import()`: `chat-core.js` (1.2 KB gzip) when the first chat starts, `community-core.js` (2.1 KB gzip) when a page has a post, reaction row, poll or follow button. One controller per chat or thread, one listener for the page; never an Alpine component per message, post or row.

User text is always escaped first: `TallUiKit\Support\RichText::render($text, $mentionUrl, $hashtagUrl)` splits the text into URLs, `@mentions` and `#hashtags`, escapes every piece, and links only http(s) URLs (`rel="nofollow ugc noopener"`, underlined). Line breaks are kept with `whitespace-pre-wrap`. Nothing a user types can become markup.

Payloads: every workbench demo response stays under 40 KB (tested). Repeated controls use lean classes (`.ui-act`, `.ui-reaction`, `.ui-chat-bubble`, `.ui-composer-input`) and messages and comments are built without per-row Blade conditionals, because each one adds two morph markers to every Livewire response.

| Need | Use |
|---|---|
| A conversation with a composer | `x-ui.chat` (+ `.message`, `.day`, `.typing`, `.composer`) |
| A box to write a message, comment or reply | `x-ui.chat.composer` |
| A list of conversations | `x-ui.chat.inbox` |
| A feed item | `x-ui.post` |
| Replies under a post or record | `x-ui.comment-thread` |
| Reactions with counts and a picker | `x-ui.reactions` |
| A person in a list; the top of a profile | `x-ui.member-card`; `x-ui.profile-header` (both use `x-ui.follow-button`) |
| A question with options and results | `x-ui.poll` |
| Groups and channels | `x-ui.channel-list` |
| A ranking with movement | `x-ui.leaderboard` (on `x-ui.ranked-list`) |
| An activity log (who did what) | `x-ui.timeline`, not a chat |

## Chat

```blade
<x-ui.chat
    label="Conversation with Bola Ade"
    :messages="$messages"
    older-action="loadOlder"
    :has-older="$hasOlder"
    :typing="$typing"
    send-action="send"
    :maxlength="2000"
    retry-action="retry"
    react-action="react"
    framed
/>
```

```php
public function send(string $body, string $key): bool
{
    // The key is the same for every attempt at one draft: store it and ignore repeats.
    if (Message::where('idempotency_key', $key)->exists()) {
        return true;
    }
    // ... validate, create, broadcast ...
    return true; // false keeps the draft and shows "Not sent. Try again."
}
```

Each message: `key` (stable), `author`, `authorKey` (grouping; the author by default), `avatar`, `mine`, `time`, `body`, `status` (`sent`, `sending`, `failed`), `edited`, `deleted`, `attachments` (`type` image or file, `name`, `src` or `href`, `thumb`, `alt`, `meta`, `width`, `height`), `reactions` (as `x-ui.reactions`), `seen` (true or "Seen by Ada"; shown under the last sent message that has it).

| Prop | Meaning |
|---|---|
| `messages` | The list, oldest first; or write rows in the slot |
| `olderAction`, `hasOlder` | "Load older messages" at the top: one call; the reading position is kept |
| `typing` | Names for `x-ui.chat.typing` ("Ada is typing") |
| `sendAction`, `maxlength`, `attach` | The default composer; or a `composer` slot |
| `retryAction`, `reactAction` | Per message: called with the key (and the reaction) |
| `groupMinutes` | 5: one author's messages within this many minutes form a group |
| `framed`, `height` | A 1px border and 8px radius; the height classes (`h-[36rem] max-h-[85vh]`) |
| `label`, `empty`, `tz`, `now`, `id`, `mentionUrl`, `hashtagUrl` | Name, empty text, time zone, fixed "now", id, link patterns |

Behaviour: opens at the newest message; stays at the bottom while you are there; scrolled up, incoming messages count into a "3 new messages" button that jumps down (focus moves to the composer). Your own new message always scrolls down. Images open the chat's one image dialog (the `x-ui.lightbox` look).

Accessibility: the list is `role="log"` (named, focusable for keyboard scrolling) with `aria-live="off"`; a separate polite status region reads each incoming message once ("Ada Obi: Are we still on for 3pm?"). Loading history, the first render and your own sends are never announced. Grouped messages keep the author and time for screen readers. States are words. The typing line is its own status region; its dots fade with the motion tokens and hold still under reduced motion.

### Composer

`x-ui.chat.composer`: a textarea that grows to 6 rows, then scrolls; Enter sends and Shift+Enter adds a line (`enterSends` false: Ctrl or Cmd+Enter sends); an input method (IME) never sends early. `action` is called once with `params`, the text and an idempotency key; Send is blocked while it runs, so a double click or double Enter cannot post twice. `maxlength` adds a counter ("120 / 2,000", danger text in the last 10%); `attach` (a `wire:model` upload property) adds Attach and `x-ui.upload-progress.list`; `disabled` with `disabledReason`; `size` sm, md, lg (32, 36, 40px). Events: `ui-chat-send` (no action), `ui-composer-sent`, `ui-composer-cancel` (Escape).

## Inbox

`x-ui.chat.inbox`: `conversations` (`key`, `name`, `avatar`, `people` for a group, `preview`, `time`, `unread`, `active`, `href`, `muted`), `action` (when rows have no `href`), `navigate`, slots `search` and `filters`. One Tab stop; arrows, Home and End move. Previews wrap (never clipped or ellipsed); pass a short one. Unread is bold text plus an info pill read as "3 unread".

## Post

`x-ui.post`: `key`, `author`, `avatar`, `handle`, `verified`, `time`, `body`, `href` (permalink on the time), `likes`, `liked`, `comments`, `likeAction`, `commentHref` or `commentAction`, `shareUrl`, `mentionUrl`, `hashtagUrl`, `headingLevel`. Slots: default (under the body, e.g. reactions or a poll), `media`, `more` (`x-ui.dropdown.item` rows), `footer` (e.g. a comment thread). Like is optimistic: pressed and counted at once, then `likeAction(key, on)`; `false` or a failed request rolls back and shows a toast. Counts are compact ("1.2k"); the full number is spoken. Share uses the system share sheet, or copies the link with "Link copied."

## Comment thread

`x-ui.comment-thread`: `comments` (`key`, `author`, `avatar`, `time`, `body`, `edited`, `replies`, `more`), `depth` (3; deeper replies become "Continue thread" via `continueHref` with `:key`), `replyAction(parent, text, key)` (null parent for the "Add a comment" box), `moreAction(key)` ("Show 3 more replies"), `maxlength`. Timeline look: avatar markers on a 1px rail. One reply box for the thread, copied under the comment being answered (focus moves in; Escape or a sent reply returns focus to Reply). "Hide replies" collapses a thread (`aria-expanded`).

## Reactions

`x-ui.reactions`: `reactions` (`key`, `count`, `mine`), `options` (key => [symbol, label]; Like, Love, Funny, Wow, Sad, Celebrate by default), `action(subject, reaction)`, `subject`, `picker`, `size` (sm, md). Toggles are buttons named "Like 12" with `aria-pressed`. The picker is a Popover API panel, placed below its button (never flipped), arrow keys move, Escape returns focus. Optimistic with rollback.

## Member card and profile header

`x-ui.member-card` (a 1px bordered card) and `x-ui.profile-header` (flat, the page's heading at `level` 1): `key`, `name`, `avatar`, `role`, `handle`, `bio`, `verified`, `stats` (label and value; shown compact in an `x-ui.stat-strip`), `following`, `followAction(key, on)`, `messageHref` or `messageAction`. `x-ui.follow-button` reads "Follow" or "Following" (`aria-pressed`), optimistic with rollback. The avatar has an `xl` (48px) size for the header.

## Poll

`x-ui.poll`: `key`, `question`, `options` (`key`, `label`, `votes`), `multiple`, `voted` (the viewer's keys), `closed`, `endsAt`, `results` (`after-vote` or `always`), `action(key, choices)`. Results are `x-ui.progress` bars with "42%, 120 votes"; the viewer's choice reads "(your vote)"; "Poll ended" and the total. Voting with nothing chosen says "Choose an option."; after voting, focus moves to the results.

## Channel list

`x-ui.channel-list`: `sections` (`key`, `label`, `collapsible`, `open`, `channels`: `key`, `name`, `href`, `unread`, `mentions`, `active`, `private`, `muted`), `action`, `navigate`. The sidebar section look; a collapsed section says "2 unread". Unread is bold text and a dot (", unread" spoken); mentions are an info pill ("3 mentions").

## Leaderboard

`x-ui.leaderboard`: `rows` (`key`, `rank`, `label`, `sub`, `value`, `change` in places, `new`, `current`, `href`), `valueLabel` (spoken after values), `format` (number, compact, none). Change is an arrow plus words ("Up 2", "Down 1", "Same", "New"); your row has a `surface-muted` fill, "You" and `aria-current`. No medals, avatars or podiums.

## Do and don't

- Do make every send, reply, vote and follow idempotent on the server, and validate keys and choices there.
- Do return `false` from an action that could not be saved; the kit rolls the optimistic state back.
- Do page history and replies from the server (`olderAction`, `moreAction`, `continueHref`).
- Don't bind the composer with `wire:model` or poll faster than every 30 seconds; prefer broadcasting.
- Don't wrap posts or comments in cards, add shadows, or use emoji as decoration (reaction symbols are content with labels).
- Don't truncate previews or names; they wrap.
