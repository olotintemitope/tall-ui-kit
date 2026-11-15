/*
 * Workflow components (docs/components/workflow.md): x-ui.tag-input, x-ui.banner,
 * x-ui.avatar-group, and the lazy loaders for x-ui.slider, x-ui.tree and x-ui.sortable.
 *
 * The slider, tree and sortable behaviour lives in its own chunk (slider-core.js,
 * tree-core.js, sortable-core.js), fetched with import() only when the page has one.
 * Until it arrives, the server-rendered markup shows the right state. The stubs below
 * are the Alpine components (x-modelable needs a reactive `value` from the start).
 */
import './tag-input';
import './banner';
import './avatar-group';
import './upload-progress';

let sortable = null;

function loadSortable() {
    sortable ??= import('./sortable-core').then((m) => m.start());

    return sortable;
}

function scanSortable() {
    if (document.querySelector('[data-sortable]')) loadSortable();
}

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    /*
     * The initial value is read from data-value, not passed in x-data: a re-render with a
     * new value then changes an attribute, not the x-data expression, so Livewire's morph
     * keeps the component instead of starting it again.
     */
    const initial = (el) => {
        try {
            return JSON.parse(el.dataset.value ?? 'null');
        } catch {
            return null;
        }
    };

    Alpine.data('uiSlider', (options = {}) => ({
        value: null,

        init() {
            if (this.value === null) this.value = initial(this.$root);
            import('./slider-core').then((m) => m.mount(this, { ...options, value: this.value }));
        },
    }));

    Alpine.data('uiTree', (options = {}) => ({
        value: null,

        init() {
            if (this.value === null) this.value = initial(this.$root);
            import('./tree-core').then((m) => m.mount(this, { ...options, value: this.value }));
        },
    }));
});

document.addEventListener('alpine:initialized', scanSortable);
document.addEventListener('livewire:navigated', scanSortable);
// A pointer or key on a handle before the chunk arrived: load it now.
['pointerover', 'focusin'].forEach((type) => document.addEventListener(type, (event) => {
    if (!sortable && event.target.closest?.('[data-sortable]')) loadSortable();
}, { passive: true }));

document.addEventListener('livewire:init', () => {
    // A morph puts the server's thumb positions back; the slider repaints from its value.
    window.Livewire.hook('morph.updated', ({ el }) => {
        if (el instanceof Element && el.matches('[data-slider], [data-slider-track], [data-slider-thumb], [data-slider-fill], [data-slider-output]')) {
            el.closest('[data-slider]')?._uiSlider?.paint();
        }
    });
});
