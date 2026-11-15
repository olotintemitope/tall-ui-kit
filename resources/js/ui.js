import './anchor';
import { storageKey } from './storage';
import './defer';
import './select';
import './forms';
import './checklist';

/*
 * x-ui behaviours that are too long for an inline x-data. Registered on
 * `alpine:init` so they use Livewire's bundled Alpine, like app.js does.
 */

function isTypingTarget(target) {
    if (!target) {
        return false;
    }

    return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable || target.getAttribute?.('role') === 'combobox';
}

const FOCUS_MEDIA = '(min-width: 1024px)';

function focusStorageKey(area) {
    return storageKey(`focus_${area}`);
}

function readFocus(area) {
    try {
        return window.localStorage.getItem(focusStorageKey(area)) === 'on';
    } catch {
        return false;
    }
}

function writeFocus(area, on) {
    try {
        window.localStorage.setItem(focusStorageKey(area), on ? 'on' : 'off');
    } catch {
        // Storage unavailable: focus mode lasts for this page only.
    }
}

function isVisible(element) {
    return element.getClientRects().length > 0;
}

/* A dialog, menu or listbox is open, so Escape belongs to it. */
function overlayOpen() {
    return [...document.querySelectorAll('[role=dialog], [role=alertdialog], [role=menu], [role=listbox]')].some(isVisible);
}

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    /*
     * Focus mode (partials/shell/*): hides the sidebar and regions marked
     * data-focus-hide, shrinks the top bar and gives the main content full width,
     * from lg only. State is per area in localStorage (<prefix>focus_<area>);
     * the inline script in partials/shell/collapse-script applies it before paint.
     * Toggle with the top bar button, Cmd/Ctrl+., the palette command; Escape exits
     * when no dialog or menu is open. Never turns on by itself.
     */
    Alpine.store('focusMode', {
        on: document.documentElement.dataset.focus === 'on',
        area: null,
        message: '',

        boot(area) {
            this.area = area;
            this.on = readFocus(area);
            document.documentElement.dataset.focus = this.on ? 'on' : 'off';
        },

        available() {
            return this.area !== null && window.matchMedia(FOCUS_MEDIA).matches;
        },

        toggle() {
            this.set(!this.on);
        },

        set(on) {
            if (!this.available() || on === this.on) {
                return;
            }

            this.on = on;
            document.documentElement.dataset.focus = on ? 'on' : 'off';
            writeFocus(this.area, on);
            this.message = on ? 'Focus mode on. Press Escape to exit.' : 'Focus mode off.';

            if (!isTypingTarget(document.activeElement)) {
                requestAnimationFrame(() => document.querySelector('[data-focus-toggle]')?.focus({ preventScroll: true }));
            }
        },

        onKeydown(event) {
            if ((event.metaKey || event.ctrlKey) && !event.altKey && event.key === '.') {
                if (this.available()) {
                    event.preventDefault();
                    this.toggle();
                }

                return;
            }

            if (event.key === 'Escape' && this.on && !event.defaultPrevented && !isTypingTarget(event.target) && !overlayOpen()) {
                this.set(false);
            }
        },
    });

    /*
     * x-ui.form: `dirty` drives the sticky action bar. After a clean submit (no
     * invalid field once the response is rendered) the form is no longer dirty.
     * Focus after a submit is handled once, for every form, by the commit hook in
     * the Focus management section below (error summary, else first invalid field).
     */
    Alpine.data('uiForm', () => ({
        dirty: false,
        submitted: false,
        unhook: null,

        init() {
            if (!window.Livewire || !this.$wire) {
                return;
            }

            const componentId = this.$wire.$id;

            this.unhook = window.Livewire.hook('commit', ({ component, succeed }) => {
                if (component.id !== componentId || !this.submitted) {
                    return;
                }

                succeed(() => {
                    this.submitted = false;
                    if (!this.$el.querySelector('[aria-invalid="true"]') && !this.$el.hasAttribute('data-keep-dirty')) {
                        this.dirty = false;
                    }
                });
            });
        },

        destroy() {
            if (typeof this.unhook === 'function') {
                this.unhook();
            }
        },
    }));

    /*
     * Command palette over the sidebar destinations (partials/shell/command-palette).
     * Opens with Cmd+K / Ctrl+K or an `open-command-palette` window event.
     * `items` is a list of { label, href, section }; `commands` a list of
     * { command, label, section } run in place (only `focus-mode` today, from lg).
     */
    Alpine.data('commandPalette', (items = [], commands = []) => ({
        open: false,
        query: '',
        active: 0,
        items,
        commands,

        get entries() {
            const commands = this.commands
                .filter((item) => item.command !== 'focus-mode' || Alpine.store('focusMode').available())
                .map((item) => ({
                    ...item,
                    key: `command:${item.command}`,
                    label: item.command === 'focus-mode' && Alpine.store('focusMode').on ? 'Exit focus mode' : item.label,
                }));

            return [...this.items.map((item) => ({ ...item, key: item.href })), ...commands];
        },

        get results() {
            const query = this.query.trim().toLowerCase();

            if (query === '') {
                return this.entries;
            }

            return this.entries.filter((item) =>
                `${item.label} ${item.section ?? ''}`.toLowerCase().includes(query),
            );
        },

        show() {
            this.open = true;
            this.query = '';
            this.active = 0;
            this.$nextTick(() => this.$refs.query?.focus());
        },

        hide() {
            this.open = false;
        },

        onGlobalKeydown(event) {
            if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                this.open ? this.hide() : this.show();
                return;
            }

            if (event.key === '/' && !this.open && !isTypingTarget(event.target)) {
                event.preventDefault();
                this.show();
            }
        },

        move(step) {
            const count = this.results.length;

            if (count === 0) {
                return;
            }

            this.active = (this.active + step + count) % count;
            this.$nextTick(() => {
                this.$refs.list?.querySelector(`[data-index="${this.active}"]`)?.scrollIntoView({ block: 'nearest' });
            });
        },

        go(index = this.active) {
            const item = this.results[index];

            if (!item) {
                return;
            }

            this.hide();

            if (item.command === 'focus-mode') {
                // After the palette's focus trap has handed focus back (its leave transition is 100ms).
                setTimeout(() => Alpine.store('focusMode').toggle(), 150);
                return;
            }

            window.location.assign(item.href);
        },
    }));
});

