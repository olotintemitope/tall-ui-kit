/*
 * Toasts (x-ui.toaster, x-ui.toast; docs/ui-kit.md "Toasts"). One store for both
 * entry points:
 *
 *   Livewire  $this->dispatch('toast', variant: 'success', title: 'Saved.',
 *                 description: '...', action: ['label' => 'Undo', 'method' => 'undo']);
 *   Alpine    $toast('Saved.')  or  $toast({ variant: 'error', title: '...' })
 *             $toast.promise(promise, { loading: 'Exporting', success: 'Export ready', error: 'Export failed' })
 *
 * Older calls keep working: `type` is read as `variant` and `message` as `title`
 * ($store.toasts.push({ type, message }) and $dispatch('toast', {...})).
 *
 * Options: variant (success | error | warning | info | neutral | loading; `tone` is an
 * alias and `danger` is read as error), title,
 * description, action ({ label, method, params, url, component }), dismissible
 * (default true), duration (ms; default 4000; 0 is sticky), id (pushing an id that
 * is already shown updates that toast, which is how a loading toast resolves).
 * Errors and loading toasts are always sticky. Timers pause while a toast is
 * hovered or focused. Toasts never take focus (Alt+T reaches them, resources/js/ui.js).
 * While a modal dialog is open the toaster moves into it (resources/js/toaster-layer.js).
 */

import './toaster-layer';

const DEFAULT_DURATION = 4000;
const VARIANTS = ['success', 'error', 'warning', 'info', 'neutral', 'loading'];

function normalise(input = {}, extra = {}, defaultDuration = DEFAULT_DURATION) {
    const options = typeof input === 'string' ? { title: input, ...extra } : { ...input, ...extra };
    // `tone` and `danger` are accepted so toasts share the badge and alert vocabulary.
    let variant = options.variant ?? options.tone ?? options.type ?? 'neutral';
    if (variant === 'danger') {
        variant = 'error';
    }
    if (!VARIANTS.includes(variant)) {
        variant = 'neutral';
    }

    const sticky = variant === 'error' || variant === 'loading';
    const duration = sticky ? 0 : Number(options.duration ?? defaultDuration);
    const action = options.action && options.action.label ? { ...options.action } : null;

    return {
        id: options.id != null ? String(options.id) : null,
        variant,
        title: String(options.title ?? options.message ?? ''),
        description: options.description ? String(options.description) : '',
        action,
        dismissible: options.dismissible !== false,
        duration: Number.isFinite(duration) && duration > 0 ? duration : 0,
        component: options.component ?? null,
    };
}

function createStore() {
    let nextId = 1;
    const timers = new Map();

    return {
        items: [],
        expanded: false,
        max: 3,
        duration: DEFAULT_DURATION,

        configure({ max, duration } = {}) {
            if (Number(max) > 0) this.max = Number(max);
            if (Number(duration) >= 0) this.duration = Number(duration);
        },

        get visible() {
            return this.expanded ? this.items : this.items.slice(-this.max);
        },

        get hiddenCount() {
            return this.expanded ? 0 : Math.max(0, this.items.length - this.max);
        },

        push(input = {}, extra = {}) {
            const toast = normalise(input, extra, this.duration);
            if (!toast.title && !toast.description) {
                return null;
            }

            const existing = toast.id ? this.items.find((item) => item.id === toast.id) : null;
            if (existing) {
                Object.assign(existing, toast, { paused: existing.paused, remaining: undefined });
                this.schedule(existing);

                return existing.id;
            }

            toast.id ??= `toast-${nextId++}`;
            toast.paused = false;
            this.items.push(toast);
            this.schedule(this.items[this.items.length - 1]);

            return toast.id;
        },

        update(id, input = {}) {
            return this.push({ ...input, id });
        },

        schedule(toast) {
            this.clear(toast.id);
            if (toast.duration > 0 && !toast.paused) {
                timers.set(toast.id, { started: Date.now(), remaining: toast.remaining ?? toast.duration, handle: setTimeout(() => this.dismiss(toast.id), toast.remaining ?? toast.duration) });
            }
        },

        clear(id) {
            const timer = timers.get(id);
            if (timer) {
                clearTimeout(timer.handle);
                timers.delete(id);
            }
        },

        pause(id) {
            const toast = this.items.find((item) => item.id === id);
            const timer = timers.get(id);
            if (!toast || toast.paused) {
                return;
            }
            toast.paused = true;
            if (timer) {
                toast.remaining = Math.max(500, timer.remaining - (Date.now() - timer.started));
                this.clear(id);
            }
        },

        resume(id) {
            const toast = this.items.find((item) => item.id === id);
            if (!toast || !toast.paused) {
                return;
            }
            toast.paused = false;
            this.schedule(toast);
        },

        dismiss(id) {
            this.clear(id);
            this.items = this.items.filter((toast) => toast.id !== id);
            if (this.items.length <= this.max) {
                this.expanded = false;
            }
        },

        dismissAll() {
            [...this.items].forEach((toast) => this.dismiss(toast.id));
        },

        /* Runs a toast's action: a Livewire method on its component, or a URL. */
        run(toast) {
            const action = toast.action;
            if (!action) {
                return;
            }
            if (action.method) {
                const componentId = action.component ?? toast.component;
                const component = componentId ? window.Livewire?.find(componentId) : null;
                component?.call(action.method, ...(action.params ?? []));
            } else if (action.url) {
                window.location.assign(action.url);
            }
            this.dismiss(toast.id);
        },

        /* A loading toast that resolves to success or error with the promise. */
        promise(promise, { loading = 'Working', success = 'Done.', error = 'Something went wrong.' } = {}) {
            const id = this.push({ variant: 'loading', title: loading });
            Promise.resolve(promise).then(
                (value) => this.push({ id, variant: 'success', ...(typeof success === 'function' ? success(value) : { title: success }) }),
                (reason) => this.push({ id, variant: 'error', ...(typeof error === 'function' ? error(reason) : { title: error }) }),
            );

            return promise;
        },
    };
}

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;
    Alpine.store('toasts', createStore());

    const store = () => Alpine.store('toasts');
    Alpine.magic('toast', () => {
        const toast = (input, extra) => store().push(input, extra);
        toast.promise = (promise, messages) => store().promise(promise, messages);
        toast.dismiss = (id) => store().dismiss(id);

        return toast;
    });

    /*
     * One listener for both sources. Livewire's dispatch() fires a bubbling `toast`
     * event from the component's element (event.__livewire set), so the component
     * that sent it is known and a `method` action can call back into it.
     */
    window.addEventListener('toast', (event) => {
        const detail = Array.isArray(event.detail) ? event.detail[0] : event.detail;
        const host = event.__livewire && event.target instanceof Element ? event.target.closest('[wire\\:id]') : null;
        store().push({ ...(detail ?? {}), component: detail?.component ?? host?.getAttribute('wire:id') ?? null });
    });
});

export { normalise as normaliseToast };
