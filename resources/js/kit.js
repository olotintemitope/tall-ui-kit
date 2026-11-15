import { registerCharts } from './charts';
import { storageKey } from './storage';

/*
 * Alpine is bundled with Livewire (loaded by @livewireScripts in layouts/app),
 * so it is never imported here. Stores register on `alpine:init`, which fires
 * after this deferred module has run.
 */

/* <html data-ui-privacy-key> overrides the key (an app keeping an older key). */
function privacyKey() {
    return document.documentElement.dataset.uiPrivacyKey ?? storageKey('privacy_mode');
}

function readPrivacyMode() {
    try {
        return window.localStorage.getItem(privacyKey()) === 'hidden';
    } catch {
        return false;
    }
}

function writePrivacyMode(hidden) {
    document.documentElement.dataset.privacy = hidden ? 'hidden' : 'visible';

    try {
        window.localStorage.setItem(privacyKey(), hidden ? 'hidden' : 'visible');
    } catch {
        // Storage unavailable: the choice lasts for this page only.
    }
}

function isTypingTarget(target) {
    if (!target) {
        return false;
    }

    // A select trigger (role=combobox) types ahead, so its letters are not shortcuts.
    return ['INPUT', 'TEXTAREA', 'SELECT'].includes(target.tagName) || target.isContentEditable || target.getAttribute?.('role') === 'combobox';
}

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    registerCharts(Alpine);

    // Toasts: the `toasts` store and the $toast magic live in ./toasts.js.

    /*
     * Privacy mode masks money figures (x-ui.money). It shares the React
     * PrivacyModeContext storage key and its "hidden"/"visible" values, so the
     * choice carries across React and Laravel pages during the migration.
     * Alpine's $persist would JSON-encode the value and break that, hence the
     * manual storage access. @transition: switch to $persist once React is gone.
     */
    Alpine.store('privacy', {
        hidden: readPrivacyMode(),

        toggle() {
            this.hidden = !this.hidden;
            writePrivacyMode(this.hidden);
        },

        reveal() {
            this.hidden = false;
            writePrivacyMode(false);
        },

        hide() {
            this.hidden = true;
            writePrivacyMode(true);
        },
    });

    window.addEventListener('keydown', (event) => {
        if (event.key !== 'h' && event.key !== 'H') {
            return;
        }

        if (event.ctrlKey || event.metaKey || event.altKey || isTypingTarget(event.target)) {
            return;
        }

        if (!document.querySelector('[data-privacy-shortcut]')) {
            return;
        }

        event.preventDefault();
        Alpine.store('privacy').toggle();
    });

    /*
     * A notice the user can dismiss until its state changes. `key` is the
     * storage key and `state` the value that marks it dismissed; a new state
     * shows it again. Storage access lives here, not in x-init/x-on, because
     * Alpine expressions cannot hold statements such as try/catch.
     */
    Alpine.data('dismissible', (key, state) => ({
        hidden: false,

        init() {
            try {
                this.hidden = window.localStorage.getItem(key) === state;
            } catch {
                // Storage unavailable: show the notice.
            }
        },

        dismiss() {
            this.hidden = true;

            try {
                window.localStorage.setItem(key, state);
            } catch {
                // Storage unavailable: dismissed for this page only.
            }
        },
    }));
});

