import { storageKey } from './storage';

/*
 * Colour scheme (docs/theming.md "Dark mode"). x-ui.head has already set
 * html[data-color-scheme] before first paint; this module owns changes after that:
 *
 *   window.uiColorScheme.get()        'light' | 'dark' | 'system'
 *   window.uiColorScheme.resolved()   'light' | 'dark' (system resolved through the OS)
 *   window.uiColorScheme.set('dark')  sets the attribute and localStorage, then
 *                                     dispatches `ui-color-scheme` on window
 *
 * `ui-color-scheme` also fires when the OS switches while the choice is "system", so
 * anything drawn by script can follow (the kit's charts read tokens through var() and
 * need nothing). The Alpine component `uiThemeToggle` backs x-ui.theme-toggle.
 */

const root = document.documentElement;
const media = window.matchMedia?.('(prefers-color-scheme: dark)');

function allowed() {
    return (root.dataset.colorSchemes ?? 'light dark system').split(' ').filter(Boolean);
}

function get() {
    return root.dataset.colorScheme ?? 'system';
}

function resolved(scheme = get()) {
    if (scheme === 'system') return media?.matches ? 'dark' : 'light';

    return scheme === 'dark' ? 'dark' : 'light';
}

function emit() {
    window.dispatchEvent(new CustomEvent('ui-color-scheme', { detail: { scheme: get(), resolved: resolved() } }));
}

function set(scheme) {
    if (!allowed().includes(scheme)) return false;
    root.dataset.colorScheme = scheme;

    try {
        window.localStorage.setItem(storageKey('color_scheme'), scheme);
    } catch {
        // Storage unavailable: the choice lasts for this page only.
    }

    emit();

    return true;
}

media?.addEventListener?.('change', () => {
    if (get() === 'system') emit();
});

/* Another tab changed the choice: follow it. */
window.addEventListener('storage', (event) => {
    if (event.key !== storageKey('color_scheme') || !allowed().includes(event.newValue)) return;
    root.dataset.colorScheme = event.newValue;
    emit();
});

window.uiColorScheme = { get, set, resolved, allowed };

const NAMES = { light: 'Light', dark: 'Dark', system: 'System' };

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiThemeToggle', ({ persist = 'local', announceLabel = 'Colour scheme' } = {}) => ({
        value: get(),

        init() {
            this.sync = () => {
                this.value = get();
            };
            window.addEventListener('ui-color-scheme', this.sync);
        },

        destroy() {
            window.removeEventListener('ui-color-scheme', this.sync);
        },

        wire() {
            try {
                return this.$wire ?? null;
            } catch {
                return null;
            }
        },

        choose(scheme) {
            if (scheme === this.value || !set(scheme)) return;
            this.value = scheme;
            this.announce(`${announceLabel}: ${NAMES[scheme] ?? scheme}${scheme === 'system' ? ` (${NAMES[resolved()]})` : ''}`);

            const wire = persist === 'server' ? this.wire() : null;
            if (wire && typeof wire.setColorScheme === 'function') {
                // The server copy is a convenience; localStorage already holds the choice.
                Promise.resolve(wire.setColorScheme(scheme)).catch(() => {});
            }
        },

        move(step) {
            const items = [...this.$root.querySelectorAll('[role=radio]')];
            if (!items.length) return;
            const index = items.findIndex((item) => item.dataset.value === this.value);
            const next = items[(index + step + items.length) % items.length];
            this.choose(next.dataset.value);
            next.focus();
        },

        announce(text) {
            const live = this.$root.querySelector('[data-theme-toggle-live]');
            if (!live) return;
            live.textContent = '';
            requestAnimationFrame(() => {
                live.textContent = text;
            });
        },
    }));
});
