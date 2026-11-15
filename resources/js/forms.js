import { storageKey } from './storage';
/*
 * Behaviour for the HTML5 form, interactive and media components and the form layouts
 * (docs/ui-kit-html.md, docs/ui-kit-forms.md). Registered on `alpine:init` so it uses
 * Livewire's bundled Alpine. Imported by resources/js/ui.js.
 */

const isMac = () => /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);

const clamp = (value, min, max) => Math.min(max ?? Infinity, Math.max(min ?? -Infinity, value));

/* Rounds to the step's precision so 0.1 + 0.2 never shows 0.30000000000000004. */
function roundToStep(value, step) {
    const decimals = (String(step).split('.')[1] ?? '').length;

    return Number(value.toFixed(decimals));
}

export function formatNumber(value, { format = 'number', currency = 'NGN', decimals = null } = {}) {
    if (value === null || value === undefined || value === '' || Number.isNaN(Number(value))) {
        return '';
    }
    const number = Number(value);
    if (format === 'money') {
        const digits = decimals ?? (currency === 'USD' ? 2 : 0);
        const sign = currency === 'USD' ? '$' : '₦';

        return (number < 0 ? '−' : '') + sign + Math.abs(number).toLocaleString('en-US', { minimumFractionDigits: digits, maximumFractionDigits: digits });
    }
    if (format === 'percent') {
        return `${number.toLocaleString('en-US', { maximumFractionDigits: decimals ?? 0 })}%`;
    }

    return number.toLocaleString('en-US', { maximumFractionDigits: decimals ?? 20 });
}

export function formatDuration(seconds) {
    if (!Number.isFinite(seconds) || seconds < 0) {
        return '0:00';
    }
    const whole = Math.floor(seconds);
    const m = Math.floor(whole / 60);
    const s = String(whole % 60).padStart(2, '0');

    return `${m}:${s}`;
}

/* Text-only password strength: length plus character classes. Never blocks submit. */
export function passwordStrength(value) {
    const text = String(value ?? '');
    if (text === '') {
        return { score: 0, label: '' };
    }
    const classes = [/[a-z]/, /[A-Z]/, /\d/, /[^A-Za-z0-9]/].filter((re) => re.test(text)).length;
    let score = text.length >= 8 ? 1 : 0;
    if (text.length >= 12) score++;
    if (classes >= 3) score++;
    if (text.length >= 16 && classes >= 2) score++;
    score = Math.min(3, score);

    return {
        score,
        label: ['Too short: use at least 8 characters.', 'Weak: add length or mix letters, numbers and symbols.', 'Fair: a few more characters make it strong.', 'Strong password.'][score],
    };
}

/* The last step each wizard showed, by Livewire component id: survives the wizard's re-init on morph. */
const wizardSteps = new Map();

