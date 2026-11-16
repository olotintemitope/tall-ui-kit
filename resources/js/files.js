/*
 * File components (docs/components/files.md): x-ui.file-manager, x-ui.file-preview and the
 * upgraded x-ui.file. Small stubs only: the file manager's behaviour lives in
 * file-manager-core.js and the upgraded file input's (validation, reorder, paste,
 * chunked uploads) in file-core.js, each fetched with import() the first time a page has
 * one. Until then the server-rendered markup shows as usual.
 */
import { storageKey } from './storage';

let managerCore = null;
const loadManager = () => (managerCore ??= import('./file-manager-core'));
let fileCore = null;
const loadFile = () => (fileCore ??= import('./file-core'));

const readView = (key) => {
    try {
        return key ? window.localStorage.getItem(storageKey(key)) : null;
    } catch {
        return null;
    }
};

const writeView = (key, value) => {
    try {
        if (key) window.localStorage.setItem(storageKey(key), value);
    } catch {
        // Storage unavailable: the view lasts for this page only.
    }
};

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.data('uiFileManager', (o = {}) => ({
        view: o.view === 'list' ? 'list' : 'grid',
        count: 0,
        loaded: 0,
        canDownload: false,
        status: '',
        moveTarget: null,
        newName: '',
        dropName: o.here ?? '',
        sort: o.sort ?? 'name:asc',

        init() {
            const stored = readView(o.persist);
            if (stored === 'grid' || stored === 'list') this.view = stored;
            this.$watch('view', (value) => {
                writeView(o.persist, value);
                this._fm?.viewChanged();
            });
            loadManager().then((m) => {
                if (this.$root.isConnected) this._fm = m.mount(this, o);
            });
        },

        destroy() {
            this._fm?.destroy();
        },

        fmSelectAll() { this._fm?.selectAll(); },
        fmClear(focus = false) { this._fm?.clear(focus); },
        fmOpenSelected() { this._fm?.openSelected(); },
        fmRenameSelected() { this._fm?.renameSelected(); },
        fmDownload() { this._fm?.download(); },
        fmAskMove() { this._fm?.askMove(); },
        fmAskDelete() { this._fm?.askDelete(); },
        fmDelete() { this._fm?.remove(); },
        fmMove(scope) { this._fm?.move(scope); },
        fmCreate(scope) { this._fm?.create(scope); },
        fmPrepareUpload(folder) { this._fm?.prepareUpload(folder); },
    }));

    /*
     * x-ui.file-preview: the image zoom (fit, then 100% to 300%) and Left / Right for the
     * previous and next file. One per preview; a page shows one preview at a time.
     */
    Alpine.data('uiFilePreview', (o = {}) => ({
        level: 0,
        steps: [0, 1, 1.5, 2, 3],

        zoomBy(delta) {
            this.level = Math.max(0, Math.min(this.steps.length - 1, this.level + delta));
        },

        fit() {
            this.level = 0;
        },

        go(key) {
            if (key === null || key === undefined) return;
            if (o.action && this.$wire) this.$wire.call(o.action, key);
            else this.$dispatch('ui-file-preview', { key });
        },

        key(event) {
            const t = event.target;
            if (event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey) return;
            if (t.closest?.('input, textarea, select, audio, video, object, [contenteditable], [role=slider], [data-audio]')) return;
            if (t.closest?.('[data-file-zoom]')) {
                if (event.key === '+' || event.key === '=') { event.preventDefault(); this.zoomBy(1); return; }
                if (event.key === '-') { event.preventDefault(); this.zoomBy(-1); return; }
                if (event.key === '0') { event.preventDefault(); this.fit(); return; }
                // Arrow keys scroll a zoomed image.
                if (this.level > 0) return;
            }
            const button = event.key === 'ArrowLeft' ? this.$root.querySelector('[data-file-preview-prev]')
                : event.key === 'ArrowRight' ? this.$root.querySelector('[data-file-preview-next]') : null;
            if (!button || button.getAttribute('aria-disabled') === 'true' || button.disabled) return;
            event.preventDefault();
            button.click();
        },
    }));

    /*
     * x-ui.file with any of the new props (limits, reorder, paste, chunked): the state the
     * markup binds, then file-core.js for the behaviour.
     */
    Alpine.data('uiFile', (o = {}) => ({
        dragging: false,
        progress: null,
        files: [],
        rejected: [],
        summary: '',
        status: '',

        init() {
            loadFile().then((m) => {
                if (this.$root.isConnected) this._file = m.mount(this, o);
            });
        },

        destroy() {
            this._file?.destroy();
        },

        remove(index) { this._file?.remove(index); },
        dismiss(index) { this._file?.dismiss(index); },
        size(bytes) { return this._file?.bytes(bytes) ?? ''; },
    }));
});

/*
 * x-ui.file-manager sends its toolbar, bulk bar and dialogs once: each Livewire request
 * names the chrome this page already holds, so the server sends an empty placeholder
 * for it (Livewire keeps the wire:ignore original).
 */
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('request', ({ options }) => {
        const keys = [...new Set([...document.querySelectorAll('[data-fm-chrome]')].filter((el) => el.childElementCount).map((el) => el.dataset.fmChrome))];
        if (!keys.length || !options) return;
        if (options.headers instanceof Headers) options.headers.set('X-Tall-Ui-Chrome', keys.join(' '));
        else options.headers = { ...(options.headers ?? {}), 'X-Tall-Ui-Chrome': keys.join(' ') };
    });
});

/* A drag from the desktop over a manager before its core arrived: load it now. */
['dragenter', 'pointerover', 'focusin'].forEach((type) => document.addEventListener(type, (event) => {
    if (!managerCore && event.target.closest?.('[data-file-manager]')) loadManager();
}, { passive: true, capture: true }));