/*
 * x-ui.copy-button, x-ui.copy-field and x-ui.copy-text (docs/ui-kit-copy-icons.md).
 *
 * States: idle; copied (check icon, "Copied", emerald-700, a polite "Copied to
 * clipboard", back to idle after 2s, a repeat click restarts the timer); failed
 * (Clipboard API missing or denied: "Press ⌘C to copy", or Ctrl+C off a Mac, with
 * the value selected so the shortcut works while focus stays on the button). Every
 * label is drawn in one grid cell, so the widest reserves the width and nothing moves.
 *
 * Options: value (copied as given; without it the visible value, x-ref="field" or
 * x-ref="value", so a masked value copies its mask), toast (also a "Copied to clipboard." toast, for copies from
 * a menu that closes), force ('copied' | 'failed', gallery only: the state is drawn
 * and never changes).
 */
const COPY_RESET_MS = 2000;

function isMac() {
    const platform = navigator.userAgentData?.platform ?? navigator.platform ?? '';

    return /mac|iphone|ipad|ipod/i.test(platform);
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiCopy', (options = {}) => ({
        state: options.force ?? 'idle',
        tip: false,
        message: '',
        shortcut: isMac() ? '⌘C' : 'Ctrl+C',
        timer: null,

        get copied() {
            return this.state === 'copied';
        },

        get failed() {
            return this.state === 'failed';
        },

        text() {
            if (options.value !== undefined && options.value !== null) {
                return String(options.value);
            }

            // The visible value: the field's text, or the inline value's rendered text (a mask stays a mask).
            if (this.$refs.field) {
                return this.$refs.field.value;
            }

            return (this.$refs.value?.innerText ?? '').trim();
        },

        async copy() {
            if (options.force) {
                return;
            }
            const value = this.text();
            clearTimeout(this.timer);

            try {
                if (!window.isSecureContext || !navigator.clipboard?.writeText) {
                    throw new Error('Clipboard API unavailable');
                }
                await navigator.clipboard.writeText(value);
            } catch {
                this.select(value);
                this.announce('failed', `Copy failed. Press ${this.shortcut} to copy.`);

                return;
            }

            this.announce('copied', 'Copied to clipboard');
            if (options.toast) {
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Copied to clipboard.' } }));
            }
            this.timer = setTimeout(() => {
                this.state = 'idle';
            }, COPY_RESET_MS);
        },

        /* The live region is cleared first, so a repeat copy is announced again. */
        announce(state, message) {
            this.state = state;
            this.message = '';
            setTimeout(() => {
                this.message = message;
            }, 50);
        },

        /* Select the value without moving focus: the field's own text, and a hidden span for the shortcut. */
        select(value) {
            const field = this.$refs.field;
            if (field && typeof field.setSelectionRange === 'function') {
                field.setSelectionRange(0, field.value.length);
            }
            const span = this.$refs.selectable;
            if (!span) {
                return;
            }
            span.textContent = value;
            const range = document.createRange();
            range.selectNodeContents(span);
            const selection = window.getSelection();
            selection?.removeAllRanges();
            selection?.addRange(range);
        },

        /* A failed state stays while it is useful, until focus leaves the control. */
        leave(event) {
            if (this.failed && !options.force && !this.$root.contains(event.relatedTarget)) {
                this.state = 'idle';
            }
        },

        destroy() {
            clearTimeout(this.timer);
        },
    }));
});