/* One audio player plays at a time across the page. */
const AUDIO_EVENT = 'ui-audio-play';

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    /* x-ui.password: show/hide and the optional text-only strength hint. */
    Alpine.data('uiPassword', ({ strength = false } = {}) => ({
        shown: false,
        hint: '',
        score: 0,
        strength,
        rate(value) {
            if (!this.strength) return;
            const result = passwordStrength(value);
            this.score = result.score;
            this.hint = result.label;
        },
    }));

    /*
     * x-ui.number-input: a text input with role="spinbutton", so money can show
     * grouped ("₦150,000") once the field loses focus and plain digits while typing.
     * `value` (x-modelable) is a number or null. Up/Down step, Page Up/Down step x10,
     * Home/End go to min/max. The two buttons inside the border step and keep focus.
     */
    Alpine.data('uiStepper', (options = {}) => ({
        value: options.value ?? null,
        text: '',
        min: options.min ?? null,
        max: options.max ?? null,
        step: options.step ?? 1,
        format: options.format ?? 'number',
        currency: options.currency ?? 'NGN',
        decimals: options.decimals ?? null,
        focused: false,

        init() {
            this.paint();
            this.$watch('value', () => {
                if (!this.focused) this.paint();
            });
        },

        // A disabled or read-only field counts as at both ends, so the +/- buttons do nothing.
        get locked() {
            return Boolean(this.$refs.input?.disabled || this.$refs.input?.readOnly);
        },

        get atMin() {
            return this.locked || (this.min !== null && this.value !== null && Number(this.value) <= this.min);
        },

        get atMax() {
            return this.locked || (this.max !== null && this.value !== null && Number(this.value) >= this.max);
        },

        get valueText() {
            return this.value === null ? '' : formatNumber(this.value, this);
        },

        paint() {
            this.text = this.focused ? (this.value === null ? '' : String(this.value)) : formatNumber(this.value, this);
        },

        parse(raw) {
            const cleaned = String(raw).replace(/[^\d.\-−]/g, '').replace('−', '-');
            if (cleaned === '' || cleaned === '-' || cleaned === '.') return null;
            const number = Number(cleaned);

            return Number.isNaN(number) ? null : number;
        },

        typed(raw) {
            this.value = this.parse(raw);
        },

        focus() {
            this.focused = true;
            this.paint();
        },

        blur() {
            this.focused = false;
            if (this.value !== null) {
                this.value = roundToStep(clamp(this.value, this.min, this.max), this.step);
            }
            this.paint();
        },

        stepBy(direction, multiplier = 1) {
            const base = this.value ?? (direction > 0 ? (this.min ?? 0) - this.step * multiplier : (this.max ?? 0) + this.step * multiplier);
            this.value = roundToStep(clamp(base + direction * this.step * multiplier, this.min, this.max), this.step);
            this.paint();
            this.$refs.input?.dispatchEvent(new Event('change', { bubbles: true }));
        },

        key(event) {
            const map = { ArrowUp: [1, 1], ArrowDown: [-1, 1], PageUp: [1, 10], PageDown: [-1, 10] };
            if (map[event.key]) {
                event.preventDefault();
                this.stepBy(...map[event.key]);
            } else if (event.key === 'Home' && this.min !== null) {
                event.preventDefault();
                this.value = this.min;
                this.paint();
                this.$refs.input?.dispatchEvent(new Event('change', { bubbles: true }));
            } else if (event.key === 'End' && this.max !== null) {
                event.preventDefault();
                this.value = this.max;
                this.paint();
                this.$refs.input?.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },
    }));

    /*
     * x-ui.range: single or dual. `value` (x-modelable) is a number, or [low, high]
     * for dual. With `steps` the inputs move over indexes into that list (uneven
     * scales snap to real values). With `minModel`/`maxModel` (Livewire) the values
     * are sent once on release, null at an open end, as x-marketplace.range did.
     */
    Alpine.data('uiRange', (options = {}) => ({
        steps: options.steps ?? null,
        min: options.min ?? 0,
        max: options.max ?? 100,
        step: options.step ?? 1,
        dual: options.dual ?? false,
        format: options.format ?? 'number',
        currency: options.currency ?? 'NGN',
        unit: options.unit ?? null,
        openEnded: options.openEnded ?? false,
        minModel: options.minModel ?? null,
        maxModel: options.maxModel ?? null,
        lo: 0,
        hi: 0,
        value: options.value ?? null,

        init() {
            this.hi = this.lastIndex;
            this.lo = this.dual ? this.firstIndex : this.lastIndex;
            this.pull();
            this.$watch('value', () => this.pull());
            if (this.minModel && this.$wire) {
                this.$watch(`$wire.${this.minModel}`, () => this.pull());
                this.$watch(`$wire.${this.maxModel}`, () => this.pull());
            }
        },

        get firstIndex() {
            return this.steps ? 0 : this.min;
        },

        get lastIndex() {
            return this.steps ? this.steps.length - 1 : this.max;
        },

        get inputStep() {
            return this.steps ? 1 : this.step;
        },

        at(position) {
            return this.steps ? this.steps[position] : position;
        },

        indexOf(value) {
            if (value === null || value === undefined) return null;
            if (!this.steps) return Number(value);
            const found = this.steps.indexOf(Number(value));

            return found < 0 ? null : found;
        },

        pull() {
            if (this.minModel && this.$wire) {
                this.lo = this.indexOf(this.$wire[this.minModel]) ?? this.firstIndex;
                this.hi = this.indexOf(this.$wire[this.maxModel]) ?? this.lastIndex;

                return;
            }
            if (this.dual && Array.isArray(this.value)) {
                this.lo = this.indexOf(this.value[0]) ?? this.firstIndex;
                this.hi = this.indexOf(this.value[1]) ?? this.lastIndex;
            } else if (!this.dual && this.value !== null) {
                this.hi = this.indexOf(this.value) ?? this.lastIndex;
            }
        },

        push() {
            if (this.minModel && this.$wire) {
                this.$wire.$set(this.minModel, this.lo === this.firstIndex ? null : this.at(this.lo), false);
                this.$wire.$set(this.maxModel, this.hi === this.lastIndex ? null : this.at(this.hi));

                return;
            }
            this.value = this.dual ? [this.at(this.lo), this.at(this.hi)] : this.at(this.hi);
        },

        moveLo() {
            if (this.lo > this.hi) this.lo = this.hi;
            if (!this.minModel) this.push();
        },

        moveHi() {
            if (this.dual && this.hi < this.lo) this.hi = this.lo;
            if (!this.minModel) this.push();
        },

        text(position) {
            const value = this.at(position);
            if (value === undefined || value === null) return '';
            if (this.format === 'money' && Number(value) === 0) return 'Free';
            let out = formatNumber(value, this);
            if (this.openEnded && position === this.lastIndex && this.format === 'number') out += '+';

            return this.unit ? `${out} ${this.unit}` : out;
        },

        percent(position) {
            const span = this.lastIndex - this.firstIndex || 1;

            return ((position - this.firstIndex) / span) * 100;
        },

        get fill() {
            const start = this.dual ? this.percent(this.lo) : 0;

            return `left: ${start}%; right: ${100 - this.percent(this.hi)}%`;
        },

        get output() {
            return this.dual ? `${this.text(this.lo)} to ${this.text(this.hi)}` : this.text(this.hi);
        },
    }));

    /* x-ui.color: the native swatch and a hex field kept in step. */
    Alpine.data('uiColor', (initial = '#212322') => ({
        value: initial,
        hex: initial,
        invalid: false,
        init() {
            this.$watch('value', (value) => {
                this.hex = String(value).toUpperCase();
                this.invalid = false;
            });
            this.hex = String(this.value).toUpperCase();
        },
        typed(raw) {
            let text = String(raw).trim();
            if (!text.startsWith('#')) text = `#${text}`;
            if (/^#[0-9a-f]{3}$/i.test(text)) text = `#${text[1]}${text[1]}${text[2]}${text[2]}${text[3]}${text[3]}`;
            this.invalid = !/^#[0-9a-f]{6}$/i.test(text);
            if (!this.invalid) this.value = text.toUpperCase();
        },
    }));

    /*
     * x-ui.combobox (ARIA 1.2 combobox with a listbox popup). Typing filters by
     * substring; Down/Up move the active option (aria-activedescendant, focus stays in
     * the input), Enter picks, Escape closes (a second Escape clears), Home/End move
     * the caret, Alt+Down opens without moving. Printable keys type ahead.
     */
    Alpine.data('uiCombobox', ({ options = [], value = null, allowCustom = false, id = 'combobox' } = {}) => ({
        options,
        value,
        query: '',
        open: false,
        active: -1,
        allowCustom,
        id,
        status: '',

        init() {
            this.query = this.labelFor(this.value);
            this.$watch('value', (v) => {
                if (!this.open) this.query = this.labelFor(v);
            });
        },

        labelFor(v) {
            return this.options.find((o) => o.value === v)?.label ?? (this.allowCustom && v ? String(v) : '');
        },

        get results() {
            const q = this.query.trim().toLowerCase();
            const selectedLabel = this.labelFor(this.value).toLowerCase();
            if (q === '' || q === selectedLabel) return this.options;

            return this.options.filter((o) => o.label.toLowerCase().includes(q) || (o.hint ?? '').toLowerCase().includes(q));
        },

        optionId(index) {
            return `${this.id}-option-${index}`;
        },

        get activeId() {
            return this.open && this.active >= 0 && this.results[this.active] ? this.optionId(this.active) : null;
        },

        announce() {
            const n = this.results.length;
            this.status = '';
            setTimeout(() => {
                this.status = n === 0 ? 'No matches' : `${n} ${n === 1 ? 'option' : 'options'} available`;
            }, 120);
        },

        show(activeIndex = null) {
            if (!this.open) {
                this.open = true;
                this.announce();
            }
            const selected = this.results.findIndex((o) => o.value === this.value);
            this.active = activeIndex ?? (selected >= 0 ? selected : 0);
            this.$nextTick(() => this.scrollActive());
        },

        close(restore = true) {
            this.open = false;
            this.active = -1;
            if (restore) this.query = this.labelFor(this.value);
        },

        input() {
            this.open = true;
            this.active = this.results.length ? 0 : -1;
            if (this.query === '') this.value = null;
            this.announce();
        },

        move(step) {
            if (!this.open) {
                this.show(step > 0 ? null : this.results.length - 1);

                return;
            }
            const n = this.results.length;
            if (n === 0 || this.results.every((option) => option.disabled)) return;
            // Skip disabled options, as x-ui.select does.
            do {
                this.active = (this.active + step + n) % n;
            } while (this.results[this.active]?.disabled);
            this.$nextTick(() => this.scrollActive());
        },

        scrollActive() {
            const el = this.activeId && document.getElementById(this.activeId);
            el?.scrollIntoView({ block: 'nearest' });
        },

        pick(index) {
            const option = this.results[index];
            if (!option || option.disabled) return;
            this.value = option.value;
            this.query = option.label;
            this.close(false);
            this.$refs.input?.focus();
            this.$refs.input?.dispatchEvent(new Event('change', { bubbles: true }));
        },

        enter(event) {
            if (this.open && this.active >= 0) {
                event.preventDefault();
                this.pick(this.active);
            } else if (this.open && this.allowCustom && this.query.trim() !== '') {
                event.preventDefault();
                this.value = this.query.trim();
                this.close(false);
            }
        },

        // preventDefault as well: a native <dialog> closes on an uncancelled Escape even
        // when propagation stops, so the list (or the text) goes first, then the dialog.
        escape(event) {
            if (this.open) {
                event.preventDefault();
                event.stopPropagation();
                this.close();
            } else if (this.query !== '') {
                event.preventDefault();
                event.stopPropagation();
                this.query = '';
                this.value = null;
            }
        },

        blur(event) {
            if (this.$root.contains(event.relatedTarget)) return;
            if (this.allowCustom && this.query.trim() !== '' && this.labelFor(this.value) !== this.query) {
                this.value = this.query.trim();
            }
            this.close();
        },
    }));

    /*
     * x-ui.disclosure / x-ui.accordion item over <details>/<summary>. Native without
     * JavaScript; with it, the summary click is intercepted so x-collapse can animate
     * the height: open sets [open] first and then expands, close collapses and then
     * removes [open]. Find-in-page opening a closed item (the toggle event) is followed.
     * An accordion with `exclusive` closes its other items.
     */
    Alpine.data('uiDisclosure', (initial = false) => ({
        open: initial,
        timer: null,

        toggle() {
            this.open ? this.collapse() : this.expand();
        },

        expand() {
            clearTimeout(this.timer);
            this.$root.open = true;
            this.open = true;
            this.$root.dispatchEvent(new CustomEvent('ui-disclosure-open', { bubbles: true }));
        },

        collapse() {
            this.open = false;
            clearTimeout(this.timer);
            const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches || document.documentElement.dataset.motionPreview === 'reduced';
            this.timer = setTimeout(() => {
                if (!this.open) this.$root.open = false;
            }, reduced ? 0 : 160);
        },

        synced() {
            if (this.$root.open && !this.open) this.expand();
        },
    }));

    /*
     * x-ui.popover. With the Popover API (popover="auto"), the browser gives light
     * dismiss, Escape, the top layer and focus return; Alpine only anchors it and
     * mirrors aria-expanded. Without it (older browsers), Alpine shows the panel and
     * handles Escape and a click outside.
     */
    Alpine.data('uiPopover', () => ({
        open: false,
        native: typeof HTMLElement !== 'undefined' && Object.prototype.hasOwnProperty.call(HTMLElement.prototype, 'popover'),

        get trigger() {
            return this.$refs.anchor?.querySelector('button, [role="button"]');
        },

        init() {
            const panel = this.$refs.panel;
            const trigger = this.trigger;
            trigger?.setAttribute('aria-controls', panel.id);
            trigger?.setAttribute('aria-expanded', 'false');
            if (this.native) {
                panel.setAttribute('popover', 'auto');
                // The invoker is exempt from light dismiss, so a click on an open trigger closes it once.
                trigger?.setAttribute('popovertarget', panel.id);
                panel.addEventListener('toggle', (event) => {
                    this.open = event.newState === 'open';
                    trigger?.setAttribute('aria-expanded', this.open ? 'true' : 'false');
                });
            } else {
                panel.removeAttribute('popover');
                this.$watch('open', (value) => trigger?.setAttribute('aria-expanded', value ? 'true' : 'false'));
            }
        },

        toggle() {
            if (!this.native) this.open = !this.open;
        },

        close(returnFocus = true) {
            if (this.native) this.$refs.panel.hidePopover?.();
            this.open = false;
            if (returnFocus) this.trigger?.focus();
        },
    }));

    /*
     * x-ui.audio: a preview player around <audio preload="none">. Play/pause is a
     * toggle button (aria-pressed); the seek bar is a styled range with "1:05 of 0:30"
     * as its value text; mute is a toggle. Only one player plays at a time. Space on
     * the play button toggles (native), Left/Right on it or the seek bar move 5s.
     */
    Alpine.data('uiAudio', ({ duration = 0, title = '' } = {}) => ({
        playing: false,
        loading: false,
        failed: false,
        muted: false,
        current: 0,
        duration,
        title,
        uid: Math.random().toString(36).slice(2),

        init() {
            const audio = this.$refs.audio;
            audio.addEventListener('loadedmetadata', () => {
                if (Number.isFinite(audio.duration)) this.duration = audio.duration;
            });
            audio.addEventListener('timeupdate', () => {
                this.current = audio.currentTime;
            });
            audio.addEventListener('waiting', () => {
                this.loading = true;
            });
            audio.addEventListener('playing', () => {
                this.loading = false;
                this.playing = true;
            });
            audio.addEventListener('pause', () => {
                this.playing = false;
                this.loading = false;
            });
            audio.addEventListener('ended', () => {
                this.playing = false;
                this.current = 0;
            });
            audio.addEventListener('error', () => {
                this.failed = true;
                this.loading = false;
                this.playing = false;
            });
            this.onOther = (event) => {
                if (event.detail !== this.uid && !audio.paused) audio.pause();
            };
            window.addEventListener(AUDIO_EVENT, this.onOther);
        },

        destroy() {
            window.removeEventListener(AUDIO_EVENT, this.onOther);
            this.$refs.audio?.pause();
        },

        async toggle() {
            const audio = this.$refs.audio;
            if (this.failed) return;
            if (!audio.paused) {
                audio.pause();

                return;
            }
            window.dispatchEvent(new CustomEvent(AUDIO_EVENT, { detail: this.uid }));
            this.loading = audio.readyState < 3;
            try {
                await audio.play();
            } catch (error) {
                this.loading = false;
                if (error?.name !== 'AbortError') this.failed = true;
            }
        },

        seek(seconds) {
            const audio = this.$refs.audio;
            const end = this.duration || audio.duration || 0;
            const target = clamp(Number(seconds), 0, end);
            this.current = target;
            try {
                audio.currentTime = target;
            } catch {
                // Not seekable until metadata has loaded; the position applies on play.
            }
        },

        nudge(delta) {
            this.seek(this.current + delta);
        },

        mute() {
            this.muted = !this.muted;
            this.$refs.audio.muted = this.muted;
        },

        get elapsed() {
            return formatDuration(this.current);
        },

        get total() {
            return formatDuration(this.duration);
        },

        get seekText() {
            return `${this.elapsed} of ${this.total}`;
        },
    }));

    /* x-ui.embed: the click-to-load facade swaps itself for the provider's iframe and moves focus to it. */
    Alpine.data('uiEmbed', () => ({
        loaded: false,
        load() {
            this.loaded = true;
            this.$nextTick(() => this.$refs.frame?.focus());
        },
    }));

    /*
     * Long form with a section nav: the link for the section nearest the top of the
     * viewport gets aria-current="true" (IntersectionObserver, no scroll listener).
     */
    Alpine.data('uiSectionNav', () => ({
        current: null,
        observer: null,

        init() {
            const links = [...this.$root.querySelectorAll('a[href^="#"]')];
            const sections = links.map((a) => document.getElementById(a.getAttribute('href').slice(1))).filter(Boolean);
            this.current = sections[0]?.id ?? null;
            // The observer only says "something moved"; the current section is the last one whose
            // top has passed a reading line 120px under the top bar, or the last one at the page end.
            const pick = () => {
                const atEnd = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 2;
                const passed = sections.filter((section) => section.getBoundingClientRect().top <= 184);
                this.current = (atEnd ? sections.at(-1) : passed.at(-1) ?? sections[0])?.id ?? null;
            };
            this.observer = new IntersectionObserver(pick, { threshold: [0, 0.1, 0.25, 0.5, 0.75, 1] });
            sections.forEach((section) => this.observer.observe(section));
        },

        destroy() {
            this.observer?.disconnect();
        },

        go(event, id) {
            this.current = id;
            const target = document.getElementById(id);
            const heading = target?.querySelector('h2, h3, [tabindex="-1"]');
            if (heading) {
                event.preventDefault();
                target.scrollIntoView({ block: 'start' });
                heading.setAttribute('tabindex', '-1');
                heading.focus({ preventScroll: true });
                history.replaceState(null, '', `#${id}`);
            }
        },
    }));

    /*
     * Wizard (x-ui.form-layout.wizard): announces "Step 2 of 5: Targets" after a step
     * change, moves focus to the step heading, and keeps an optional draft per flow in
     * sessionStorage (<prefix>draft_<flow>), restored through the Livewire
     * `restoreDraft` method when the server has nothing newer.
     */
    Alpine.data('uiWizard', ({ flow = null, step = 1, total = 1, label = '' } = {}) => ({
        step,
        total,
        label,
        seen: step,
        message: '',

        /*
         * The step heading is keyed per step, so it is a new element after each change and
         * calls this from x-init. Focus moves to it after the kit's submit settle (which
         * would otherwise put focus back on Continue), unless a field is invalid.
         */
        arrive(step, heading) {
            const key = this.$wire?.$id ?? 'wizard';
            const seen = wizardSteps.get(key) ?? this.seen;
            wizardSteps.set(key, step);
            if (step === seen) return;
            const wizard = heading.closest('[data-form-layout="wizard"]');
            setTimeout(() => {
                // The morph may have swapped the node Alpine first saw, so look it up again.
                const current = wizard?.querySelector('[data-wizard-heading]');
                if (current && !wizard.querySelector('[aria-invalid="true"]')) {
                    current.focus({ preventScroll: true });
                    current.scrollIntoView({ block: 'nearest' });
                }
            }, 120);
        },

        init() {
            this.message = `Step ${this.step} of ${this.total}: ${this.label}`;
            if (flow && this.$wire) {
                try {
                    const saved = window.sessionStorage.getItem(storageKey(`draft_${flow}`));
                    if (saved && typeof this.$wire.restoreDraft === 'function') this.$wire.restoreDraft(JSON.parse(saved));
                } catch {
                    // Storage unavailable or unreadable: start fresh.
                }
            }
        },

        changed(step, total, label, draft = null) {
            const moved = step !== this.step;
            this.step = step;
            this.total = total;
            this.label = label;
            if (flow) {
                try {
                    if (draft === null) window.sessionStorage.removeItem(storageKey(`draft_${flow}`));
                    else window.sessionStorage.setItem(storageKey(`draft_${flow}`), JSON.stringify(draft));
                } catch {
                    // Storage unavailable: the draft lives for this page only.
                }
            }
            if (!moved) return;
            this.message = '';
            setTimeout(() => {
                this.message = `Step ${step} of ${total}: ${label}`;
            }, 60);
        },
    }));

});

