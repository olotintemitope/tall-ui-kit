# JavaScript

Alpine and Livewire come from `@livewireScripts`; the kit never imports Alpine. Its modules register stores, magics and `Alpine.data` components on `alpine:init`, so they use Livewire's Alpine instance.

## One entry

```js
// resources/js/app.js
import '../../vendor/olotintemitope/tall-ui-kit/resources/js/index.js';
```

Vite resolves `gsap` from your app's `node_modules`: `npm install gsap` (3.15 or later; free for commercial use under GSAP's standard licence). Your Tailwind entry must `@import` the kit CSS (see [theming.md](theming.md)) so the classes used in the kit's views, scripts and PHP are generated (`@source` lines in `tall-ui-kit.css` cover them).

## Per-feature modules

`index.js` imports these in order. Import a subset instead when a page needs less:

| Module | What it registers |
|---|---|
| `kit.js` | `privacy` store (x-ui.money masking, H shortcut), `dismissible` data, the `uiChart` component (`charts.js`) |
| `toasts.js` | `toasts` store, `$toast` magic, the `toast` browser event (Livewire `$this->dispatch('toast', ...)`); imports `toaster-layer.js` (toasts above modals) |
| `dialogs.js` | `uiDialog` (native `<dialog>`: open, close, busy, dirty guard, `open-dialog`/`open-confirm` events); imports `placement.js` |
| `ui.js` | focus helpers (`window.uiFocus`), `focusMode` and `progress` stores, `commandPalette`, `uiForm` (focus on the first error), `uiCopy`; imports `anchor.js` (lazy `x-anchor`), `defer.js` (`data-ui-defer`), `select.js` (`uiSelect`), `forms.js` (HTML5 controls, media, form layouts) |
| `motion.js` | `window.uiMotion`: sliding tab and segmented indicator, count-ups, row flash, crossfade |
| `placement.js` | `$revealBelow`: keeps popups below their trigger by scrolling, never flipping |
| `storage.js` | `storageKey(name)`: the prefixed storage key |

## Lazy and deferred behaviour (kept from the app)

- **GSAP is lazy.** `motion.js` loads `gsap` and `gsap/Flip` with `import()` the first time an indicator slides or a figure counts up, into their own chunks (about 27.6 KB and 9.3 KB gzip). Nothing animates until two frames after `alpine:initialized`.
- **`x-anchor` is lazy.** `anchor.js` wraps Alpine's anchor directive before Livewire registers it: Floating UI starts only when the floating element gets a box and stops when it is hidden.
- **Deferred sections.** Anything marked `data-ui-defer` is server-rendered but its Alpine and `wire:` directives start when it nears the viewport, gets focus or input, or in idle time. `window.uiDefer.flush()` starts everything (audits call it).

Sizes measured on the workbench build: the whole kit entry is 19.5 KB gzip, plus the lazy GSAP chunks. Budget: under 120 KB gzip for an app's main entry, excluding GSAP.

## Head script

Put `<x-ui.head />` in `<head>` (with `:area="'admin'"` in a shell that has a sidebar). It sets the storage prefix and applies privacy mode, sidebar collapse and focus mode before first paint, so nothing flashes.
