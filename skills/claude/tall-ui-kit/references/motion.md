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

**Decision.** Native first: transitions, `@starting-style` and `allow-discrete` cover every enter and exit, including the top layer, so dialogs need no JavaScript animation. `interpolate-size` / `calc-size()` would let CSS animate height to `auto`, but they are not yet in every browser we support, so heights use Alpine `x-collapse`. The View Transitions API was considered for the indicator and rejected: it snapshots the whole page (or needs a `view-transition-name` per element), blocks input during the transition and does not mix with Livewire morphs. GSAP (3.15.1, free for commercial use including all plugins under the Standard "no charge" licence, checked on gsap.com/licensing on 2026-11-14) is used only for what CSS cannot do cleanly: the sliding indicator with Flip (one element's selected look travelling to another element) and number count-ups. Only the core and Flip are imported, by dynamic `import()` the first time either runs, into their own chunks (`gsap` 27.6 KB and `Flip` 9.3 KB gzip); `motion.js` itself is 2.3 KB. Charts draw at once, with no entrance animation.

Rules: animate only `transform` and `opacity` except the exceptions above; no permanent `will-change`; nothing animates while hidden (`display: none`); every GSAP tween runs inside `gsap.matchMedia()` and is reverted (`mm.revert()`) when it ends or when Livewire morphs its element (`morph.updating`), so no inline style outlives it; the ticker treats gaps over 100ms as one frame (`lagSmoothing(100, 33)`) so a tween never jumps; reduced motion (the system setting, or the gallery's preview toggle, `html[data-motion-preview=reduced]`) makes every change instant and skips GSAP. State changes and focus happen first; motion follows and never delays input. No bounce, elastic, parallax, scroll-triggered effects, idle motion or confetti.

Hooks for page code: `data-motion-count`, `data-motion-flash`, `data-motion="crossfade"`, `data-motion-static` (no sliding indicator inside). `window.uiMotion` exposes `slideIndicator`, `countUp`, `flash`, `crossfade`, `reduced` and `running()` (the number of live tweens, for leak checks). The export columns and marketplace tray can adopt Flip for reorder through the same lazy loader (`loadGsap()`).