/*
 * Client-side invalid fallback. :user-invalid styles the control box where supported;
 * for every browser (and for screen readers) a kit control that fails its own
 * constraints (type=email, url, pattern, min/max) gets aria-invalid="true" on blur,
 * and loses it once valid. Server-set aria-invalid is never removed here.
 */
document.addEventListener('focusout', (event) => {
    const control = event.target;
    if (!(control instanceof HTMLInputElement) || !control.matches('[data-ui]') || typeof control.checkValidity !== 'function') {
        return;
    }
    if (control.value === '' && !control.required) {
        if (control.dataset.clientInvalid) {
            control.removeAttribute('aria-invalid');
            delete control.dataset.clientInvalid;
        }

        return;
    }
    const valid = control.validity.valid;
    if (!valid && control.getAttribute('aria-invalid') !== 'true') {
        control.setAttribute('aria-invalid', 'true');
        control.dataset.clientInvalid = '1';
    } else if (valid && control.dataset.clientInvalid) {
        control.removeAttribute('aria-invalid');
        delete control.dataset.clientInvalid;
    }
}, true);

/*
 * Unsaved-changes guard. A form with data-unsaved-guard that is dirty (x-ui.form's
 * `dirty`, mirrored to data-dirty) stops a full page unload (the browser's own
 * prompt) and a wire:navigate visit (the kit confirm "unsaved-changes", or
 * window.confirm when it is not on the page). Leaving through the confirm proceeds.
 */
