# Loading, toasts and notifications

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

