/*
 * Motion that native CSS cannot do cleanly (docs/ui-kit.md "Motion"). Everything
 * else is CSS (transitions, @starting-style, allow-discrete) or Alpine x-transition /
 * x-collapse; this file only:
 *
 *   - slides the selected indicator of x-ui.segmented ([data-segmented-indicator])
 *     and the x-ui.tabs underline between options, with GSAP Flip;
 *   - counts figures up or down when a [data-motion-count] value changes on a morph;
 *   - flashes a row whose [data-motion-flash] version changes on a morph (CSS
 *     keyframes on a pseudo-element, opacity only);
 *   - cross-fades a [data-motion="crossfade"] element (a status badge) whose text
 *     changes on a morph (Web Animations, opacity only).
 *
 * GSAP core and Flip are imported lazily (dynamic import, their own chunk) the
 * first time an indicator slides or a figure counts, so most pages never load
 * them. Each tween runs in a gsap.context() that is reverted when it ends or when
 * Livewire morphs its element, so no inline style outlives it. gsap.matchMedia
 * picks the reduced-motion branch: no tween, the final state at once. Nothing here
 * delays focus or input: state changes first, motion follows.
 */

const REDUCE = '(prefers-reduced-motion: reduce)';
const DURATION = { xs: 0.1, sm: 0.15, md: 0.2, lg: 0.3 };

/* cubic-bezier(0.2, 0, 0, 1), the kit's ease-motion-out, as a GSAP ease function. */
function bezier(x1, y1, x2, y2) {
    const a = (p1, p2) => 1 - 3 * p2 + 3 * p1;
    const b = (p1, p2) => 3 * p2 - 6 * p1;
    const c = (p1) => 3 * p1;
    const at = (t, p1, p2) => ((a(p1, p2) * t + b(p1, p2)) * t + c(p1)) * t;
    const slope = (t, p1, p2) => 3 * a(p1, p2) * t * t + 2 * b(p1, p2) * t + c(p1);

    return (x) => {
        if (x <= 0 || x >= 1) return x;
        let t = x;
        for (let i = 0; i < 8; i++) {
            const d = slope(t, x1, x2);
            if (Math.abs(d) < 1e-6) break;
            t -= (at(t, x1, x2) - x) / d;
        }

        return at(t, y1, y2);
    };
}

export const easeOut = bezier(0.2, 0, 0, 1);

let gsapPromise = null;

/* Lazily loads GSAP core and Flip once; resolves to { gsap, Flip }. */
export function loadGsap() {
    gsapPromise ??= Promise.all([import('gsap'), import('gsap/Flip')]).then(([core, flip]) => {
        const gsap = core.gsap ?? core.default;
        const Flip = flip.Flip ?? flip.default;
        gsap.registerPlugin(Flip);
        // A tween that starts after the ticker slept (or on a slow frame) must not
        // jump ahead: treat any gap over 100ms as one 33ms frame.
        gsap.ticker.lagSmoothing(100, 33);
        window.__uiGsapLoaded = true;

        return { gsap, Flip };
    });

    return gsapPromise;
}

/* Reduced motion: the system setting, or the gallery's preview toggle. */
export function reduced(el) {
    return window.matchMedia(REDUCE).matches
        || document.documentElement.dataset.motionPreview === 'reduced'
        || Boolean(el?.closest?.('[data-motion-preview="reduced"]'));
}

/* Running animations by element, each with its cleanup (kill tweens, revert styles). */
const running = new Map();

function stop(el) {
    const cleanup = running.get(el);
    if (cleanup) {
        running.delete(el);
        cleanup();
    }
}

/*
 * Runs `build` inside gsap.matchMedia so the reduced branch and every tween's
 * inline styles are reverted afterwards. `build(gsap, Flip, done, onCleanup)` calls
 * done() when its tweens complete and registers DOM cleanups with onCleanup().
 */