function dirtyGuardedForm() {
    return document.querySelector('form[data-unsaved-guard][data-dirty="true"]');
}

window.addEventListener('beforeunload', (event) => {
    if (dirtyGuardedForm()) {
        event.preventDefault();
        event.returnValue = '';
    }
});

let pendingNavigation = null;

document.addEventListener('livewire:navigate', (event) => {
    if (!dirtyGuardedForm() || event.detail?.history) {
        return;
    }
    event.preventDefault();
    pendingNavigation = event.detail?.url?.toString?.() ?? String(event.detail?.url ?? '');
    if (document.querySelector('[data-dialog-host="unsaved-changes"]')) {
        window.dispatchEvent(new CustomEvent('open-dialog', { detail: 'unsaved-changes' }));
    } else if (window.confirm('Leave this page? Your changes are not saved.')) {
        leaveAnyway();
    } else {
        pendingNavigation = null;
    }
});

function leaveAnyway() {
    document.querySelectorAll('form[data-unsaved-guard]').forEach((form) => form.setAttribute('data-dirty', 'false'));
    const url = pendingNavigation;
    pendingNavigation = null;
    if (url) window.Livewire?.navigate(url);
}

window.addEventListener('confirmed', (event) => {
    const name = event.detail?.name ?? event.detail;
    if (name === 'unsaved-changes') leaveAnyway();
});

