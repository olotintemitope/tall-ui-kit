/*
 * x-ui.select: the WAI-ARIA APG "select-only combobox", single or multiple
 * (resources/views/components/ui/select.blade.php, docs/ui-kit.md).
 *
 * Cheap on load: each instance is a small Alpine object. The options are server-rendered
 * plain elements inside x-ignore (no Alpine per option) and are read from the DOM the
 * first time the list is used; ids, aria-selected="false" and the listbox name are filled
 * in then. Positioning is the lazy x-anchor (anchor.js), and the outside-click listener
 * exists only while the list is open.
 *
 * Keyboard (focus never leaves the trigger; aria-activedescendant names the active option):
 *   closed  Enter, Space, Down, Up, Alt+Down open (on the chosen option); Home and End open
 *           on the first or last; a printable key opens on the first match (type-ahead)
 *   open    Down, Up, Home, End, PageDown, PageUp (10) move, skipping disabled options;
 *           Enter or Space choose and close (multiple: toggle and stay open); Alt+Up chooses
 *           and closes; Escape closes without a change and keeps focus; Tab chooses the
 *           active option (single) and moves on; printable keys type ahead (500ms buffer,
 *           a repeated letter cycles through the options starting with it)
 *
 * The value changes only when an option is chosen, so wire:model.live sends one request
 * per choice, never while arrowing. A chosen value fires `input` and `change` on the
 * trigger (they bubble: x-ui.form's dirty flag, x-on:change on the component).
 */

const PAGE = 10;
const live = new Set();

