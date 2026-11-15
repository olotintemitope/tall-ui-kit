/*
 * Deferred Alpine init for long pages (docs/ui-kit.md "Performance").
 *
 * An element with `data-ui-defer` is rendered by the server as usual (its HTML, text
 * and native controls are there from the first paint) but Alpine and Livewire's
 * wire: directives inside it start later, through Alpine's own `deferInit` (the
 * mechanism Livewire uses for components waiting on assets), when the first of these
 * happens:
 *
 *   - it comes within a viewport's height of the screen;
 *   - focus moves into it, or a pointer or key press lands in it (before the click or
 *     key reaches its controls);
 *   - idle time after load, in document order, as many per idle period as fit;
 *   - a dialog inside it is opened by name (open-dialog, open-confirm): it starts and
 *     the event is sent again, so a button in one section always reaches a dialog in
 *     another (an unknown name starts everything).
 *
 * So the first long task only initialises what is on screen, and the rest starts in
 * small pieces, never as a burst on the first tap. Until it starts, the element also
 * skips style, layout and paint while off screen (`content-visibility: auto` in
 * app.css, lifted by `data-ui-live`). Livewire morphs inside a waiting element are
 * queued by Alpine and replayed when it starts; a `ui:deferred-start` event (bubbling,
 * from the element) follows each start. `window.uiDefer.flush()` starts everything at
 * once (the browser audits call it before measuring); `pending()` counts what waits.
 */

/*
 * `data-ui-live` lifts the element's content-visibility (app.css). A Livewire morph
 * resets attributes to the server's, so it is put back after each morph of the element.
 */
function markLive(el) {
    el._uiLive = true;
    el.setAttribute('data-ui-live', '');
}

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('morph.updated', ({ el }) => {
        if (el._uiLive && !el.hasAttribute('data-ui-live')) markLive(el);
    });
});

function setup(Alpine) {
    if (!Alpine?.deferInit || !Alpine.interceptInit || window.uiDefer) {
        return;
    }

    const waiting = new Set();
    const queue = [];
    let pumping = false;

    const observer = typeof IntersectionObserver === 'undefined'
        ? null
        : new IntersectionObserver((entries) => entries.forEach((entry) => entry.isIntersecting && release(entry.target)), { rootMargin: '100% 0px' });

    function release(el) {
        const start = el._uiDeferStart;
        if (!start) return;
        delete el._uiDeferStart;
        waiting.delete(el);
        observer?.unobserve(el);
        markLive(el);
        // Alpine's own first bindings in it are not user changes (motion.js skips them).
        el._uiSettling = true;
        start();
        // After Alpine has started it: page scripts that ran on DOMContentLoaded
        // (placeholders) catch up with values wire:model has just set.
        setTimeout(() => {
            el.dispatchEvent(new CustomEvent('ui:deferred-start', { bubbles: true }));
            requestAnimationFrame(() => requestAnimationFrame(() => { delete el._uiSettling; }));
        }, 0);
    }

    /* Starts every waiting element that contains `node`, outermost first. */
    function releaseAround(node) {
        const chain = [];
        for (let el = node instanceof Element ? node : node?.parentElement; el; el = el.parentElement) {
            if (el._uiDeferStart) chain.unshift(el);
        }
        chain.forEach(release);
    }

    function flush() {
        [...waiting].forEach(release);
    }

    const idle = window.requestIdleCallback
        ? (callback) => window.requestIdleCallback(callback, { timeout: 1000 })
        : (callback) => setTimeout(() => callback({ timeRemaining: () => 0 }), 50);

    function pump() {
        if (pumping) return;
        pumping = true;
        idle((deadline) => {
            pumping = false;
            do {
                const el = queue.shift();
                if (el?._uiDeferStart) release(el);
            } while (queue.length && deadline.timeRemaining() > 8);
            if (queue.length) pump();
        });
    }

    function startPumpAfterLoad() {
        if (document.readyState === 'complete') pump();
        else window.addEventListener('load', pump, { once: true });
    }

    Alpine.interceptInit((el) => {
        if (el._uiDeferSeen || typeof el.hasAttribute !== 'function' || !el.hasAttribute('data-ui-defer')) return;
        // A Livewire morph clones the new HTML (detached) and keeps what its bindings evaluate
        // to (x-bind, x-text). A deferred clone kept the server's attributes instead, so every
        // morph reset bound values inside a started section (a select's label, aria-expanded).
        // Clones of a section still waiting are ignored by Alpine itself.
        if (!el.isConnected) return;
        // Seen once: when Alpine initialises the element for real it passes through here again.
        el._uiDeferSeen = true;
        Alpine.deferInit(el, new Promise((resolve) => { el._uiDeferStart = resolve; }));
        waiting.add(el);
        queue.push(el);
        observer?.observe(el);
        startPumpAfterLoad();
    });

    ['pointerdown', 'keydown', 'focusin'].forEach((type) => document.addEventListener(type, (event) => {
        if (!waiting.size) return;
        releaseAround(event.target);
        if (type === 'keydown') releaseAround(document.activeElement);
    }, { capture: true }));

    const isWaiting = (node) => {
        for (let el = node; el; el = el.parentElement) if (el._uiDeferStart) return true;

        return false;
    };

    ['open-dialog', 'open-confirm'].forEach((type) => window.addEventListener(type, (event) => {
        if (!waiting.size) return;
        const name = typeof event.detail === 'string' ? event.detail : event.detail?.name;
        const hosts = name ? [...document.querySelectorAll(`[data-dialog-host="${CSS.escape(String(name))}"]`)] : [];
        // The dialog is live already: nothing to do.
        if (hosts.length && !hosts.some(isWaiting)) return;
        event.stopImmediatePropagation();
        if (hosts.length) hosts.forEach(releaseAround);
        else flush(); // An alias or unnamed dialog: start everything.
        const target = event.target instanceof Node && event.target.isConnected ? event.target : window;
        setTimeout(() => target.dispatchEvent(new CustomEvent(type, { detail: event.detail, bubbles: true, composed: true })), 0);
    }, { capture: true }));

    window.uiDefer = { flush, pending: () => waiting.size };
}

document.addEventListener('alpine:init', () => setup(window.Alpine));
