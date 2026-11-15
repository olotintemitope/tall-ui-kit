/*
 * Selects (x-ui.select), menus and popovers open BELOW their trigger, always. There is
 * no native <select> in the app (PopupPlacementTest).
 *
 * Floating panels anchor with `x-anchor.bottom-*.noflip`, so Floating UI never moves
 * them above the trigger. When there is no room below, revealBelow() scrolls the
 * nearest scroll container (usually the page) just enough to show the whole panel,
 * without pushing the trigger off the top. Anchored panels follow the scroll
 * (autoUpdate); the table row menu re-measures through its own callback.
 *
 * Use from Alpine: x-effect="open && $revealBelow($el)".
 */

const MARGIN = 8;

const scrollParent = (el) => {
    for (let node = el?.parentElement; node && node !== document.body; node = node.parentElement) {
        const { overflowY } = getComputedStyle(node);

        if (/(auto|scroll|overlay)/.test(overflowY) && node.scrollHeight > node.clientHeight) {
            return node;
        }
    }

    return document.scrollingElement || document.documentElement;
};

/**
 * @param {HTMLElement} panel  the open floating panel
 * @param {HTMLElement|null} trigger  what it is anchored to (keeps it on screen)
 * @returns {Promise<number>} pixels scrolled
 */
export function revealBelow(panel, trigger = null) {
    return new Promise((resolve) => {
        // Two frames: x-show has displayed the panel and Floating UI has placed it.
        requestAnimationFrame(() => requestAnimationFrame(() => {
            if (!panel || panel.getClientRects().length === 0) {
                return resolve(0);
            }

            const container = scrollParent(trigger || panel);
            const isPage = container === document.scrollingElement || container === document.documentElement;
            const bottomEdge = isPage ? window.innerHeight : container.getBoundingClientRect().bottom;
            const topEdge = isPage ? 0 : container.getBoundingClientRect().top;

            const panelRect = panel.getBoundingClientRect();
            let overflow = panelRect.bottom - (bottomEdge - MARGIN);

            if (overflow <= 0) {
                return resolve(0);
            }

            // Never scroll the trigger above the visible area.
            if (trigger) {
                overflow = Math.min(overflow, Math.max(0, trigger.getBoundingClientRect().top - topEdge - MARGIN));
            }

            if (overflow <= 0) {
                return resolve(0);
            }

            const before = isPage ? window.scrollY : container.scrollTop;
            // Instant, not smooth: the panel must be fully visible the moment it opens.
            const options = { top: overflow, behavior: 'instant' };
            isPage ? window.scrollBy(options) : container.scrollBy(options);
            const after = isPage ? window.scrollY : container.scrollTop;

            resolve(after - before);
        }));
    });
}

window.uiRevealBelow = revealBelow;

document.addEventListener('alpine:init', () => {
    window.Alpine.magic('revealBelow', () => (panel, trigger = null) => revealBelow(panel, trigger));
});
