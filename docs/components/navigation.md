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

For a fixed sidebar shell, use the kit's `--ui-sidebar-width` on the desktop content grid track and sidebar mount width. It is `15rem` when expanded and `3.5rem` when collapsed; it updates with `html[data-sidebar]` so the main content reclaims the rail's space.

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

With `navigate` (the default) the sidebar's items, its logo and the account menu's Settings use `wire:navigate`: one fetch, `<body>` swapped, no CSS or JS re-parsed, and only `<main>` cross-fades (`wire:transition.navigate`, see [motion.md](../motion.md)). The palette (`commandPalette`) goes the same way through `Livewire.navigate`; items with `reload: true` load in full. Use plain `wire:navigate`, not `.hover`: Livewire already starts the fetch on mousedown, and hover prefetch measured 19 full page renders (about 2.6s of server time) for one slow scan down an 18-item admin sidebar, against 0 without it.

- **Persist the shell sidebar.** Pass `persist` a key that changes with the items, e.g. `"sidebar-{$area}-".substr(md5(json_encode($sections)), 0, 12)` (NewBanger's `Navigation::persistKey`). The sidebar is wrapped in a plain `<div x-persist>` (no Alpine of its own: Livewire re-initialises the persisted element's own directives on every visit, which would leave its children bound to a stale scope), so the rail, the drawer, their Alpine state (collapse, open sections, tooltips) and the nav scroll (`wire:navigate:scroll`) survive. A page whose items differ (a new badge count, other capabilities) gets a fresh sidebar.
- **`resources/js/navigate.js`** runs inside Livewire's swap (`onSwap` of `livewire:navigating`, before the view transition's snapshot, so nothing flashes): it puts back the `<html>` `data-*` attributes Livewire drops (collapse, focus mode, privacy, last input, motion preview); re-marks the current item from the URL with `SidebarTones::isActive`'s rule (items with `active` left null carry `data-nav-match`; `data-nav-exact` and `data-nav-tones` on the sidebar root); restores the window scroll on back and forward (Livewire restores `<body>`, which never scrolls in a standards-mode page); and loads a page from another area in full (`[data-sidebar-area]` changed: the sign-in page after the session ended, another shell through history). `window.uiNavigate.syncSidebars()` re-marks by hand.
- **Keep as full page loads:** external links; area switches (another shell and sidebar); log out, impersonation and every form post; marketing pages, public links and file downloads; payment provider hand-offs and their return and callback routes.
- **Page scripts.** Alpine components and Livewire `@script` start again on each page. Module code must not hold `<body>` or page elements: observe `document.documentElement`, re-attach per-page watchers on `livewire:navigated`, and stop observers whose element is detached (the kit's indicator observer, toast focus return, deferred sections and the data table's resize observer do). A heading made focusable by the kit keeps its `tabindex` through morphs. Inline `<script>` in `<body>` runs again on each visit; scripts added to `<head>` stay.

## Shells

The kit has no shell layout: the app composes one. NewBanger's three shells (a dark admin sidebar, light curator and musician sidebars) share a 56px top bar with a breadcrumb, a focus mode toggle (from `lg`), a privacy toggle, the notifications bell and an account control (32px icon buttons); `x-ui.sidebar` fed by one navigation source; and a Cmd+K / Ctrl+K / `/` command palette over the same destinations. A shell layout should: print `<x-ui.head :area="...">` in `<head>`, load the kit CSS and JS, place `x-ui.progress-bar` under the top bar and `x-ui.toaster` once at the end of `<body>`, give `main` `id="main"`, `tabindex="-1"` and `wire:transition.navigate` with a "Skip to content" link, persist the sidebar (`persist`), and use the tinted `page-muted` surface for whole form pages.
