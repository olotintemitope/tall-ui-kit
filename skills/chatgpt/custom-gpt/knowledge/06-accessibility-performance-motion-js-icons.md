# Accessibility and focus

## Accessibility

Target: WCAG 2.2 AA with no exemptions. Browser audit for the kit: `scripts/a11y.mjs` runs axe-core 4.10.2 (`wcag2a`, `wcag2aa`, `wcag21a`, `wcag21aa`, `wcag22aa`, `best-practice`) on every workbench gallery page in both themes at 1440 and 390px, with a menu, a dialog and a select open, and flags any page that scrolls sideways (`composer serve`, then `npm run a11y`). Apps should run the same audit on their own pages, plus reflow, target size, landmarks, heading order, injected text spacing, forced colours and reduced motion.

- Keyboard: everything interactive is a native button, link or input, or a roving-tabindex group (tabs, segmented, radio groups) with arrow keys, Home and End; dialogs trap focus and Escape releases it; a link card is one tab stop; a chart's plot is one tab stop (Left and Right, Home and End step through the points and announce each in a live region, Escape hides the read-out) and its chart/table toggle is another; tables that scroll sideways become focusable named regions.
- Focus visible and not obscured: `:focus-visible` rings on every control; `html` has `scroll-padding-top: 4.5rem` (the 56px sticky top bar) and `scroll-padding-bottom: 5rem` (the sticky form actions), so a focused field is scrolled clear of both.
- Target size: interactive targets are at least 24x24 (icon-sm buttons 32, filter-token remove 24, segmented sm 24, tabs 36 or 40, pagination 32, range inputs 24); inline text links use the inline exception.
- Names, roles, values: icon-only buttons have `aria-label`; toggles `aria-pressed`; menus and disclosures `aria-expanded`; the current page `aria-current`; busy regions and buttons `aria-busy`; invalid fields `aria-invalid` and `aria-describedby`; live regions are polite and each change is announced once.
- Charts: `role="img"` with a summary name and a data table; the third line is dashed; legends print values as text.
- Status messages: toasts, loaders, selection counts and validation summaries are polite live regions; focus never moves to announce them.
- Reflow and zoom: no page-level sideways scroll at 320px or 200% zoom; segmented controls wrap; data tables scroll inside their own box.
- Text spacing: components use line boxes, not fixed heights, for text; nothing is truncated (see [Rules](rules.md)).
- Contrast: see the Contrast table on the workbench gallery; every text pair 4.5:1, every boundary, mark and icon 3:1, in every state.
- Forced colours: borders, outlines, the selected tab's underline and toggle borders stay visible; checkboxes and radios fall back to the native control; chart swatches and bars keep their colour (`forced-color-adjust: none`); the switch thumb and progress bar use `CanvasText`. Boundaries never rely on `box-shadow`.
- Reduced motion: transitions and smooth scrolling are off; spinners and the progress bar pulse slowly; skeletons and any `animate-spin` icon stop rotating.
- Semantics: one `main`, `nav` and `header` per shell; heading levels in order; lists are lists; tables have a caption and `scope`; the detail card is a `dl`; `html` has `lang`; page titles are unique.
- Dragging: every drag needs a single-pointer alternative. Open item: `x-ui.export-columns` reorders by drag or Alt+Up/Down on the handle only; it needs visible Move up and Move down buttons (and a 24px handle) before it meets 2.5.7 and 2.5.8.

Keyboard map: buttons and links Enter (buttons also Space); tabs and segmented Left and Right (Home and End on tabs), selection follows focus; radio groups arrow keys; checkboxes and switches Space; dropdowns Enter, Space or Down to open, arrows to move, Escape to close and return focus; selects as in Select; dialogs and the command palette Tab cycles inside, Escape closes; Cmd+K or `/` opens the palette; Cmd+. toggles focus mode; H toggles privacy mode.

### Select

`x-ui.select` is the WAI-ARIA APG select-only combobox (`resources/views/components/ui/select.blade.php`, `resources/js/select.js`). There is no native `<select>` in any view: its popup belongs to the browser, cannot be styled and may open above the control. `PopupPlacementTest` fails on any `<select` under `resources/views` (allowlist empty).

