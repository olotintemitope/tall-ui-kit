/*
 * x-ui.slider behaviour (docs/components/workflow.md, "Slider"), loaded lazily by
 * widgets.js. `component` is the Alpine object (its `value` is x-modelable); the DOM
 * is written directly (thumb positions, fill, aria-value*, the output), so there is
 * no Alpine binding per thumb.
 */
import { formatNumber } from './forms';

const KEYS = ['ArrowRight', 'ArrowUp', 'ArrowLeft', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End'];

export function mount(component, o) {
    const root = component.$root;
    // Started again on the same element (a re-initialised component): keep one set of listeners.
    if (root._uiSlider) {
        root._uiSlider.adopt(component);

        return;
    }
    const track = root.querySelector('[data-slider-track]');
    const thumbs = [...root.querySelectorAll('[data-slider-thumb]')];
    const fill = root.querySelector('[data-slider-fill]');
    const output = root.querySelector('[data-slider-output]');
    const decimals = (String(o.step).split('.')[1] ?? '').length;
    const disabled = () => root.hasAttribute('data-disabled');

    const snap = (v) => {
        const n = Number(v);
        const raw = Number.isFinite(n) ? n : o.min;
        const stepped = o.min + Math.round((raw - o.min) / o.step) * o.step;

        return Number(Math.min(o.max, Math.max(o.min, stepped)).toFixed(decimals));
    };
    const read = (value) => {
        if (o.dual) {
            const pair = Array.isArray(value) ? value.map(snap) : [o.min, o.max];

            return pair[0] <= pair[1] ? pair : [pair[1], pair[0]];
        }

        return [snap(Array.isArray(value) ? value[0] : value ?? o.min)];
    };
    const text = (v) => {
        const base = formatNumber(v, { format: o.format, currency: o.currency, decimals: o.decimals });

        return o.unit ? `${base} ${o.unit}` : base;
    };
    const pct = (v) => ((v - o.min) / (o.max - o.min)) * 100;

    let values = read(o.value);
    let drag = null;

    function paint() {
        thumbs.forEach((thumb, i) => {
            const v = values[i];
            thumb.style.left = `${pct(v)}%`;
            thumb.setAttribute('aria-valuenow', String(v));
            thumb.setAttribute('aria-valuetext', text(v));
            thumb.setAttribute('aria-valuemin', String(o.dual && i === 1 ? values[0] : o.min));
            thumb.setAttribute('aria-valuemax', String(o.dual && i === 0 ? values[1] : o.max));
        });
        fill.style.left = o.dual ? `${pct(values[0])}%` : '0%';
        fill.style.right = `${100 - pct(o.dual ? values[1] : values[0])}%`;
        if (output) output.textContent = o.dual ? `${text(values[0])} to ${text(values[1])}` : text(values[0]);
        root.querySelectorAll('[data-slider-input]').forEach((input) => {
            input.value = String(values[Number(input.dataset.sliderInput)]);
        });
    }

    function set(i, v) {
        let next = snap(v);
        if (o.dual) next = i === 0 ? Math.min(next, values[1]) : Math.max(next, values[0]);
        if (next === values[i]) return false;
        values = values.map((old, j) => (j === i ? next : old));
        paint();

        return true;
    }

    function commit() {
        const next = o.dual ? [...values] : values[0];
        const same = o.dual ? Array.isArray(component.value) && component.value[0] === next[0] && component.value[1] === next[1] : component.value === next;
        if (same) return;
        component.value = next;
        root.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: next } }));
    }

    function fromPointer(event) {
        const box = track.getBoundingClientRect();
        const ratio = Math.min(1, Math.max(0, (event.clientX - box.left) / box.width));

        return o.min + ratio * (o.max - o.min);
    }

    function nearest(v) {
        if (!o.dual) return 0;
        const [lo, hi] = values;
        if (v <= lo) return 0;
        if (v >= hi) return 1;

        return v - lo <= hi - v ? 0 : 1;
    }

    track.addEventListener('pointerdown', (event) => {
        if (disabled() || event.button > 0) return;
        event.preventDefault();
        const v = fromPointer(event);
        const own = event.target.closest('[data-slider-thumb]');
        const i = own ? Number(own.dataset.sliderThumb) : nearest(v);
        drag = { i, id: event.pointerId };
        track.setPointerCapture?.(event.pointerId);
        thumbs[i].focus({ preventScroll: true });
        thumbs[i].setAttribute('data-dragging', '');
        if (!own) set(i, v);
    });

    track.addEventListener('pointermove', (event) => {
        if (!drag || event.pointerId !== drag.id) return;
        let v = fromPointer(event);
        // Two thumbs on one value: the drag's direction picks which one moves.
        if (o.dual && values[0] === values[1]) {
            drag.i = v < values[0] ? 0 : 1;
            thumbs[drag.i].focus({ preventScroll: true });
        }
        set(drag.i, v);
    });

    const end = (event) => {
        if (!drag || event.pointerId !== drag.id) return;
        thumbs[drag.i].removeAttribute('data-dragging');
        drag = null;
        commit();
    };
    track.addEventListener('pointerup', end);
    track.addEventListener('pointercancel', end);

    thumbs.forEach((thumb, i) => {
        thumb.addEventListener('keydown', (event) => {
            if (disabled() || !KEYS.includes(event.key)) return;
            event.preventDefault();
            const v = values[i];
            const lowest = o.dual && i === 1 ? values[0] : o.min;
            const highest = o.dual && i === 0 ? values[1] : o.max;
            const next = {
                ArrowRight: v + o.step,
                ArrowUp: v + o.step,
                ArrowLeft: v - o.step,
                ArrowDown: v - o.step,
                PageUp: v + o.pageStep,
                PageDown: v - o.pageStep,
                Home: lowest,
                End: highest,
            }[event.key];
            if (set(i, next)) commit();
        });
    });

    // A new value from wire:model / x-model (or the server) moves the thumbs.
    component.$watch('value', (value) => {
        if (drag) return;
        values = read(value);
        paint();
    });
    if (component.value !== undefined && component.value !== null) values = read(component.value);
    paint();
    // A Livewire morph puts the server's markup back; widgets.js repaints through this.
    root._uiSlider = {
        paint,
        adopt(next) {
            component = next;
            component.$watch('value', (value) => {
                if (drag) return;
                values = read(value);
                paint();
            });
            values = read(component.value);
            paint();
        },
    };
}
