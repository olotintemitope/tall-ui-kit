import { storageKey } from './storage';

/*
 * x-ui.checklist (docs/components/feedback.md, "Checklist").
 *
 * uiChecklist: collapse and dismiss, kept per `name`:
 *   persist "local"   localStorage <prefix>checklist_<name> = {"collapsed":bool,"dismissed":bool}
 *   persist "server"  $wire.checklistCollapse / checklistDismiss (the WithChecklist trait);
 *                     outside a Livewire component it falls back to localStorage
 *   persist "none"    nothing kept
 * Each change also dispatches a bubbling `ui-checklist` event, detail
 * { name, action: 'collapse' | 'dismiss', collapsed }, so an app can store it its own way.
 *
 * Morph hook: when a Livewire update turns a step done ([data-done] 0 -> 1), the row gets
 * [data-checklist-flip] (the check scales in, CSS on the kit motion tokens), the
 * checklist's polite status names the step, and if focus was in that row (its action
 * is gone now) it stays in the row instead of falling to <body> or jumping to the next
 * step's action. The progress bar keeps its element (a stable id), so its fill
 * transitions to the new value.
 */

function readLocal(name) {
    try {
        return JSON.parse(window.localStorage.getItem(storageKey(`checklist_${name}`)) ?? '{}') ?? {};
    } catch {
        return {};
    }
}

function writeLocal(name, patch) {
    try {
        window.localStorage.setItem(storageKey(`checklist_${name}`), JSON.stringify({ ...readLocal(name), ...patch }));
    } catch {
        // Storage unavailable: the choice lasts for this page only.
    }
}

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

/* The first visible control after `root` in document order, outside it. */
function nextControlAfter(root) {
    for (const el of document.querySelectorAll(FOCUSABLE)) {
        if (root.contains(el) || el.closest('[inert]')) continue;
        if (!(root.compareDocumentPosition(el) & Node.DOCUMENT_POSITION_FOLLOWING)) continue;
        if (el.getClientRects().length > 0) return el;
    }

    return null;
}

function focusEl(el) {
    if (window.uiFocus?.focusAfter) {
        window.uiFocus.focusAfter(el);
    } else if (el) {
        el.focus();
    }
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiChecklist', ({ name = null, persist = 'none', collapsed = false } = {}) => ({
        collapsed,
        hidden: false,

        init() {
            if (this.usesLocal()) {
                const saved = readLocal(name);
                if (typeof saved.collapsed === 'boolean' && this.$root.querySelector('[data-checklist-toggle]')) {
                    this.collapsed = saved.collapsed;
                }
                this.hidden = saved.dismissed === true && this.$root.dataset.complete === '1' && this.$root.dataset.dismissible === '1';
            }
        },

        /* The surrounding Livewire component, or null ($wire throws outside one). */
        wire() {
            try {
                return this.$wire ?? null;
            } catch {
                return null;
            }
        },

        usesLocal() {
            return name !== null && (persist === 'local' || (persist === 'server' && !this.wire()));
        },

        toggle() {
            this.collapsed = !this.collapsed;
            this.save('collapse');
        },

        dismiss() {
            if (this.$root.dataset.complete !== '1') return;

            const next = nextControlAfter(this.$root);
            this.hidden = true;
            this.save('dismiss');
            // The checklist is gone: focus the next control on the page, else the page heading.
            this.$nextTick(() => focusEl(next));
        },

        save(action) {
            this.$root.dispatchEvent(new CustomEvent('ui-checklist', { bubbles: true, detail: { name, action, collapsed: this.collapsed } }));

            if (name === null || persist === 'none') return;

            const wire = persist === 'server' ? this.wire() : null;
            if (wire) {
                const call = action === 'dismiss' ? wire.checklistDismiss(name) : wire.checklistCollapse(name, this.collapsed);
                // The server refused or failed: keep the choice in this browser instead.
                Promise.resolve(call).catch(() => writeLocal(name, action === 'dismiss' ? { dismissed: true } : { collapsed: this.collapsed }));

                return;
            }

            writeLocal(name, action === 'dismiss' ? { dismissed: true } : { collapsed: this.collapsed });
        },
    }));
});

/* Steps that turned done in the current morph, flushed once in the next frame. */
let flips = [];
let completed = new Set();
let scheduled = false;

function announce(root, text) {
    const live = root?.querySelector('[data-checklist-live]');
    if (!live) return;
    live.textContent = '';
    requestAnimationFrame(() => {
        live.textContent = text;
    });
}

function flush() {
    scheduled = false;
    const byRoot = new Map();

    for (const flip of flips) {
        const root = flip.row.closest('[data-checklist]');
        if (!root) continue;
        byRoot.set(root, [...(byRoot.get(root) ?? []), flip]);

        // Focus was on the step's action, which a done step no longer has: keep it in the row.
        if (flip.hadFocus && !flip.row.contains(document.activeElement)) {
            focusEl(flip.row);
        }
    }

    for (const [root, list] of byRoot) {
        const parts = list.map((flip) => `${flip.row.dataset.label}: done.`);
        if (completed.has(root)) {
            parts.push(root.querySelector('[data-checklist-complete] p')?.textContent.trim() ?? '');
        }
        announce(root, parts.filter(Boolean).join(' '));
    }

    flips = [];
    completed = new Set();
}

function schedule() {
    if (scheduled) return;
    scheduled = true;
    requestAnimationFrame(flush);
}

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('morph.updating', ({ el, toEl }) => {
        if (!(el instanceof HTMLElement) || !(toEl instanceof HTMLElement)) return;

        if (el.hasAttribute('data-checklist-item')) {
            if (el.dataset.done === '0' && toEl.dataset.done === '1') {
                toEl.setAttribute('data-checklist-flip', '');
                flips.push({ row: el, hadFocus: el.contains(document.activeElement) });
                schedule();
            } else {
                toEl.removeAttribute('data-checklist-flip');
            }
        } else if (el.hasAttribute('data-checklist') && el.dataset.complete === '0' && toEl.dataset.complete === '1') {
            completed.add(el);
            schedule();
        }
    });
});