- Markup: a `<button role="combobox" aria-haspopup="listbox" aria-expanded aria-controls>` named by the field's `<label for>` (or `aria-label`), with `aria-invalid`, `aria-required` and `aria-describedby` from `x-ui.field`. The popup holds a `role="listbox"` (named by the label, `aria-multiselectable` with `multiple`) of `role="option"` elements with `aria-selected`; groups are `role="group"` labelled by their heading. Focus stays on the trigger and `aria-activedescendant` names the active option, which is kept scrolled into view inside the list.
- Keyboard. Closed: Enter, Space, Down, Up and Alt+Down open on the chosen option; Home and End open on the first or last; a letter opens on the first match. Open: Down, Up, Home, End, PageDown and PageUp (10) move; Enter or Space choose and close (multiple: toggle and stay open); Alt+Up chooses and closes; Escape closes without a change (a dialog around it stays open); Tab chooses the active option and moves on; letters type ahead (a 500ms buffer; a repeated letter cycles). Disabled options are skipped. A click on the field label focuses the trigger without opening it.
- Binding: `wire:model`, `.live` and `.live.debounce` bind as before; the value changes only when an option is chosen, so `.live` sends one request per choice, never while arrowing. Inside a Livewire view the component reads the bound property on the server, so the first paint and every morph show the right label; x-model and unbound values are put back after a morph. A choice fires `input` and `change` on the trigger (they bubble to `x-ui.form`'s dirty flag). An `x-model` expression is read in the select's own Alpine scope, so bind a parent property other than `value`, `open` or `text`. Plain form posts: a hidden input with the field `name` (one per value with `multiple`); an explicit `required` uses a hidden text input so the browser's check runs, and focus moves to the trigger when it fails.
- Placement: the list opens below the trigger, always (`x-anchor.bottom-start.noflip`, `fixed` so tables, dialogs and sheets never clip it, plus `$revealBelow`). Near the end of a short page a spacer gives the page room to scroll while it is open; when there is still too little room the list shortens (at least 120px) and scrolls inside, from `max-h-72`.
- Look: the input look (1px `control` border, chevron, 36px or 32px minimum height, focus border and ring, open shows the focus border); the trigger wraps the full label, and options wrap (the list is up to 28rem wide and never narrower than the trigger). Chosen options show a check; the active option a `surface-subtle` fill inside a 1px `control` outline (3.38:1 on the fill); disabled options `ink-muted` (5.85:1); a disabled select the dashed disabled look; while its Livewire request runs a 12px ring replaces the chevron. `multiple` lists the chosen labels as wrapping text ("None selected" or the placeholder when empty). Phones use the same popup (no native fallback).
- Performance: options are rendered once on the server as bare elements (about 32 bytes each) inside `x-ignore`, with no Alpine per option; ids and `aria-selected="false"` are added the first time the list opens. Each select is one small Alpine object; positioning is the lazy `x-anchor` and the outside-click listener exists only while open.

### Overflow audit (no cut-off text)

NewBanger's `tests/Browser/overflow-audit.mjs` (app side) opens pages in headless Chromium and lists everything that hides text: `clipped` (overflow hidden or clip with content larger than the box), `ellipsis`, `line-clamp`, `spill` (text running outside its own box), `outside` (text past a clipping ancestor), `select` (a select's chosen label wider than the room beside its chevron) and `placeholder`, at 1440, 1920, 1024, 768, 390 and 320px, with long-content fixtures (a 111-character name, a 120-character title, a 60-character unbroken email). Port it to your app and fix every finding before handing a page over. In the kit, `tests/Feature/NoCutOffTest.php` guards the class-level rules.

## Focus behaviour

Focus never falls to `<body>`, and the 2px `focus` ring (2px offset, `:focus-visible` only) is the same everywhere: programmatic focus after a keyboard action shows it, after a mouse action it does not, and `tabindex="-1"` targets (page headings, dialog titles, the error summary) follow the same rule. Helpers live in `resources/js/ui.js` (`window.uiFocus.focusAfter(target, fallbacks)`, `uiFocus.restore()`); `tests/Browser/focus-checks.js` drives the demos in the gallery's Focus behaviour section.