async function animate(key, build, fallback) {
    stop(key);
    if (reduced(key)) {
        fallback?.();

        return;
    }
    const { gsap, Flip } = await loadGsap();
    const mm = gsap.matchMedia();
    const cleanups = [];
    let finished = false;
    const finish = () => {
        if (finished) return;
        finished = true;
        running.delete(key);
        mm.revert();
        cleanups.splice(0).forEach((cleanup) => cleanup());
        fallback?.();
    };
    running.set(key, finish);
    mm.add({ motion: '(prefers-reduced-motion: no-preference)', reduce: REDUCE }, (context) => {
        if (context.conditions.reduce) {
            finish();

            return undefined;
        }
        build(gsap, Flip, finish, (cleanup) => cleanups.push(cleanup));

        return undefined;
    });
}

/* --- Sliding indicator (segmented and tabs) ----------------------------------- */

function placeGhost(ghost, target, group, kind) {
    const box = group.getBoundingClientRect();
    const rect = target.getBoundingClientRect();
    ghost.style.left = `${rect.left - box.left}px`;
    ghost.style.width = `${rect.width}px`;
    if (kind === 'tabs') {
        ghost.style.top = `${rect.bottom - box.top - 1}px`;
        ghost.style.height = '1px';
    } else {
        ghost.style.top = `${rect.top - box.top}px`;
        ghost.style.height = `${rect.height}px`;
    }
}

export function slideIndicator(group, from, to, kind = 'segmented') {
    if (!group || !from || !to || from === to) return;

    animate(group, (gsap, Flip, done, onCleanup) => {
        if (!from.isConnected || !to.isConnected) {
            done();

            return;
        }
        const style = getComputedStyle(to);
        const ghost = document.createElement('span');
        ghost.setAttribute('data-motion-ghost', '');
        ghost.setAttribute('aria-hidden', 'true');
        if (kind === 'tabs') {
            ghost.style.backgroundColor = style.borderBottomColor;
        } else {
            ghost.style.backgroundColor = style.backgroundColor;
            ghost.style.border = `1px solid ${style.borderTopColor}`;
            ghost.style.borderRadius = style.borderRadius;
            ghost.style.boxSizing = 'border-box';
        }

        group.setAttribute('data-motion-indicating', '');
        to.setAttribute('data-motion-pending', '');
        group.appendChild(ghost);
        placeGhost(ghost, from, group, kind);
        const state = Flip.getState(ghost);
        placeGhost(ghost, to, group, kind);

        onCleanup(() => {
            ghost.remove();
            group.removeAttribute('data-motion-indicating');
            to.removeAttribute('data-motion-pending');
        });
        Flip.from(state, { duration: DURATION.md, ease: easeOut, scale: true, onComplete: () => done() });
    });
}

function indicatorGroup(el) {
    if (el.matches('[role=radio]')) return { group: el.closest('[role=radiogroup]'), kind: 'segmented', selected: (node) => node.hasAttribute('data-segmented-indicator') };
    if (el.matches('[role=tab]')) return { group: el.closest('[role=tablist]'), kind: 'tabs', selected: (node) => node.getAttribute('aria-selected') === 'true' };

    return null;
}

/*
 * Alpine's first bindings (on load and after wire:navigate) set aria-selected and
 * data-segmented-indicator on every tabs and segmented control. Those are not user
 * changes: sliding them loaded GSAP and ran a Flip per control during the first long
 * task. Indicators slide only once the page has painted after Alpine started, and
 * not for the first bindings of a section whose Alpine starts late (data-ui-defer).
 */
let settled = false;

function settleAfterInit() {
    settled = false;
    requestAnimationFrame(() => requestAnimationFrame(() => { settled = true; }));
}

document.addEventListener('alpine:initialized', settleAfterInit);
document.addEventListener('livewire:navigating', () => { settled = false; });
document.addEventListener('livewire:navigated', settleAfterInit);

function watchIndicators() {
    const observer = new MutationObserver((records) => {
        if (!settled) return;
        const changes = new Map();
        for (const record of records) {
            const el = record.target;
            if (!(el instanceof HTMLElement)) continue;
            const info = indicatorGroup(el);
            if (!info?.group || info.group.closest('[data-ui-defer]')?._uiSettling) continue;
            const now = record.attributeName === 'aria-selected' ? el.getAttribute('aria-selected') : el.getAttribute('data-segmented-indicator');
            if (now === record.oldValue) continue;
            const entry = changes.get(info.group) ?? { ...info, from: null, to: null };
            if (info.selected(el)) entry.to = el;
            else if (record.attributeName === 'aria-selected' ? record.oldValue === 'true' : record.oldValue !== null) entry.from = el;
            changes.set(info.group, entry);
        }
        for (const [group, { from, to, kind }] of changes) {
            if (from && to && !group.closest('[data-motion-static]')) slideIndicator(group, from, to, kind);
        }
    });
    observer.observe(document.body, { subtree: true, attributes: true, attributeOldValue: true, attributeFilter: ['data-segmented-indicator', 'aria-selected'] });
}