/*
 * x-ui.progress-bar: a 2px indeterminate bar under the top bar while a Livewire
 * request or a wire:navigate visit runs. Shown only after 200ms, so quick requests
 * show nothing; hidden as soon as the last pending request settles.
 */
const PROGRESS_DELAY = 200;

document.addEventListener('alpine:init', () => {
    window.Alpine.store('progress', {
        visible: false,
        pending: 0,
        timer: null,

        start() {
            this.pending += 1;
            if (this.pending === 1) {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => {
                    this.visible = this.pending > 0;
                }, PROGRESS_DELAY);
            }
        },

        done() {
            this.pending = Math.max(0, this.pending - 1);
            if (this.pending === 0) {
                this.reset();
            }
        },

        reset() {
            clearTimeout(this.timer);
            this.pending = 0;
            this.visible = false;
        },
    });
});

function progress() {
    return window.Alpine?.store?.('progress');
}

document.addEventListener('livewire:init', () => {
    window.Livewire.hook('request', ({ succeed, fail }) => {
        progress()?.start();
        succeed(() => progress()?.done());
        fail(() => progress()?.done());
    });
});

document.addEventListener('livewire:navigating', () => progress()?.start());
document.addEventListener('livewire:navigated', () => progress()?.reset());

/*
 * Focus management (docs/ui-kit.md "Focus behaviour").
 *
 * window.uiFocus.focusAfter(target, fallbacks): focuses the first of target and the
 *   fallbacks that is still in the document and visible. Programmatic focus after a
 *   keyboard action shows the :focus-visible ring; after a mouse action it does not
 *   (the browser's own heuristic, so nothing is forced).
 * window.uiFocus.restore(): when focus has fallen to <body>, moves it to the page's
 *   main heading (or #main).
 *
 * Livewire: on every request the focused control is remembered by id, or by the
 * wire:key of its item and its position in it. If the morph replaced it, focus goes
 * back to its replacement; if its item was removed (a deleted row, a removed filter
 * token), to the same control in the next item, then the previous one, then the
 * list's heading, then the page heading. Never to <body>.
 */