| Rule | Where | Behaviour |
|---|---|---|
| Re-render keeps focus | Every Livewire component | Rows and cards carry `wire:key`; on each request the focused control is remembered by id, or by its item's `wire:key` and its position, and refocused if the morph replaced it |
| State changes keep focus | Tabs, segmented, switches, checkboxes, toggle buttons | Focus stays on the control; selected and focused show both the selection style and the ring |
| Loading and unavailable | `x-ui.button` (`loading`, `busy`, `disabled`) | `aria-busy` and `aria-disabled`, never the `disabled` attribute, plus a capture-phase click guard; focus stays through the request |
| Dialogs | `x-ui.dialog` family, `x-ui.confirm` family, `x-ui.sheet`, command palette, dropdowns | Open: the first field, else the first button (never ×); Cancel for destructive confirmations; the acknowledge button for `x-ui.alert-dialog`. Close: the browser returns focus to the trigger; if it is gone, `uiFocus.focusAfter` falls back to the page heading |
| Destructive list actions | Rows, filter tokens, tray items | The same control in the next item, then the previous one, then the list heading (the list's `aria-labelledby`), then the page heading |
| Form submit | `x-ui.form` | Errors: the error summary when more than 3 (`tabindex="-1"`), otherwise the first invalid field. Success on the page: focus stays on the submit button and a polite toast confirms. Success that navigates: see Navigation |
| Navigation | Shells | After `wire:navigate`, the page `h1` (`tabindex="-1"`) or `#main`; every shell has a "Skip to content" link to `#main` |
| Toasts | `x-ui.toaster` | Never take focus; Alt+T (Option+T) moves to the newest toast's dismiss button; when it closes, focus returns where it was |
| Disclosure | Accordions, "Show all" | Focus stays on the button that toggled |
| Checklist | `x-ui.checklist` | Collapse keeps focus on its button; a step that turns done keeps focus in its row (the row takes it if its action is gone) and a polite status names the step; Dismiss moves focus to the next control on the page, else the page heading |
| Ring visibility | Scroll containers, sticky bars | Tabs use an inset ring; `html` scroll padding keeps focused fields clear of the top bar and the sticky form actions |
# Performance budgets

## Performance

Rules for every Livewire page and kit component (measured in NewBanger with `npm run build` served by `php artisan serve`, Playwright timings; see the batch report for numbers):

- Public properties hold ids and scalars, never models or collections (`tests/Feature/UiKitPerformanceTest.php` checks the overview and gallery). Derived data goes in `#[Computed]` or straight to the view.
- `#[Lazy]` (with a skeleton preset as the placeholder) for big regions below the fold, kept only when first paint measurably improves.
- Text inputs: `wire:model.live.debounce.300ms` only where results update live, otherwise `.blur` or submit.
- `wire:key` on every looped row and card.
- No `wire:poll` without a reason; if needed, 30s or slower with `.visible`.
- No N+1 queries: data-backed components get a query-count test (the overview has them).
- Loader visuals wait: buttons and overlays use `wire:loading.delay.short` (150ms), the progress bar 200ms.
- Charts are server-rendered SVG and HTML (`TallUiKit\Support\Charts\ChartPlot`), with no chart library and no chart instance: a Livewire morph sends the new plot, a resize stretches it, and the only script is a small hover and keyboard component (`resources/js/charts.js`). Ticks, bars and lines are built as single strings, not Blade loops, so a 90-day chart carries no per-item morph markers. Sparklines are server-rendered SVG too.
- Animate only `transform` and `opacity` (spinner rotation, progress slide, loader fade, skeleton pulse, everything in Motion); the accordion height and the sidebar and focus-mode widths are the documented exceptions. No permanent `will-change`. Hidden loaders are `display: none`, so they do not animate. Reduced motion is respected everywhere.
- Budgets: main JS entry under 120 KB gzip (excluding the GSAP chunks; measured 2026-11-15: `app` 26.3 KB, `ui` 10.3 KB, `motion` 2.4 KB, lazy `gsap` 27.6 KB and `Flip` 9.3 KB; the 56.9 KB Chart.js chunk is gone), CLS under 0.05 (skeleton presets match the final heights), a period switch or refresh under 300ms locally, a period-switch Livewire response under 30 KB.

### Long pages and long lists

What made NewBanger's long gallery and marketplace pages slow (2026-11-15, headless Chromium, thread CPU time from a DevTools trace, so a busy machine does not inflate it) and the rules that came out of it:

- **x-anchor is lazy** (`resources/js/anchor.js`). Alpine's anchor plugin started Floating UI for every `x-anchor` at init: a `computePosition` that walks every ancestor with `getComputedStyle`, and an `autoUpdate` with scroll listeners on every scrolling ancestor. With 77 closed popovers and tooltips it was about 1s of the marketplace's first long task, and each scroll recomputed all of them. The directive is now wrapped before Livewire registers the plugin, so positioning starts when the floating element gets a box (one shared ResizeObserver) and stops when it is hidden. Markup and modifiers are unchanged.
- **No layout reads while Alpine initialises.** A synchronous `scrollWidth` or `offsetLeft` read in an `x-init` forces a full layout per component, between other components' DOM writes. `x-ui.table` measured its scroll box in `x-init` (3.8s of forced layout on the workbench gallery); it now relies on the ResizeObserver's first callback. `x-ui.tabs` reveals the current tab in `requestAnimationFrame`. New components: measure in a ResizeObserver or a frame, never inline.
- **Nothing animates for Alpine's first bindings.** The tabs and segmented indicator slid on load (GSAP loaded and a Flip ran per control); `motion.js` now waits until two frames after `alpine:initialized` (and after a deferred section starts).
- **No Alpine component per item in a long list.** Morphing an element that carries Alpine state makes Livewire clone and re-initialise it, so the cost of every re-render grows with the number of components. A card or row is plain markup; per-item behaviour is one delegated listener for the page (`resources/js/marketplace.js`), and per-item popovers use the Popover API (light dismiss, Escape and the top layer for free) with their content in an inert `<template>` until they open. Tests: `tests/Feature/Marketplace/MarketplacePerformanceTest.php`.
- **Deferred sections** (`data-ui-defer`, `resources/js/defer.js`). A section, card or closed drawer with `data-ui-defer` is server-rendered as usual but its Alpine and `wire:` directives start through Alpine's own `deferInit` when it comes within a viewport of the screen, gets focus, a tap or a key press (before the event reaches its controls), or in idle time after load (in document order, as many per idle period as fit). Opening a dialog by name (`open-dialog`, `open-confirm`) that sits in a waiting element starts that element and sends the event again, so a button in one section always reaches a dialog in another. Nothing starts in a burst on the first tap, so it never costs INP. Until then it has `content-visibility: auto` (skipped for style, layout and paint while off screen, still in the accessibility tree and find-in-page); that is lifted (`data-ui-live`) as soon as it starts, because content-visibility also clips painting and menus, tooltips and focus rings must never be cut off. `window.uiDefer.flush()` starts everything (the browser audits call it); a `ui:deferred-start` event follows each start (placeholders resync on it). Use it for long galleries and grids, never for what is on screen at load. the workbench gallery defers every section after Typography; the marketplace defers the sections under the playlists and each card.
- **Compact repeated markup.** Each whitespace run between tags is a text node that the parser builds and every morph walks. `x-marketplace.playlist-card` drops it (its containers are all flex, grid, rows or blocks, so it never rendered): 101 to 36 non-element nodes per card.
- **Paginate long lists**: render a page (48 cards) with `x-ui.load-more`, never hundreds of cards at once.
- **Reserve late columns.** A long page paints before it has finished streaming. If a side rail (the selection tray) comes after the list in the source, give the row a grid template with the rail's width so the list does not reflow when it arrives (that was the marketplace's 0.127 CLS).
- **Measuring.** `php artisan serve` on a production build (`npm run build`), Playwright with a CDP session: `Tracing` (`devtools.timeline`) for style, layout and long tasks (use each event's `tdur`, the thread time), `Profiler` for script hot spots, `Emulation.setCPUThrottlingRate` 4 for a mid-range phone, and a `PerformanceObserver` for LCP, CLS and event timing. Note the machine's load average with every number.
# Motion

Tokens (`tall-ui-kit.css`): durations 100, 150, 200 and 300ms (`duration-100` ... `duration-300`, or `--motion-xs`, `--motion-sm`, `--motion-md`, `--motion-lg` in CSS, all 0ms under reduced motion); curves `ease-motion-out` (`cubic-bezier(0.2, 0, 0, 1)`, entering and moving) and `ease-motion-in-out` (`cubic-bezier(0.4, 0, 0.2, 1)`, size changes).

| What | How | Motion |
|---|---|---|
| Dropdowns, notification popover, command palette | Alpine `x-transition` | fade and a 4px slide, 150ms in, 100ms out |
| Dialogs (`x-ui.dialog` family, confirmations) | CSS on the native `<dialog>`: `@starting-style`, `transition-behavior: allow-discrete` on `overlay` and `display` | fade and scale from 0.98, 150ms; the `::backdrop` fades |
| Sheets, the mobile drawer | same, `.ui-sheet` / `.ui-drawer` | slide from the edge, 200ms |
| Full-screen dialogs on phones | `.ui-dialog-fullscreen` | 16px rise and fade |
| Toasts | Alpine `x-transition` | 4px rise and fade in, fade out |
| Accordion and sidebar sections, sidebar groups | Alpine `x-collapse` | height, 150ms (measured exception) |
| Switch thumb, checkbox tick | CSS on `[data-ui=switch]` and `[data-ui=checkbox] ~ svg` | transform 150ms, tick opacity 100ms and scale from 0.6 |
| Badge status change | `data-motion="crossfade"`, Web Animations after a morph | opacity, 150ms |
| Row highlight on refresh | `data-motion-flash="{version}"`; a CSS keyframe on `::after` | `surface-muted` wash, multiply, opacity only, 600ms |
| Sidebar collapse, focus-mode width | CSS `transition-[width]` / `[padding]` | 150ms `ease-motion-in-out` (measured exceptions) |
| Segmented and tabs indicator | GSAP Flip (`resources/js/motion.js`) | a ghost of the selected box or underline slides, 200ms |
| Figure count-up | GSAP, `data-motion-count="{value}"` on a `type-figure` | 300ms; compact values (1.2k) are not counted |
| Page change (`wire:navigate`) | `wire:transition.navigate` on the shell's `<main>` (Livewire 4.4 view transitions); CSS on `::view-transition-*(livewire-navigate)` | cross-fade of the main region only, opacity, 150ms `ease-motion-out`; the sidebar, top bar and toasts hold still; instant without the API, under reduced motion and with a dialog or popover open |
| Chart entrance (`x-ui.chart` line, bar, stacked-bar) | `data-motion-enter` on the plot frame; one shared `IntersectionObserver` in `motion.js`; CSS keyframes | once, the first time the plot is a quarter in view: lines draw in (`stroke-dashoffset`, measured on-screen length), 400ms; bars rise from the baseline (`scaleY`), 300ms, staggered 150ms at most in total; never on a morph |
| Checklist step done (`x-ui.checklist`) | `[data-checklist-flip]` set by `resources/js/checklist.js` on `morph.updating`; a CSS keyframe | the check scales in from 0.6 and fades in, 150ms `ease-motion-out`; the progress fill follows `x-ui.progress`'s width transition, 200ms; only when a step turns done, never on first render |

**Decision.** Native first: transitions, `@starting-style` and `allow-discrete` cover every enter and exit, including the top layer, so dialogs need no JavaScript animation. `interpolate-size` / `calc-size()` would let CSS animate height to `auto`, but they are not yet in every browser we support, so heights use Alpine `x-collapse`. The View Transitions API was considered for the indicator and rejected: it snapshots the page (or needs a `view-transition-name` per element) and does not mix with Livewire morphs. It is used for page changes only, through Livewire's own `wire:transition.navigate` (livewire.laravel.com/docs/4.x/navigate, "Animating page visits with view transitions", checked 2026-11-15) on the shell's `<main>`: Livewire names the region `livewire-navigate` for the swap, turns the root animation off so the sidebar and top bar hold still, and swaps instantly under `prefers-reduced-motion`, without the API (no polyfill) and while a dialog or popover is open; back and forward, scroll restoration and the page cache go through the same swap. `tall-ui-kit.css` keeps the region's group and the root group from tweening geometry (`animation-name: none`, so only the snapshots' opacity runs, 150ms `ease-motion-out`), gives the toaster its own name with no animation so it stays above the region, sets `pointer-events: none` on `::view-transition` so a click during the fade reaches the new page, and turns the fade off under the gallery's reduced preview. Focus still moves to the new page's `h1` on `livewire:navigated`, with no ring after a pointer action (`html[data-input]`). Morphs are untouched (`wire:transition` is not used). Cross-document transitions (`@view-transition { navigation: auto }`) were skipped for full page loads: they cannot be limited to one region per layout from a shared stylesheet, so they would fade the whole page, header and sidebar included, on every ordinary link. The shell's sidebar and palette use `wire:navigate` ([navigation.md](navigation.md), Page changes), so the region fade covers ordinary moves around an area. GSAP (3.15.1, free for commercial use including all plugins under the Standard "no charge" licence, checked on gsap.com/licensing on 2026-11-14) is used only for what CSS cannot do cleanly: the sliding indicator with Flip (one element's selected look travelling to another element) and number count-ups. Only the core and Flip are imported, by dynamic `import()` the first time either runs, into their own chunks (`gsap` 27.6 KB and `Flip` 9.3 KB gzip); The chart entrance adds about 0.6 KB gzip to `motion.js` (3.6 KB gzip in the NewBanger build) and no GSAP: `x-ui.chart` renders a bare `data-motion-enter` on the plot frame of line, bar and stacked-bar plots with data; `motion.js` marks it `wait` (its marks hidden, only on screen with motion allowed) and one shared `IntersectionObserver` (threshold 0.25) plays it the first time it is a quarter in view (`run`, then `done` after 500ms, when its custom properties are removed). The lines are non-scaling strokes, so their dash length is measured in screen pixels from the path and the plot's width (`--motion-length`); the dashed third series fades instead, so its dash pattern is kept. Bars and stacks rise with `scaleY` from the bottom, each slot delayed by `--motion-delay`. `morph.updating` copies the state onto the incoming markup, so a refresh or a period or series switch updates at once (the count-up and crossfade hooks still run), and a plot that gains data in a morph shows it at once; a chart first rendered by a lazy section waits to be seen like any other. The empty plot, `motion="none"`, `data-motion-static` and horizontal bars and sparklines never animate; the tooltip, crosshair, keyboard stepping, privacy masking and the sr-only table are separate elements and unaffected. The observer's callback reads one width and writes custom properties (under 1ms per call measured), so it never makes a long task.

