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
