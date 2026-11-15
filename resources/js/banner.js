import { storageKey } from './storage';

/*
 * x-ui.banner dismissal (docs/components/workflow.md, "Banner"). The same store pattern
 * as x-ui.checklist: persist "local" (localStorage <prefix>dismiss_<key>), "server"
 * ($wire.dismissNotice(key), the WithDismissals trait writing to the bound
 * DismissStore; localStorage outside Livewire or when the server refuses) or "none".
 */

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function readLocal(key) {
    try {
        return window.localStorage.getItem(storageKey(`dismiss_${key}`)) === '1';
    } catch {
        return false;
    }
}

function writeLocal(key) {
    try {
        window.localStorage.setItem(storageKey(`dismiss_${key}`), '1');
    } catch {
        // Storage unavailable: dismissed for this page only.
    }
}

/* The first visible control after `root` in document order, else the page's h1. */
function nextFocus(root) {
    for (const el of document.querySelectorAll(FOCUSABLE)) {
        if (root.contains(el) || el.closest('[inert]')) continue;
        if (!(root.compareDocumentPosition(el) & Node.DOCUMENT_POSITION_FOLLOWING)) continue;
        if (el.getClientRects().length > 0) return el;
    }

    return document.querySelector('main h1, h1');
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiBanner', ({ key = null, persist = 'none' } = {}) => ({
        hidden: false,

        init() {
            this.hidden = key !== null && this.usesLocal() && readLocal(key);
        },

        wire() {
            try {
                return this.$wire ?? null;
            } catch {
                return null;
            }
        },

        usesLocal() {
            return persist === 'local' || (persist === 'server' && !this.wire());
        },

        dismiss() {
            const next = nextFocus(this.$root);
            this.hidden = true;
            this.$root.dispatchEvent(new CustomEvent('ui-dismiss', { bubbles: true, detail: { key } }));
            this.$nextTick(() => {
                if (window.uiFocus?.focusAfter) window.uiFocus.focusAfter(next);
                else if (next) {
                    if (!next.matches(FOCUSABLE)) next.setAttribute('tabindex', '-1');
                    next.focus();
                }
            });
            if (key === null || persist === 'none') return;
            const wire = persist === 'server' ? this.wire() : null;
            if (wire) {
                Promise.resolve(wire.dismissNotice(key)).catch(() => writeLocal(key));

                return;
            }
            writeLocal(key);
        },
    }));
});