const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type=hidden]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function isUsable(el) {
    return el instanceof HTMLElement && el.isConnected && el.getClientRects().length > 0;
}

function mainHeading() {
    const heading = document.querySelector('#main h1') ?? document.querySelector('main h1') ?? document.querySelector('#main');
    if (heading && !heading.hasAttribute('tabindex')) {
        heading.setAttribute('tabindex', '-1');
    }

    return heading;
}

/*
 * Where a control sat, recorded when it loses focus (it is still in the document
 * then): the focusable controls after it in its section, the focusable controls
 * before it, and the section's heading. A dialog trigger that a Livewire action
 * removed is resolved through this, so focus goes to the next control where the
 * trigger was, never to <body>.
 */
const positions = new WeakMap();

function sectionOf(el) {
    return el.closest('[aria-labelledby], section, [role=region], form, main') ?? document.body;
}

function headingOf(container) {
    const labelled = container.getAttribute?.('aria-labelledby');
    const heading = (labelled && document.getElementById(labelled.split(' ')[0])) || container.querySelector?.('h1, h2, h3');
    if (heading && !heading.hasAttribute('tabindex')) heading.setAttribute('tabindex', '-1');

    return heading ?? null;
}

function positionOf(el) {
    const container = sectionOf(el);
    const controls = [...container.querySelectorAll(FOCUSABLE)].filter((node) => !node.closest('dialog, [role=dialog]'));
    const index = controls.indexOf(el);

    return {
        after: index < 0 ? [] : controls.slice(index + 1, index + 4),
        before: index < 0 ? [] : controls.slice(Math.max(0, index - 3), index).reverse(),
        heading: headingOf(container),
    };
}

document.addEventListener('focusout', (event) => {
    const el = event.target;
    if (el instanceof HTMLElement && el.isConnected && !el.closest('dialog, [role=dialog]')) {
        positions.set(el, positionOf(el));
    }
}, true);

function focusAfter(target, fallbacks = []) {
    const remembered = target && !isUsable(target) ? positions.get(target) : null;
    const candidates = [
        target,
        ...fallbacks,
        ...(remembered ? [...remembered.after, ...remembered.before, remembered.heading] : []),
        mainHeading(),
        document.getElementById('main'),
    ];

    for (const el of candidates) {
        if (!isUsable(el) || el.closest('[inert]')) continue;
        if (!el.matches(FOCUSABLE) && !el.hasAttribute('tabindex')) {
            el.setAttribute('tabindex', '-1');
        }
        el.focus({ preventScroll: false });
        if (document.activeElement === el) return el;
    }

    return null;
}

function restore() {
    if (!document.activeElement || document.activeElement === document.body) {
        focusAfter(null);
    }
}

/*
 * A way to find a control again after a morph replaced or removed it: its id, or the
 * wire:key of its closest keyed item plus its position among that item's controls.
 */
function signature(el) {
    if (!el) return null;
    if (el.id) return { id: el.id };
    const item = el.closest('[wire\\:key]');
    if (!item) return null;

    return { key: item.getAttribute('wire:key'), index: Math.max(0, [...item.querySelectorAll(FOCUSABLE)].indexOf(el)) };
}

function resolve(sig) {
    if (!sig) return null;
    if (sig.id) return document.getElementById(sig.id);
    const item = [...document.querySelectorAll('[wire\\:key]')].find((node) => node.getAttribute('wire:key') === sig.key);
    if (!item) return null;
    const controls = [...item.querySelectorAll(FOCUSABLE)];

    return controls[sig.index] ?? controls[0] ?? null;
}

/*
 * Where focus goes if the focused control's keyed item disappears: the same control
 * in the next item, then the previous one, then the list heading (the list's
 * aria-labelledby, or its section's heading).
 */