const isPrintable = (event) => event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiSelect', (initial = '', multiple = false) => ({
        value: initial,
        open: false,
        text: '',
        activeId: null,
        multiple,
        cache: null,
        active: -1,
        buffer: '',
        bufferTimer: null,
        outside: null,
        spacer: null,

        init() {
            this.text = this.$root.querySelector('[data-select-label]')?.textContent ?? '';
            // Only a real change re-reads the options (wire:model hands over 25 for "25", null for "").
            this.$watch('value', (now, before) => this.signature(now) !== this.signature(before) && this.sync());
            live.add(this);
        },

        destroy() {
            live.delete(this);
            this.unlisten();
            this.spacer?.remove();
        },

        signature(value) {
            return this.multiple ? JSON.stringify((Array.isArray(value) ? value : []).map(String)) : String(value ?? '');
        },

        list() {
            return this.$refs.popup.firstElementChild;
        },

        /* [{ el, value, label, disabled }] in document order, read once (again after a morph). */
        options() {
            if (this.cache) return this.cache;
            const base = this.$refs.trigger.id;
            this.cache = [...this.list().querySelectorAll('[role=option]')].map((el, index) => {
                el.id ||= `${base}-o${index}`;

                return { el, value: el.dataset.value ?? el.textContent, label: el.textContent, disabled: el.getAttribute('aria-disabled') === 'true' };
            });

            return this.cache;
        },

        chosen() {
            const values = this.multiple ? (Array.isArray(this.value) ? this.value : []) : [this.value ?? ''];

            return new Set(values.map((v) => String(v ?? '')));
        },

        /* The option index shown as chosen: the value's, else (no match) the placeholder or first option, as a native select. */
        chosenIndex() {
            const options = this.options();
            const chosen = this.chosen();
            const index = options.findIndex((o) => chosen.has(o.value));

            return index >= 0 || this.multiple ? index : options.findIndex((o) => !o.disabled);
        },

        /* Label, aria-selected and the hidden form inputs follow the value. */
        sync() {
            const options = this.options();
            const chosen = this.chosen();
            const single = this.multiple ? -1 : this.chosenIndex();
            const labels = [];
            options.forEach((o, index) => {
                const on = this.multiple ? chosen.has(o.value) : index === single;
                if (o.el.getAttribute('aria-selected') !== String(on)) o.el.setAttribute('aria-selected', String(on));
                if (on) labels.push(o.label);
            });
            const label = this.$root.querySelector('[data-select-label]');
            this.text = labels.length ? labels.join(', ') : (this.multiple ? (label?.dataset.none ?? 'None selected') : '');
            this.syncInputs(this.multiple ? options.filter((o) => chosen.has(o.value)).map((o) => o.value) : [options[single]?.value ?? '']);
        },

        syncInputs(values) {
            const carrier = this.$root.querySelector(':scope > input[data-select-input]');
            if (carrier) {
                carrier.value = values[0] ?? '';

                return;
            }
            const box = this.$root.querySelector(':scope > [data-select-inputs]');
            if (!box) return;
            const name = box.dataset.name;
            const disabled = this.$refs.trigger.disabled;
            box.replaceChildren(...values.map((v) => Object.assign(document.createElement('input'), { type: 'hidden', name, value: v, disabled })));
        },

        /*
         * After a Livewire morph the options are the server's again. A wire:model value is
         * rendered by the server already; an x-model or unbound value is put back.
         */
        refresh() {
            const activeValue = this.cache?.[this.active]?.value;
            this.cache = null;
            const label = this.$root.querySelector('[data-select-label]');
            if (this.open || !this.$root.getAttributeNames().some((n) => n.startsWith('wire:model'))) {
                this.sync();
                // The morph may have put the server's label back while `text` did not change.
                if (label && label.textContent !== this.text) label.textContent = this.text;
            } else if (label) {
                this.text = label.textContent;
            }
            if (this.open) {
                this.prepare();
                const index = this.options().findIndex((o) => o.value === activeValue);
                this.setActive(index >= 0 ? index : this.chosenIndex());
            }
        },

        /* First open: unselected options say so, and the listbox gets a name if the markup could not give one. */
        prepare() {
            this.options().forEach((o) => o.el.hasAttribute('aria-selected') || o.el.setAttribute('aria-selected', 'false'));
            const list = this.list();
            const labelledBy = list.getAttribute('aria-labelledby');
            if (list.hasAttribute('aria-label') || (labelledBy && document.getElementById(labelledBy))) return;
            const trigger = this.$refs.trigger;
            const name = trigger.getAttribute('aria-label') ?? trigger.labels?.[0]?.textContent;
            list.removeAttribute('aria-labelledby');
            if (name) list.setAttribute('aria-label', name.replace(/\s*\*\s*$/, '').trim());
        },

        enabled(index) {
            const o = this.options()[index];

            return o && !o.disabled;
        },

        /* The nearest enabled option from `index` going `step` (+1 or -1), or -1. */
        seek(index, step) {
            const n = this.options().length;
            for (let i = index; i >= 0 && i < n; i += step) {
                if (this.enabled(i)) return i;
            }

            return -1;
        },

        first() {
            return this.seek(0, 1);
        },

        last() {
            return this.seek(this.options().length - 1, -1);
        },

        setActive(index, scroll = true) {
            const options = this.options();
            options[this.active]?.el.removeAttribute('data-active');
            this.active = index;
            const option = options[index];
            if (!option) {
                this.activeId = null;

                return;
            }
            option.el.setAttribute('data-active', '');
            this.activeId = option.el.id;
            if (scroll) this.reveal(option.el);
        },

        /* Keeps the active option inside the list's own scroll box (never scrolls the page). */
        reveal(el) {
            const list = this.list();
            const top = el.offsetTop - list.clientTop;
            const bottom = top + el.offsetHeight;
            if (top < list.scrollTop + 4) list.scrollTop = Math.max(0, top - 4);
            else if (bottom > list.scrollTop + list.clientHeight - 4) list.scrollTop = bottom - list.clientHeight + 4;
        },

        show(at = null) {
            const trigger = this.$refs.trigger;
            if (trigger.disabled || this.open) {
                if (this.open && at !== null) this.setActive(at);

                return;
            }
            this.prepare();
            const popup = this.$refs.popup;
            popup.style.minWidth = `${trigger.offsetWidth}px`;
            this.list().style.maxHeight = '';
            this.open = true;
            const chosen = this.chosenIndex();
            const index = typeof at === 'number' && at >= 0 ? at : (this.enabled(chosen) ? chosen : this.first());
            this.listen();
            this.$nextTick(() => {
                this.setActive(index);
                this.makeRoom();
                this.$revealBelow(popup, trigger).then(() => this.fit());
            });
        },

        /*
         * A fixed list does not lengthen the page, so near the end of a short page there is
         * nothing to scroll and it would hang off the screen. As an absolutely placed menu
         * extends the page, a spacer at the end of the body makes that room while the list
         * is open (never inside a dialog or sheet: they scroll their own body).
         */
        makeRoom() {
            const trigger = this.$refs.trigger;
            if (trigger.closest('dialog, [role=dialog]')) return;
            const page = document.scrollingElement || document.documentElement;
            const need = trigger.getBoundingClientRect().bottom + 6 + this.$refs.popup.offsetHeight + 8 - window.innerHeight;
            const spare = page.scrollHeight - page.clientHeight - page.scrollTop;
            if (need <= spare) return;
            this.spacer = Object.assign(document.createElement('div'), { ariaHidden: 'true' });
            this.spacer.setAttribute('data-select-spacer', '');
            this.spacer.style.height = `${Math.ceil(need - spare)}px`;
            document.body.append(this.spacer);
        },

        /* No room left below even after scrolling: shorten the list (never flip it above). */
        fit() {
            if (!this.open) return;
            const list = this.list();
            const room = window.innerHeight - this.$refs.trigger.getBoundingClientRect().bottom - 6 - 12;
            if (room < list.offsetHeight) list.style.maxHeight = `${Math.max(120, Math.floor(room))}px`;
            this.reveal(this.options()[this.active]?.el ?? list);
        },

        close(focus = true) {
            if (!this.open) return;
            this.open = false;
            this.setActive(-1, false);
            this.unlisten();
            this.spacer?.remove();
            this.spacer = null;
            if (focus) this.$refs.trigger.focus({ preventScroll: true });
        },

        toggle() {
            this.open ? this.close() : this.show();
        },

        listen() {
            this.outside = (event) => {
                if (!this.$root.contains(event.target)) this.close(false);
            };
            document.addEventListener('pointerdown', this.outside, true);
        },

        unlisten() {
            if (this.outside) document.removeEventListener('pointerdown', this.outside, true);
            this.outside = null;
        },

        emit() {
            const trigger = this.$refs.trigger;
            trigger.dispatchEvent(new Event('input', { bubbles: true }));
            trigger.dispatchEvent(new Event('change', { bubbles: true }));
        },

        choose(index, close = true) {
            const option = this.options()[index];
            if (!option || option.disabled) {
                if (close) this.close();

                return;
            }
            if (this.multiple) {
                const chosen = this.chosen();
                chosen.has(option.value) ? chosen.delete(option.value) : chosen.add(option.value);
                this.value = this.options().filter((o) => chosen.has(o.value)).map((o) => o.value);
                this.emit();

                return;
            }
            const changed = String(this.value ?? '') !== option.value;
            this.value = option.value;
            if (close) this.close();
            if (changed) this.emit();
        },

        /* Type-ahead over the option labels, from the option after the active one. */
        match(char) {
            clearTimeout(this.bufferTimer);
            this.bufferTimer = setTimeout(() => { this.buffer = ''; }, 500);
            this.buffer += char.toLowerCase();
            const repeated = [...this.buffer].every((c) => c === this.buffer[0]);
            const query = repeated ? this.buffer[0] : this.buffer;
            const options = this.options();
            const n = options.length;
            const from = this.open ? this.active : this.chosenIndex();
            const start = Math.max(0, from) + (repeated || this.buffer.length === 1 ? 1 : 0);
            for (let k = 0; k < n; k++) {
                const i = (start + k) % n;
                if (!options[i].disabled && options[i].label.trim().toLowerCase().startsWith(query)) return i;
            }

            return -1;
        },

        key(event) {
            const k = event.key;
            if (event.ctrlKey || event.metaKey) return;

            if (!this.open) {
                if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(k)) {
                    event.preventDefault();
                    this.show();
                } else if (k === 'Home' || k === 'End') {
                    event.preventDefault();
                    this.show(k === 'Home' ? this.first() : this.last());
                } else if (isPrintable(event)) {
                    event.preventDefault();
                    const index = this.match(k);
                    this.show(index >= 0 ? index : null);
                }

                return;
            }

            const options = this.options();
            const step = (from, by) => {
                const target = Math.min(options.length - 1, Math.max(0, from + by));
                const found = this.seek(target, by > 0 ? 1 : -1);

                return found >= 0 ? found : this.seek(target, by > 0 ? -1 : 1);
            };

            switch (k) {
                case 'ArrowDown':
                    event.preventDefault();
                    this.setActive(this.active < 0 ? this.first() : step(this.active, 1));
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    if (event.altKey) this.choose(this.active);
                    else this.setActive(this.active < 0 ? this.last() : step(this.active, -1));
                    break;
                case 'Home':
                    event.preventDefault();
                    this.setActive(this.first());
                    break;
                case 'End':
                    event.preventDefault();
                    this.setActive(this.last());
                    break;
                case 'PageDown':
                    event.preventDefault();
                    this.setActive(step(Math.max(0, this.active), PAGE));
                    break;
                case 'PageUp':
                    event.preventDefault();
                    this.setActive(step(Math.max(0, this.active), -PAGE));
                    break;
                case 'Enter':
                    event.preventDefault();
                    this.choose(this.active, !this.multiple);
                    break;
                case ' ':
                    event.preventDefault();
                    if (this.buffer !== '') {
                        const index = this.match(' ');
                        if (index >= 0) this.setActive(index);
                    } else {
                        this.choose(this.active, !this.multiple);
                    }
                    break;
                case 'Escape':
                    // The list is the innermost layer: a dialog or sheet around it stays open.
                    event.preventDefault();
                    event.stopPropagation();
                    this.close();
                    break;
                case 'Tab':
                    if (!this.multiple) this.choose(this.active, false);
                    this.close(false);
                    break;
                default:
                    if (isPrintable(event)) {
                        event.preventDefault();
                        const index = this.match(k);
                        if (index >= 0) this.setActive(index);
                    }
            }
        },

        blurred(event) {
            if (this.open && !this.$root.contains(event.relatedTarget)) this.close(false);
        },

        hover(event) {
            const el = event.target.closest?.('[role=option]');
            if (!el || el.hasAttribute('data-active') || el.getAttribute('aria-disabled') === 'true') return;
            this.setActive(this.options().findIndex((o) => o.el === el), false);
        },

        pick(event) {
            const el = event.target.closest?.('[role=option]');
            if (!el) return;
            const index = this.options().findIndex((o) => o.el === el);
            if (!this.enabled(index)) return;
            this.setActive(index, false);
            this.choose(index, !this.multiple);
            this.$refs.trigger.focus({ preventScroll: true });
        },

        /* The browser's required check failed on a plain form post: show the field as invalid and focus it. */
        invalidated() {
            const trigger = this.$refs.trigger;
            trigger.setAttribute('aria-invalid', 'true');
            trigger.focus();
        },
    }));
});

/* A click on the field's <label> focuses the trigger, as it focuses a native select, without opening it. */
document.addEventListener('click', (event) => {
    const label = event.target.closest?.('label');
    const control = label?.control;
    if (control?.matches?.('[data-ui="select"]') && !control.contains(event.target)) {
        event.preventDefault();
        if (!control.disabled) control.focus();
    }
}, true);

/* A Livewire morph re-rendered the options of every select inside the component. */
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('morphed', ({ el }) => {
        live.forEach((select) => {
            if (el.contains(select.$root)) select.refresh();
        });
    });
});