Rules: animate only `transform` and `opacity` except the exceptions above; no permanent `will-change`; nothing animates while hidden (`display: none`); every GSAP tween runs inside `gsap.matchMedia()` and is reverted (`mm.revert()`) when it ends or when Livewire morphs its element (`morph.updating`), so no inline style outlives it; the ticker treats gaps over 100ms as one frame (`lagSmoothing(100, 33)`) so a tween never jumps; reduced motion (the system setting, or the gallery's preview toggle, `html[data-motion-preview=reduced]`) makes every change instant and skips GSAP. State changes and focus happen first; motion follows and never delays input. No bounce, elastic, parallax, scroll-triggered effects, idle motion or confetti.

Hooks for page code: `data-motion-count`, `data-motion-flash`, `data-motion="crossfade"`, `data-motion-static` (no sliding indicator and no chart entrance inside), `data-motion-enter` (x-ui.chart's plot frame; `motion="none"` leaves it out), `wire:transition.navigate` (one per page, on `<main>`). `window.uiMotion` exposes `slideIndicator`, `countUp`, `flash`, `crossfade`, `reduced`, `watchEntrances(root)` (observe charts added outside Livewire) and `running()` (the number of live tweens, for leak checks). The export columns and marketplace tray can adopt Flip for reorder through the same lazy loader (`loadGsap()`).
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
| `ui.js` | focus helpers (`window.uiFocus`), `focusMode` and `progress` stores, `commandPalette`, `uiForm` (focus on the first error), `uiCopy`; imports `anchor.js` (lazy `x-anchor`), `defer.js` (`data-ui-defer`), `select.js` (`uiSelect`), `forms.js` (HTML5 controls, media, form layouts), `checklist.js` (`uiChecklist`, the step-done morph hook), `navigate.js` (`wire:navigate` support: `<html>` state, the persisted sidebar's current item, history scroll, another area in full; `window.uiNavigate`) |
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
# UI kit: icons and copy to clipboard

Companion to [the components index](components.md). Gallery: the workbench gallery, section "Copy to clipboard"
(`resources/views/livewirethe workbench gallery-gallery/copy.blade.php`).

## Icons

`x-ui.icon` draws Lucide icons from `mallardduck/blade-lucide-icons`.

```blade
<x-ui.icon name="chart-line" />
<x-ui.icon name="check" stroke-width="1.5" class="size-4 text-ink-muted" />
```

- API unchanged: `name`, `strokeWidth` (2), other attributes (class, `wire:*`, `x-*`) pass through. `aria-hidden="true"` by default: the control or text beside the icon carries the name; icon-only buttons have `aria-label`.
- Output: one inline `<svg>` (24px box, sized by class), `stroke="currentColor"` and the given `stroke-width`. `TallUiKit\Support\LucideIcons::inner()` takes the package file's child elements and drops its own `<svg>` wrapper, so the root is always the kit's and no attribute is duplicated.
- Names are the React app's (`lucide-react@0.436.0`). Use any Lucide name; `tests/Feature/UiKitIconsTest.php` scans the kit's views, `src` and the workbench for `x-ui.icon` names (literals and the quoted literals in `:name` expressions), `icon="..."` props, `'icon' => '...'` options and `->icon('...')` actions, and fails on any name that does not resolve. An unknown name throws in local and testing and draws nothing in production.

### Version

The package is pinned at `1.23.0`, which ships Lucide `0.412.0`. Checked node for node against `lucide-react@0.436.0`: every icon the Blade views use is identical except the two chart icons below. The next release, `1.24.0`, jumps to Lucide `0.553.0`, where 27 of the icons in use have different elements or path data (search, settings, bell, users and others), so do not upgrade casually: rerun the comparison (each used icon's elements against the React app's) and update this section.

Lucide renamed its chart icons between 0.412 and 0.436 (`bar-chart-*` and `line-chart` became `chart-*`). Views use the React names; `LucideIcons::ALIASES` maps them to the package's files:

| React name | Package file | Drawing |
|---|---|---|
| `chart-line` | `line-chart` | Same lines; the axis corner is square (0.436 rounds it by 2px) |
| `chart-column` | `bar-chart-3` | Same bars; the axis corner is square |
| `chart-no-axes-column` | `bar-chart-2` | Identical |
| `chart-no-axes-column-increasing` | `bar-chart` | Identical |
| `chart-no-axes-gantt` | `gantt-chart` | Identical |
| `file-chart-column` | `file-bar-chart-2` | Identical |
| `file-chart-column-increasing` | `file-bar-chart` | Identical |
| `file-chart-line` | `file-line-chart` | Identical |
| `square-chart-gantt` | `gantt-chart-square` | Identical |

Not in 0.412 at all (none used yet; add them by upgrading, not by copying paths): the other `chart-*` icons (`chart-bar`, `chart-pie`, `chart-area`, `chart-spline`, ...), `map-pin-*` variants, `tickets`, `id-card`, `microchip`, `binoculars` and a few more.

### Performance and production

- `config/blade-icons.php` turns off the package's per-icon Blade components (`<x-lucide-*>`, about 1,650 registrations per request). Use `x-ui.icon`.
- Each icon's markup is read once per request and kept in memory (`LucideIcons::inner`).
- Production: `php artisan icons:cache` writes the icon manifest to `bootstrap/cache/blade-icons.php` (git-ignored); `php artisan icons:clear` removes it. `php artisan optimize` runs `icons:cache` too (blade-icons registers it), so the deploy step in `docs/tall-migration-plan.md` 5.2 covers it; run it on its own where `optimize` is skipped.

## Copy to clipboard

Three components on one Alpine behaviour, `uiCopy` in `resources/js/ui.js`.

```blade
<x-ui.copy-button value="NB-CMP-048217" />
<x-ui.copy-button value="NB-CMP-048217" variant="icon" size="sm" label="Copy campaign reference" />
<x-ui.copy-button :value="$link" variant="link" label="Copy link" />

<x-ui.field label="Referral link" name="referral">
    <x-ui.copy-field :text="$link" />
</x-ui.field>
<x-ui.copy-field text="whsec_••••3f9a" :value="$secret" label="Webhook secret" mono icon-only />

<x-ui.copy-text label="Copy reference" mono>{{ $payout->reference }}</x-ui.copy-text>
```

| Component | Use | Look |
|---|---|---|
| `x-ui.copy-button` | A standalone action | `label`: outline button, icon and label. `icon`: ghost icon button, label as `aria-label` and tooltip. `link`: link style with the icon. Sizes as `x-ui.button`: default 36px, `sm` 32px |
| `x-ui.copy-field` | Keys, links, secrets | Read-only input and a joined button inside one 1px `control` border, divided by a 1px `control` rule; inset focus ring on the button; the input selects its text on focus |
| `x-ui.copy-text` | A value inside text or a table cell | The value, then a 24px icon button that shows on hover of the value, on keyboard focus, while a state shows, and always on touch screens. Always in the tab order; its space is always kept. The value wraps (long references break anywhere). Lean markup for dense rows (the `.ui-copy-*` classes in `tall-ui-kit.css`, about 1.2 KB per instance, down from 3.2 KB); its tooltip (the label, "Copied" or "Press ⌘C to copy") is anchored `fixed`, so a scrolling table never clips it |

### States

| State | Icon | Label | Colour | Announced |
|---|---|---|---|---|
| Idle | copy | "Copy" (or `label`) | ink (label), ink-muted icon buttons (5.86:1), primary-text links (5.21:1) | |
| Copied, 2s | check, cross-faded over 150ms (opacity only) | "Copied" | emerald-700, 5.48:1 on white | "Copied to clipboard" (polite) |
| Failed | copy | stays "Copy"; a hint attached below the button reads "Press ⌘C to copy" (Ctrl+C off a Mac) | as idle; the hint is white on ink (15.9:1) | "Copy failed. Press ⌘C to copy." (polite); the button is `aria-describedby` the hint while it shows |

- A repeat click restarts the 2s timer and announces again (the live region is cleared first).
- Failed: the Clipboard API is missing (no secure context) or the write is refused. The value is put in a visually hidden, `aria-hidden` span and selected, so the shortcut works while focus stays on the button; in `x-ui.copy-field` the input's own text is selected too. The state stays until focus leaves the component.
- No width shift: "Copy" and "Copied" sit in one grid cell (`inline-grid`, `[grid-area:1/1]`, the hidden one `invisible`), so the button is only as wide as "Copied" (the old third label, "Press ⌘C to copy", reserved about 166px). The failure hint (`data-copy-hint`) is a tooltip-style bubble anchored with `x-anchor.bottom-start.fixed`, outside the flow, so it never moves the layout and is not clipped by the field's `overflow-hidden` border. Icon-only buttons already carry the guidance in their tooltip. The icons share one 16px cell the same way.
- Focus stays on the button in every state. Reduced motion: the cross-fade is instant (`motion-reduce:transition-none`).
- No toast by default. `toast` adds a "Copied to clipboard." toast, for copies started from a menu that closes (the button's own feedback is gone with the menu).

### What is copied

Only the visible value. `x-ui.copy-text` copies its rendered text and `x-ui.copy-field` its `text`, so a masked value (`•••• 4821`) copies the mask. A page that wants the full value copied passes `value` explicitly, after its own permission check: the value is then in the page's markup. Do not wrap privacy-mode money (`x-ui.money`) in `x-ui.copy-text`.

### Gallery states

`data-force="copied"` or `data-force="failed"` on any of the three draws that state and turns copying off; `data-force="hover"` or `"focus"` on `x-ui.copy-button` draws the pointer state, as on `x-ui.button`.

### Tests

`tests/Feature/UiKitCopyTest.php`: markup, the live region, the failure fallback span, the reserved-width label grid, sizes, forced states, value and mask rules, the toast option, the gallery section. The clipboard itself is checked in the browser.