function plan(focused) {
    const plans = [signature(focused)];
    const item = focused.closest('[wire\\:key]');
    if (item) {
        const index = Math.max(0, [...item.querySelectorAll(FOCUSABLE)].indexOf(focused));
        for (const sibling of [item.nextElementSibling, item.previousElementSibling]) {
            if (sibling?.hasAttribute('wire:key')) plans.push({ key: sibling.getAttribute('wire:key'), index });
        }
        const list = item.parentElement;
        const labelled = list?.closest('[aria-labelledby]');
        const heading = labelled ? document.getElementById(labelled.getAttribute('aria-labelledby')) : list?.closest('section')?.querySelector('h2, h3');
        if (heading) {
            if (!heading.id) heading.id = `focus-heading-${Math.random().toString(36).slice(2, 8)}`;
            if (!heading.hasAttribute('tabindex')) heading.setAttribute('tabindex', '-1');
            plans.push({ id: heading.id });
        }
    }

    return plans.filter(Boolean);
}

window.uiFocus = { focusAfter, restore, signature, resolve };

/*
 * The control that last had focus. wire:submit disables a form's submit button
 * while the request runs, and the browser may drop focus to <body> before the
 * request's commit hook sees it, so the hook falls back to this.
 */
let lastFocused = null;

/* The control a dialog was opened from, so an action inside the dialog can fall back to where it was. */
const openers = new WeakMap();

document.addEventListener('focusin', (event) => {
    const el = event.target;
    if (!(el instanceof HTMLElement) || el === document.body) return;
    const dialog = el.closest('dialog, [role=dialog]');
    if (dialog && lastFocused && !lastFocused.closest('dialog, [role=dialog]')) {
        openers.set(dialog, lastFocused);
    }
    lastFocused = el;
}, true);

/*
 * A submitted form decides where focus goes once the response is rendered: the error
 * summary when there are more than 3 errors (x-ui.error-summary only renders then),
 * otherwise the first invalid field, otherwise it stays on the submit button.
 */
let submittedForm = null;

document.addEventListener('submit', (event) => {
    submittedForm = event.target;
}, true);

function focusFormResult(form) {
    const summary = form.querySelector('[data-error-summary]');
    if (summary && isUsable(summary)) {
        summary.scrollIntoView({ block: 'center' });
        summary.focus({ preventScroll: true });

        return document.activeElement === summary;
    }
    const invalid = form.querySelector('[aria-invalid="true"]');
    if (invalid) {
        const target = invalid.matches('fieldset') ? invalid.querySelector('input:not(:disabled)') : invalid;
        const box = target?.closest('[data-control-box], [data-invalid]') ?? target;
        box?.scrollIntoView({ block: 'center' });
        target?.focus({ preventScroll: true });

        return Boolean(target) && document.activeElement === target;
    }

    return false;
}

/*
 * Runs for one request once Livewire has morphed and rendered it (the commit hook's
 * succeed callback fires after the morph, in the next animation frame), and once more
 * a frame later in case an Alpine effect or a re-enabled control moved focus again.
 * Every request carries its own state, so overlapping requests (a fast run of
 * deletions) never share or clear each other's plans.
 */
function settle(state) {
    const { focused, plans, form } = state;

    if (form) {
        if (form.isConnected && focusFormResult(form)) return true;
    }
    if (focused?.isConnected && document.activeElement === focused) return true;
    if (document.activeElement && document.activeElement !== document.body && !form) return true;
    // Livewire disables a form's submit buttons during wire:submit; it is enabled again by now.
    if (focused?.isConnected && isUsable(focused) && !focused.disabled) {
        focused.focus({ preventScroll: true });
        if (document.activeElement === focused) return true;
    }
    // An action inside a dialog that removed its trigger: the trigger's position decides.
    focusAfter(state.opener ?? null, plans.map(resolve));

    return true;
}

function settleTwice(state) {
    settle(state);
    requestAnimationFrame(() => {
        // Only correct a second time if focus has fallen to <body> since (or the form's target moved).
        if (state.form ? !state.form.contains(document.activeElement) || document.activeElement.matches('button[type=submit]') : document.activeElement === document.body) {
            settle(state);
        }
    });
}