window.uiForms = { formatNumber, formatDuration, passwordStrength, isMac };

/*
 * Placeholders are never cut off: an input with data-placeholder-short shows its full
 * placeholder when it fits the box and the short one (then "Search") when it does not,
 * re-measured whenever the box resizes. The accessible name stays the full text.
 */
const placeholderCanvas = typeof document !== 'undefined' ? document.createElement('canvas').getContext('2d') : null;
const placeholderObserver = typeof ResizeObserver !== 'undefined' ? new ResizeObserver((entries) => entries.forEach((entry) => fitPlaceholder(entry.target))) : null;

function fitPlaceholder(input) {
    const style = getComputedStyle(input);
    placeholderCanvas.font = `${style.fontWeight} ${style.fontSize} ${style.fontFamily}`;
    const room = input.clientWidth - parseFloat(style.paddingLeft) - parseFloat(style.paddingRight) - 2;
    const choice = [input.dataset.placeholderFull, input.dataset.placeholderShort, 'Search'].find((text) => text && placeholderCanvas.measureText(text).width <= room) ?? 'Search';
    if (input.placeholder !== choice) input.placeholder = choice;
}

function watchPlaceholders() {
    // Refit every one: a Livewire morph may have put the full placeholder back.
    document.querySelectorAll('input[data-placeholder-short]').forEach((input) => {
        fitPlaceholder(input);
        if (input.dataset.placeholderWatched) return;
        input.dataset.placeholderWatched = '1';
        placeholderObserver?.observe(input);
    });
}

document.addEventListener('DOMContentLoaded', watchPlaceholders);
document.addEventListener('livewire:navigated', watchPlaceholders);
document.addEventListener('ui:deferred-start', watchPlaceholders);
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('commit', ({ succeed }) => succeed(() => requestAnimationFrame(watchPlaceholders)));
});