/* --- Count-up ------------------------------------------------------------------- */

const NUMBER = /-?[\d,]+(?:\.\d+)?/;

/* Tweens the number inside el's text from `from` to its current text. */
export function countUp(el, from) {
    const finalText = el.textContent;
    const match = finalText.match(NUMBER);
    if (!match || /\d\s*[kMB]\b/.test(finalText)) return;
    const to = Number(match[0].replace(/,/g, ''));
    if (!Number.isFinite(from) || !Number.isFinite(to) || from === to) return;
    const decimals = (match[0].split('.')[1] ?? '').length;
    const format = new Intl.NumberFormat('en-GB', { minimumFractionDigits: decimals, maximumFractionDigits: decimals });
    const [before, after] = [finalText.slice(0, match.index), finalText.slice(match.index + match[0].length)];

    animate(el, (gsap, Flip, done) => {
        const value = { n: from };
        gsap.to(value, {
            n: to,
            duration: DURATION.lg,
            ease: easeOut,
            onUpdate: () => { el.textContent = before + format.format(value.n) + after; },
            onComplete: () => done(),
        });
    }, () => { el.textContent = finalText; });
}

function numberIn(el) {
    const match = el?.textContent?.match(NUMBER);

    return match ? Number(match[0].replace(/,/g, '')) : NaN;
}

/* --- Row flash and badge cross-fade (no GSAP) ------------------------------------ */

export function flash(el) {
    if (!el || reduced(el)) return;
    el.removeAttribute('data-motion-flashing');
    void el.offsetWidth;
    el.setAttribute('data-motion-flashing', '');
    const clear = () => el.removeAttribute('data-motion-flashing');
    el.addEventListener('animationend', clear, { once: true });
    setTimeout(clear, 800);
}

export function crossfade(el) {
    if (!el || reduced(el) || typeof el.animate !== 'function') return;
    el.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 150, easing: 'cubic-bezier(0.2, 0, 0, 1)' });
}

/* --- Livewire morph hooks ---------------------------------------------------------- */

let hooked = false;

function hookLivewire() {
    if (hooked || !window.Livewire?.hook) return;
    hooked = true;
    const pending = new Map();

    window.Livewire.hook('morph.updating', ({ el, toEl }) => {
        if (!(el instanceof HTMLElement) || !(toEl instanceof HTMLElement)) return;
        // A morph replaces what a tween was doing: revert it so no inline style is left.
        if (running.has(el)) stop(el);
        if (el.hasAttribute('data-motion-count') && el.dataset.motionCount !== toEl.dataset.motionCount) {
            pending.set(el, { type: 'count', from: numberIn(el) });
        } else if (el.hasAttribute('data-motion-flash') && el.dataset.motionFlash !== toEl.dataset.motionFlash) {
            pending.set(el, { type: 'flash' });
        } else if (el.getAttribute('data-motion') === 'crossfade' && el.textContent.trim() !== toEl.textContent.trim()) {
            pending.set(el, { type: 'crossfade' });
        }
    });

    window.Livewire.hook('morph.updated', ({ el }) => {
        const job = pending.get(el);
        if (!job) return;
        pending.delete(el);
        if (job.type === 'count') countUp(el, job.from);
        else if (job.type === 'flash') flash(el);
        else crossfade(el);
    });
}

hookLivewire();
document.addEventListener('livewire:init', hookLivewire);

if (document.body) {
    watchIndicators();
} else {
    document.addEventListener('DOMContentLoaded', watchIndicators, { once: true });
}

/* For the gallery's replay buttons and for page code. */
window.uiMotion = { loadGsap, slideIndicator, countUp, flash, crossfade, reduced, running: () => running.size };