let focusHooksRegistered = false;

function registerFocusHooks() {
    if (focusHooksRegistered || !window.Livewire?.hook) return;
    focusHooksRegistered = true;
    window.Livewire.hook('commit', ({ component, succeed, fail }) => {
        const root = component?.el;
        const form = submittedForm && (!root || root.contains(submittedForm)) ? submittedForm : null;
        if (form) submittedForm = null;

        let focused = document.activeElement;
        if (!focused || focused === document.body) {
            focused = lastFocused?.isConnected && (!root || root.contains(lastFocused)) ? lastFocused : null;
        }
        if (!focused && !form) {
            return;
        }

        const dialog = focused?.closest('dialog, [role=dialog]');
        const opener = dialog ? openers.get(dialog) ?? null : null;
        // Inside a dialog the dialog's own controls are no fallback once it closes.
        const state = { focused, plans: focused && !dialog ? plan(focused) : [], form, opener };
        succeed(() => settleTwice(state));
        fail(() => settleTwice({ ...state, form: null }));
    });
}

// Livewire may already have started when this module runs.
registerFocusHooks();
document.addEventListener('livewire:init', registerFocusHooks);

/*
 * Last input type, on <html data-input>: programmatic focus targets (page headings,
 * dialog titles, the error summary) show the focus ring only after a keyboard action
 * (app.css). Kept across wire:navigate, which replaces the page's <html> attributes.
 */
const INPUT_KEY = 'ui.input';
const setInput = (type) => {
    document.documentElement.dataset.input = type;
    try {
        sessionStorage.setItem(INPUT_KEY, type);
    } catch {
        // Storage unavailable: the attribute alone still works for this page.
    }
};
const restoreInput = () => {
    try {
        document.documentElement.dataset.input = sessionStorage.getItem(INPUT_KEY) || 'pointer';
    } catch {
        document.documentElement.dataset.input = 'pointer';
    }
};
document.addEventListener('keydown', (event) => {
    if (!event.metaKey && !event.ctrlKey && !event.altKey) setInput('keyboard');
}, true);
document.addEventListener('pointerdown', () => setInput('pointer'), true);
restoreInput();
document.addEventListener('livewire:navigated', restoreInput);

/* After wire:navigate, focus moves to the new page's h1 (or #main), as a full page load would. */
document.addEventListener('livewire:navigated', () => {
    if (window.__uiFirstNavigation === undefined) {
        window.__uiFirstNavigation = false;

        return;
    }
    requestAnimationFrame(() => mainHeading()?.focus({ preventScroll: true }));
});

/*
 * Toasts never take focus. Alt+T (Option+T) jumps to the newest toast's dismiss
 * button; when that toast goes away, focus returns to where the user was.
 */
let beforeToasts = null;

document.addEventListener('keydown', (event) => {
    if (!event.altKey || event.ctrlKey || event.metaKey || event.code !== 'KeyT') {
        return;
    }
    const buttons = [...document.querySelectorAll('[data-toast] button')].filter(isUsable);
    if (!buttons.length) {
        return;
    }
    event.preventDefault();
    beforeToasts = document.activeElement;
    buttons[buttons.length - 1].focus();
});

// When the focused toast is dismissed (by its button or the 4s timer), return focus.
document.addEventListener('alpine:initialized', () => {
    const region = document.querySelector('[data-toast-region]');
    if (!region) {
        return;
    }
    let focusedToast = null;
    region.addEventListener('focusin', (event) => {
        focusedToast = event.target;
    });
    new MutationObserver(() => {
        if (focusedToast && !focusedToast.isConnected) {
            focusedToast = null;
            if (!document.activeElement || document.activeElement === document.body) {
                focusAfter(beforeToasts);
            }
            beforeToasts = null;
        }
    }).observe(region, { childList: true, subtree: true });
});
