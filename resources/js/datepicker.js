/*
 * Custom date and time pickers: x-ui.date, x-ui.datetime, x-ui.month, x-ui.week,
 * x-ui.time-input and x-ui.date-range (all through x-ui.date-picker). No native picker
 * anywhere (PopupPlacementTest guards the views). docs/components/forms.md "Dates and times".
 *
 * One Alpine component per picker, cheap on load: it reads its config and watches its
 * value. The popup's contents (header, grid, time columns) are built with plain DOM the
 * first time it opens, and re-rendered as one innerHTML per month; every cell is a bare
 * element and all events are delegated to the popup, so there is no Alpine per cell.
 * Positioning is the lazy x-anchor (anchor.js), below only, fixed (dialogs, sheets and
 * table wrappers never clip it), and $revealBelow scrolls to show it.
 *
 * The value is what a native input posts (date 2026-11-15, time 18:05, month 2026-11,
 * week 2026-W47, datetime 2026-11-15T18:05; a range is { start, end }). It changes only
 * when something is chosen or typed text is committed (Enter, blur), never while arrowing,
 * so wire:model.live sends one request per choice. Then `input` and `change` fire on the
 * root (x-ui.form's dirty flag, wire:model.change), and a `blur` when focus leaves the
 * picker (wire:model.blur).
 *
 * Keyboard, in the grid (WAI-ARIA APG date picker dialog): arrows by day and week,
 * PageUp/PageDown by month, Shift+PageUp/PageDown by year, Home/End to the week's start
 * and end, Enter or Space choose, Escape closes and returns focus to what opened it, Tab
 * cycles inside the popup. In the field: typing is parsed leniently on Enter or blur
 * (date-core.js), Down or Alt+Down opens the popup.
 */

import * as D from './date-core';

const live = new Set();
const svg = (path) => `<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">${path}</svg>`;
const ICONS = { prev: svg('<path d="m15 18-6-6 6-6"/>'), next: svg('<path d="m9 18 6-6-6-6"/>'), down: svg('<path d="m6 9 6 6 6-6"/>') };
const esc = (s) => String(s).replace(/[&<>"]/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]));
const NOUN = { date: 'date', datetime: 'date and time', time: 'time', month: 'month', week: 'week', range: 'dates' };
const EXAMPLE = { date: '15 Nov 2026', datetime: '15 Nov 2026, 18:00', time: '18:00', month: 'Nov 2026', week: '2026-W47', range: '1 Nov 2026 – 15 Nov 2026' };
let uid = 0;

function weekStartFor(config) {
    if (Number.isInteger(config.weekStart)) return config.weekStart % 7;
    const forced = document.documentElement.dataset.uiWeekStart;
    if (forced !== undefined && forced !== '') return +forced % 7;

    return D.localeWeekStart(navigator.language || document.documentElement.lang || 'en');
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiDatePicker', (config = {}) => ({
        mode: 'date',
        value: '',
        open: false,
        built: false,
        view: null,
        focus: null,
        pending: null,
        hover: null,
        opener: null,
        outside: null,
        jump: null,
        spacer: null,
        weekStart: 1,
        uid: 0,

        init() {
            this.mode = this.$root.dataset.mode || 'date';
            // min and max ride on data attributes (shorter than JSON in x-data).
            config.min ??= this.$root.dataset.min ?? '';
            config.max ??= this.$root.dataset.max ?? '';
            this.uid = ++uid;
            this.wire();
            // The server's value (data-value; wire:model or x-model replace it right after).
            const initial = this.$root.dataset.value ?? '';
            if (this.mode === 'range') {
                try {
                    this.value = this.normal(JSON.parse(initial || '{}'));
                } catch {
                    this.value = { start: '', end: '' };
                }
            } else {
                this.value = initial;
            }
            this.weekStart = this.mode === 'week' ? 1 : weekStartFor(config);
            const bind = config.bind;
            if (this.mode === 'range' && bind) {
                const read = () => ({ start: String(this.$wire.$get(bind.start.name) ?? ''), end: String(this.$wire.$get(bind.end.name) ?? '') });
                this.value = read();
                this.$wire.$watch(bind.start.name, () => { this.value = read(); });
                this.$wire.$watch(bind.end.name, () => { this.value = read(); });
            }
            this.$watch('value', () => this.sync());
            live.add(this);
        },

        /* Listeners added here, not as x-on attributes, so the server markup stays small. */
        wire() {
            const root = this.$root;
            const field = this.$refs.text;
            const popup = this.$refs.popup;
            field.addEventListener('input', (event) => {
                event.stopPropagation();
                this.typing();
            });
            field.addEventListener('change', (event) => {
                event.stopPropagation();
                this.commitText();
            });
            field.addEventListener('keydown', (event) => this.fieldKey(event));
            this.$refs.button.addEventListener('click', (event) => this.toggle(event.currentTarget));
            root.addEventListener('focusout', (event) => this.focusOut(event));
            popup.addEventListener('click', (event) => this.click(event));
            popup.addEventListener('keydown', (event) => this.key(event));
            popup.addEventListener('mouseover', (event) => this.hoverCell(event));
            // A press on the popup's padding keeps focus where it is (it would fall to the page and close the popup).
            popup.addEventListener('mousedown', (event) => event.target.closest('button, [tabindex]') || event.preventDefault());
        },

        /* The polite region is in the server markup: a region added with its text is not read, and a morph would drop one added here. */
        say(message) {
            if (this.$refs.status) this.$refs.status.textContent = message;
        },

        destroy() {
            live.delete(this);
            this.unlisten();
            this.spacer?.remove();
        },

        /* ---------- value ---------- */

        normal(value) {
            if (this.mode === 'range') return { start: String(value?.start ?? ''), end: String(value?.end ?? '') };

            return value === null || value === undefined ? '' : String(value);
        },

        text() {
            return D.format(this.mode, this.normal(this.value), { hours: config.hours });
        },

        /* The field, the hidden inputs and an open popup follow the value. */
        sync() {
            const field = this.$refs.text;
            if (field && (document.activeElement !== field || !this.dirtyText)) {
                field.value = this.text();
                this.dirtyText = false;
            }
            const value = this.normal(this.value);
            this.$root.querySelectorAll(':scope [data-date-value]').forEach((input) => {
                const key = input.dataset.dateValue;
                input.value = this.mode === 'range' ? (value[key] ?? '') : value;
            });
            if (this.open) this.render();
        },

        emit() {
            this.$root.dispatchEvent(new Event('input', { bubbles: true }));
            this.$root.dispatchEvent(new Event('change', { bubbles: true }));
        },

        setValue(next) {
            const value = this.normal(next);
            if (JSON.stringify(value) === JSON.stringify(this.normal(this.value))) {
                this.sync();

                return;
            }
            const bind = config.bind;
            if (this.mode === 'range' && bind && this.$wire) {
                const isLive = bind.start.live || bind.end.live;
                this.$wire.$set(bind.start.name, value.start, false);
                this.$wire.$set(bind.end.name, value.end, isLive);
            }
            this.value = value;
            this.setInvalid(false);
            this.emit();
        },

        /* ---------- bounds ---------- */

        disabledList: null,

        isDisabledDay(isoDay) {
            const rule = config.disabledDates;
            if (!rule) return false;
            if (typeof rule === 'string') {
                const fn = rule.split('.').reduce((o, k) => o?.[k], window);

                return typeof fn === 'function' ? Boolean(fn(isoDay)) : false;
            }
            this.disabledList ??= new Set(rule);
            const weekday = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'][D.fromIso(isoDay).getUTCDay()];

            return this.disabledList.has(isoDay) || this.disabledList.has(weekday) || (this.disabledList.has('weekends') && (weekday === 'sat' || weekday === 'sun'));
        },

        /* Day bounds: for datetime the date part of min and max. */
        dayMin() {
            return String(config.min ?? '').slice(0, 10);
        },

        dayMax() {
            return String(config.max ?? '').slice(0, 10);
        },

        dayAllowed(isoDay) {
            return D.within(isoDay, this.dayMin(), this.dayMax()) && !this.isDisabledDay(isoDay);
        },

        allowed(value) {
            const min = config.min ?? '';
            const max = config.max ?? '';
            switch (this.mode) {
                case 'range': return this.dayAllowed(value.start) && this.dayAllowed(value.end);
                case 'date': return this.dayAllowed(value);
                case 'datetime': return D.within(value, min, max) && !this.isDisabledDay(value.slice(0, 10));
                default: return D.within(value, min, max);
            }
        },

        /* ---------- the text field ---------- */

        dirtyText: false,

        typing() {
            this.dirtyText = true;
        },

        commitText() {
            const field = this.$refs.text;
            if (!field || !this.dirtyText) return true;
            this.dirtyText = false;
            const raw = field.value.trim();
            if (raw === '') {
                this.setValue(this.mode === 'range' ? { start: '', end: '' } : '');

                return true;
            }
            const parsed = D.parse(this.mode, raw, D.today(), { defaultTime: config.defaultTime || '09:00' });
            if (!parsed) {
                this.setInvalid(true, `Enter a ${NOUN[this.mode]} like ${EXAMPLE[this.mode]}.`);

                return false;
            }
            if (!this.allowed(parsed)) {
                this.setInvalid(true, this.inBounds(parsed) ? `That ${NOUN[this.mode]} cannot be chosen.` : this.boundsMessage());

                return false;
            }
            this.setValue(parsed);
            field.value = this.text();

            return true;
        },

        /* Inside min and max, so a refusal means a disabled day. */
        inBounds(value) {
            if (this.mode === 'range') return D.within(value.start, this.dayMin(), this.dayMax()) && D.within(value.end, this.dayMin(), this.dayMax());

            return D.within(value, config.min ?? '', config.max ?? '');
        },

        boundsMessage() {
            const min = D.format(this.mode === 'range' ? 'date' : this.mode, this.mode === 'datetime' ? config.min : this.dayOr(config.min), { hours: config.hours });
            const max = D.format(this.mode === 'range' ? 'date' : this.mode, this.mode === 'datetime' ? config.max : this.dayOr(config.max), { hours: config.hours });
            if (min && max) return `Choose a ${NOUN[this.mode]} from ${min} to ${max}.`;
            if (min) return `Choose a ${NOUN[this.mode]} on or after ${min}.`;
            if (max) return `Choose a ${NOUN[this.mode]} on or before ${max}.`;

            return `That ${NOUN[this.mode]} cannot be chosen.`;
        },

        dayOr(value) {
            return this.mode === 'range' ? String(value ?? '').slice(0, 10) : value;
        },

        /* Typed text that is not a value: the field shows the error state and the reason is announced. */
        setInvalid(on, message = '') {
            const field = this.$refs.text;
            if (!field) return;
            if (on) {
                field.dataset.serverInvalid ??= field.getAttribute('aria-invalid') ?? '';
                field.setAttribute('aria-invalid', 'true');
            } else if (field.dataset.serverInvalid !== undefined) {
                const server = field.dataset.serverInvalid;
                delete field.dataset.serverInvalid;
                server ? field.setAttribute('aria-invalid', server) : field.removeAttribute('aria-invalid');
            }
            this.say(message);
        },

        fieldKey(event) {
            if (event.key === 'Enter') {
                if (this.dirtyText) {
                    event.preventDefault();
                    this.commitText();
                }
            } else if (event.key === 'ArrowDown') {
                event.preventDefault();
                this.commitText();
                this.show(event.currentTarget);
            } else if (event.key === 'Escape' && this.dirtyText) {
                event.preventDefault();
                event.stopPropagation();
                this.dirtyText = false;
                this.setInvalid(false);
                event.currentTarget.value = this.text();
            }
        },

        /* Focus left the picker: commit typed text, and tell wire:model.blur. */
        focusOut(event) {
            if (this.$root.contains(event.relatedTarget)) return;
            // A month change re-renders the grid under the focused day: focus moves on at once, so look again after it.
            setTimeout(() => {
                if (this.$root.contains(document.activeElement)) return;
                this.commitText();
                if (this.open) this.close(false);
                this.$root.dispatchEvent(new FocusEvent('blur'));
            }, 0);
        },

        /* ---------- popup ---------- */

        toggle(opener) {
            this.open ? this.close() : this.show(opener);
        },

        show(opener) {
            if (this.open || this.$refs.text?.disabled) return;
            this.opener = opener ?? this.$refs.button;
            this.build();
            this.pending = null;
            this.hover = null;
            this.prepareView();
            this.render();
            this.open = true;
            this.listen();
            this.$nextTick(() => {
                this.makeRoom();
                this.$revealBelow(this.$refs.popup, this.$refs.box);
                // The time columns were filled while hidden: scroll their chosen options into view now.
                this.$refs.popup.querySelectorAll('[data-col]').forEach((list) => this.activate(list, list.querySelector('[data-active]')));
                this.focusActive();
            });
        },

        /*
         * A fixed popup does not lengthen the page, so near the end of a short page there is
         * nothing to scroll and it would hang off the screen: a spacer at the end of the body
         * makes that room while it is open (never inside a dialog or sheet: they scroll their own body).
         */
        makeRoom() {
            const box = this.$refs.box;
            if (box.closest('dialog, [role=dialog]:not(.ui-date-pop)')) return;
            const page = document.scrollingElement || document.documentElement;
            const need = box.getBoundingClientRect().bottom + 6 + this.$refs.popup.offsetHeight + 8 - window.innerHeight;
            const spare = page.scrollHeight - page.clientHeight - page.scrollTop;
            if (need <= spare) return;
            this.spacer = Object.assign(document.createElement('div'), { ariaHidden: 'true' });
            this.spacer.setAttribute('data-date-spacer', '');
            this.spacer.style.height = `${Math.ceil(need - spare)}px`;
            document.body.append(this.spacer);
        },

        close(focus = true) {
            if (!this.open) return;
            this.spacer?.remove();
            this.spacer = null;
            this.closeJump(false);
            this.open = false;
            this.pending = null;
            this.unlisten();
            if (focus) (this.opener ?? this.$refs.button)?.focus({ preventScroll: true });
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

        /* The shown month (or year) and the focused cell: the value, else today, inside min and max. */
        prepareView() {
            const value = this.normal(this.value);
            let start;
            switch (this.mode) {
                case 'month': start = D.fromIso(`${value || ''}-01`); break;
                case 'week': { const w = D.fromWeek(value); start = w ? D.weekMonday(w.year, w.week) : null; break; }
                case 'range': start = D.fromIso(value.start); break;
                case 'time': start = null; break;
                default: start = D.fromIso(value);
            }
            let focus = start ?? D.today();
            if (this.mode === 'month') focus = D.firstOfMonth(focus);
            focus = this.clampFocus(focus);
            if (this.mode === 'week') focus = D.mondayOf(focus);
            this.focus = focus;
            this.view = this.mode === 'week' ? D.firstOfMonth(D.addDays(focus, 3)) : D.firstOfMonth(focus);
        },

        clampFocus(date) {
            if (this.mode === 'month') {
                const min = config.min ? D.fromIso(`${config.min}-01`) : null;
                const max = config.max ? D.fromIso(`${config.max}-01`) : null;
                if (min && date < min) return min;
                if (max && date > max) return max;

                return date;
            }
            if (this.mode === 'week') {
                const min = D.fromWeek(config.min);
                const max = D.fromWeek(config.max);
                if (min && date < D.weekMonday(min.year, min.week)) return D.weekMonday(min.year, min.week);
                if (max && date > D.weekMonday(max.year, max.week)) return D.weekMonday(max.year, max.week);

                return date;
            }

            return D.clampDate(date, this.dayMin(), this.dayMax());
        },

        /* ---------- building ---------- */

        id(part) {
            return `ui-date-${this.uid}-${part}`;
        },

        build() {
            if (this.built) return;
            this.built = true;
            const mode = this.mode;
            const popup = this.$refs.popup;
            const parts = [];
            if (mode === 'range' && config.presets?.length) {
                parts.push(`<div class="ui-cal-presets" role="group" aria-label="Presets">${config.presets.map((p, i) => `<button type="button" class="ui-cal-preset focus-ring" data-preset="${i}" aria-pressed="false">${esc(p.label)}</button>`).join('')}</div>`);
            }
            if (mode !== 'time') {
                const unit = mode === 'month' ? 'year' : 'month';
                parts.push(`<div class="ui-cal-days"><div class="ui-cal-head">
                    <button type="button" class="ui-cal-icon focus-ring" data-nav="-1" aria-label="Previous ${unit}">${ICONS.prev}</button>
                    <div class="ui-cal-jumps">
                        ${mode === 'month' ? '' : `<button type="button" class="ui-cal-jump focus-ring" data-jump="month" aria-haspopup="listbox" aria-expanded="false" aria-controls="${this.id('list')}" aria-label="Month"><span data-label></span>${ICONS.down}</button>`}
                        <button type="button" class="ui-cal-jump focus-ring" data-jump="year" aria-haspopup="listbox" aria-expanded="false" aria-controls="${this.id('list')}" aria-label="Year"><span data-label></span>${ICONS.down}</button>
                    </div>
                    <button type="button" class="ui-cal-icon focus-ring" data-nav="1" aria-label="Next ${unit}">${ICONS.next}</button>
                </div>
                <h2 id="${this.id('heading')}" class="sr-only" aria-live="polite" data-heading></h2>
                <table role="grid" class="ui-cal-grid" aria-labelledby="${this.id('heading')}" data-mode="${mode}" data-grid></table>
                <div role="listbox" id="${this.id('list')}" class="ui-cal-list" tabindex="-1" hidden data-list></div></div>`);
            }
            if (mode === 'time' || mode === 'datetime') {
                const twelve = +config.hours === 12;
                parts.push(`<div class="ui-time" data-time>
                    <div role="listbox" tabindex="0" aria-label="Hour" class="ui-time-col focus-ring" data-col="hour"></div>
                    <div role="listbox" tabindex="0" aria-label="Minute" class="ui-time-col focus-ring" data-col="minute"></div>
                    ${twelve ? '<div role="listbox" tabindex="0" aria-label="am or pm" class="ui-time-col focus-ring" data-col="meridiem"></div>' : ''}
                </div>`);
            }
            const today = mode === 'time' ? 'Now' : ({ month: 'This month', week: 'This week' }[mode] ?? 'Today');
            const done = mode === 'datetime' || mode === 'time' ? '<button type="button" class="ui-cal-link focus-ring" data-done>Done</button>' : '';
            parts.push(`<div class="ui-cal-foot"><button type="button" class="ui-cal-link focus-ring" data-today>${today}</button>${done}</div>`);
            popup.innerHTML = `<div class="ui-cal-body${mode === 'datetime' ? ' ui-cal-split' : ''}${mode === 'time' ? ' ui-cal-time' : ''}">${parts.join('')}</div>`;
        },

        /* ---------- rendering ---------- */

        render() {
            if (!this.built) return;
            if (this.mode !== 'time') {
                if (this.mode === 'month') this.renderMonths();
                else this.renderDays();
                this.renderHead();
            }
            if (this.mode === 'time' || this.mode === 'datetime') this.renderTime();
            this.renderPresets();
        },

        renderHead() {
            const popup = this.$refs.popup;
            const y = this.view.getUTCFullYear();
            const m = this.view.getUTCMonth();
            const month = popup.querySelector('[data-jump="month"] [data-label]');
            if (month) month.textContent = D.MONTHS[m];
            popup.querySelector('[data-jump="year"] [data-label]').textContent = String(y);
            const heading = popup.querySelector('[data-heading]');
            const text = this.mode === 'month' ? String(y) : `${D.MONTHS[m]} ${y}`;
            if (heading.textContent !== text) heading.textContent = text;
            // Previous and next stay focusable at the limits (focus never drops to the page): aria-disabled only.
            const step = this.mode === 'month' ? 12 : 1;
            const prev = D.addMonths(this.view, -step);
            const next = D.addMonths(this.view, step);
            const lo = this.mode === 'month' ? String(config.min ?? '').slice(0, 4) : this.boundMonth('min');
            const hi = this.mode === 'month' ? String(config.max ?? '').slice(0, 4) : this.boundMonth('max');
            const prevKey = this.mode === 'month' ? String(prev.getUTCFullYear()) : D.isoMonth(prev);
            const nextKey = this.mode === 'month' ? String(next.getUTCFullYear()) : D.isoMonth(next);
            popup.querySelector('[data-nav="-1"]').setAttribute('aria-disabled', lo && prevKey < lo ? 'true' : 'false');
            popup.querySelector('[data-nav="1"]').setAttribute('aria-disabled', hi && nextKey > hi ? 'true' : 'false');
        },

        /* The YYYY-MM of min or max, whatever the mode. */
        boundMonth(which) {
            const bound = config[which];
            if (!bound) return '';
            if (this.mode === 'week') {
                const w = D.fromWeek(bound);

                return w ? D.isoMonth(D.addDays(D.weekMonday(w.year, w.week), which === 'min' ? 0 : 6)) : '';
            }

            return String(bound).slice(0, 7);
        },

        weekdayHead(withWeek) {
            const cells = [];
            if (withWeek) cells.push('<th scope="col" abbr="Week" class="ui-cal-wk">Wk</th>');
            for (let i = 0; i < 7; i++) {
                const name = D.DAYS[(this.weekStart + i) % 7];
                cells.push(`<th scope="col" abbr="${name}">${name.slice(0, 2)}</th>`);
            }

            return `<thead><tr>${cells.join('')}</tr></thead>`;
        },

        renderDays() {
            const grid = this.$refs.popup.querySelector('[data-grid]');
            const week = this.mode === 'week';
            const month = this.view.getUTCMonth();
            const todayIso = D.iso(D.today());
            const value = this.normal(this.value);
            const focusIso = D.iso(this.focus);
            const selectedWeek = week ? value : '';
            const selectedDay = this.mode === 'datetime' ? value.slice(0, 10) : (this.mode === 'date' ? value : '');
            const rows = D.monthRows(this.view, this.weekStart);
            let body = '';
            for (const row of rows) {
                if (week) {
                    const monday = D.mondayOf(row[0]);
                    const key = D.weekString(D.isoWeek(monday));
                    const inMonth = row.some((d) => d.getUTCMonth() === month);
                    if (!inMonth) {
                        body += '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
                        continue;
                    }
                    const allowed = D.within(key, config.min ?? '', config.max ?? '');
                    const focused = D.iso(monday) === focusIso;
                    const selected = key === selectedWeek;
                    body += `<tr${selected ? ' data-selected' : ''}><td data-week="${key}" data-iso="${D.iso(monday)}" tabindex="${focused ? 0 : -1}" aria-selected="${selected}"${allowed ? '' : ' aria-disabled="true"'}${key === D.weekString(D.isoWeek(D.today())) ? ' aria-current="date"' : ''} aria-label="${D.formatWeek(key)}" class="ui-cal-wk">${D.isoWeek(monday).week}</td>`;
                    for (const day of row) body += `<td data-in-week="${key}"${day.getUTCMonth() === month ? '' : ' data-outside'}>${day.getUTCDate()}</td>`;
                    body += '</tr>';
                    continue;
                }
                body += '<tr>';
                for (const day of row) {
                    if (day.getUTCMonth() !== month) {
                        body += '<td></td>';
                        continue;
                    }
                    const id = D.iso(day);
                    const label = `${D.DAYS[day.getUTCDay()]} ${day.getUTCDate()} ${D.MONTHS[month]} ${day.getUTCFullYear()}`;
                    body += `<td data-iso="${id}" tabindex="${id === focusIso ? 0 : -1}" aria-selected="${id === selectedDay}"${this.dayAllowed(id) ? '' : ' aria-disabled="true"'}${id === todayIso ? ' aria-current="date"' : ''} aria-label="${label}">${day.getUTCDate()}</td>`;
                }
                body += '</tr>';
            }
            grid.innerHTML = `${this.weekdayHead(week)}<tbody>${body}</tbody>`;
            if (this.mode === 'range') this.markRange();
        },

        renderMonths() {
            const grid = this.$refs.popup.querySelector('[data-grid]');
            const y = this.view.getUTCFullYear();
            const focusKey = D.isoMonth(this.focus);
            const thisMonth = D.isoMonth(D.today());
            let body = '';
            for (let r = 0; r < 4; r++) {
                body += '<tr>';
                for (let c = 0; c < 3; c++) {
                    const m = r * 3 + c;
                    const key = `${D.pad(y, 4)}-${D.pad(m + 1)}`;
                    const allowed = D.within(key, config.min ?? '', config.max ?? '');
                    body += `<td data-month="${key}" tabindex="${key === focusKey ? 0 : -1}" aria-selected="${key === this.normal(this.value)}"${allowed ? '' : ' aria-disabled="true"'}${key === thisMonth ? ' aria-current="date"' : ''} aria-label="${D.MONTHS[m]} ${y}">${D.MONTHS[m].slice(0, 3)}</td>`;
                }
                body += '</tr>';
            }
            grid.innerHTML = `<tbody>${body}</tbody>`;
        },

        /* Range ends and the days between, including the hover preview while the end is not chosen. */
        markRange() {
            const value = this.normal(this.value);
            let start = this.pending ?? value.start;
            let end = this.pending ? (this.hover ?? this.pending) : value.end;
            if (start && end && end < start) [start, end] = [end, start];
            this.$refs.popup.querySelectorAll('[data-grid] td[data-iso]').forEach((cell) => {
                const id = cell.dataset.iso;
                const edge = id === start || id === end;
                cell.toggleAttribute('data-range-start', id === start);
                cell.toggleAttribute('data-range-end', id === end);
                cell.toggleAttribute('data-in-range', Boolean(start && end && id > start && id < end));
                cell.setAttribute('aria-selected', String(Boolean(start && (edge || (end && id > start && id < end)))));
            });
        },

        renderPresets() {
            if (this.mode !== 'range') return;
            const value = this.normal(this.value);
            this.$refs.popup.querySelectorAll('[data-preset]').forEach((button) => {
                const preset = config.presets[+button.dataset.preset];
                button.setAttribute('aria-pressed', String(Boolean(preset.start && preset.start === value.start && preset.end === value.end)));
            });
        },

        timeParts() {
            const value = this.normal(this.value);
            const time = this.mode === 'datetime' ? value.slice(11, 16) : value;
            const m = /^(\d{2}):(\d{2})$/.exec(time);

            return m ? { h: +m[1], m: +m[2] } : null;
        },

        renderTime() {
            const popup = this.$refs.popup;
            const twelve = +config.hours === 12;
            const parts = this.timeParts();
            const step = Math.max(1, Math.min(30, +config.minuteStep || 5));
            const option = (col, value, label, selected, disabled) => `<div role="option" id="${this.id(`${col}-${value}`)}" data-value="${value}" aria-selected="${selected}"${disabled ? ' aria-disabled="true"' : ''}>${label}</div>`;
            const hours = twelve ? [12, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11] : [...Array(24).keys()];
            const pm = parts ? parts.h >= 12 : false;
            const hourOf = (h) => (twelve ? (h % 12) + (pm ? 12 : 0) : h);
            const timeAllowed = (h, m) => this.timeAllowed(`${D.pad(h)}:${D.pad(m)}`);
            const fill = (col, html) => {
                const list = popup.querySelector(`[data-col="${col}"]`);
                list.innerHTML = html;
                const active = list.querySelector('[aria-selected="true"]') ?? list.querySelector('[role=option]:not([aria-disabled])') ?? list.firstElementChild;
                this.activate(list, active, true);
            };
            fill('hour', hours.map((h) => option('hour', h, twelve ? String(h) : D.pad(h), parts !== null && hourOf(h) === parts.h, ![...Array(60).keys()].some((m) => timeAllowed(hourOf(h), m)))).join(''));
            const minutes = [];
            for (let m = 0; m < 60; m += step) minutes.push(m);
            if (parts && !minutes.includes(parts.m)) minutes.push(parts.m);
            minutes.sort((a, b) => a - b);
            fill('minute', minutes.map((m) => option('minute', m, D.pad(m), parts !== null && parts.m === m, parts !== null && !timeAllowed(parts.h, m))).join(''));
            if (twelve) fill('meridiem', [['am', 'am'], ['pm', 'pm']].map(([v, l]) => option('meridiem', v, l, parts !== null && (v === 'pm') === pm, false)).join(''));
        },

        /* A time (HH:MM) inside min and max; for datetime, on the chosen day. */
        timeAllowed(time) {
            if (this.mode === 'time') return D.within(time, config.min ?? '', config.max ?? '');
            const day = this.normal(this.value).slice(0, 10) || D.iso(this.focus ?? D.today());

            return D.within(`${day}T${time}`, config.min ?? '', config.max ?? '');
        },

        /* ---------- focus ---------- */

        cells() {
            return this.$refs.popup.querySelector('[data-grid]');
        },

        focusActive() {
            const popup = this.$refs.popup;
            const target = this.mode === 'time' ? popup.querySelector('[data-col="hour"]') : popup.querySelector('[data-grid] [tabindex="0"]');
            target?.focus({ preventScroll: true });
        },

        /* Moves focus in the grid; another month re-renders (the heading announces it). */
        moveTo(date) {
            const next = this.clampFocus(this.mode === 'week' ? D.mondayOf(date) : date);
            this.focus = next;
            const anchor = this.mode === 'week' ? D.addDays(next, 3) : next;
            const view = this.mode === 'month' ? D.make(next.getUTCFullYear(), 0, 1) : D.firstOfMonth(anchor);
            if (!this.view || D.iso(view) !== D.iso(this.view)) {
                this.view = view;
                this.render();
            } else {
                const grid = this.cells();
                grid.querySelector('[tabindex="0"]')?.setAttribute('tabindex', '-1');
                grid.querySelector(this.cellSelector(next))?.setAttribute('tabindex', '0');
            }
            if (this.mode === 'range' && this.pending) {
                this.hover = D.iso(next);
                this.markRange();
            }
            this.cells().querySelector(this.cellSelector(next))?.focus({ preventScroll: true });
        },

        cellSelector(date) {
            return this.mode === 'month' ? `[data-month="${D.isoMonth(date)}"]` : `[data-iso="${D.iso(date)}"]${this.mode === 'week' ? '[data-week]' : ''}`;
        },

        /* Previous or next month (year in the month grid), focus staying on the button. */
        shiftView(step) {
            const months = this.mode === 'month' ? 12 * step : step;
            this.view = D.addMonths(this.view, months);
            let focus = D.addMonths(this.focus, months);
            focus = this.clampFocus(this.mode === 'week' ? D.mondayOf(focus) : focus);
            this.focus = this.mode === 'week' ? this.firstWeekIn(this.view, focus) : focus;
            this.render();
        },

        /* A Monday whose week shows in the view's month (prefers `want`). */
        firstWeekIn(view, want) {
            const month = view.getUTCMonth();

            return D.addDays(want, 3).getUTCMonth() === month ? want : D.mondayOf(D.addDays(view, 3));
        },

        /* ---------- choosing ---------- */

        chooseCell(cell) {
            if (!cell || cell.getAttribute('aria-disabled') === 'true') return;
            if (this.mode === 'month') {
                this.setValue(cell.dataset.month);
                this.close();

                return;
            }
            if (this.mode === 'week') {
                const key = cell.dataset.week ?? cell.dataset.inWeek;
                if (!key) return;
                this.setValue(key);
                this.close();

                return;
            }
            const day = cell.dataset.iso;
            if (!day) return;
            if (this.mode === 'range') {
                if (!this.pending) {
                    this.pending = day;
                    this.hover = day;
                    this.focus = D.fromIso(day);
                    this.markRange();
                    this.say(`Start ${D.formatDate(day)}. Choose the end date.`);

                    return;
                }
                const [start, end] = this.pending <= day ? [this.pending, day] : [day, this.pending];
                this.pending = null;
                this.setValue({ start, end });
                this.close();

                return;
            }
            if (this.mode === 'datetime') {
                const time = this.normal(this.value).slice(11, 16) || config.defaultTime || '09:00';
                this.$refs.popup.querySelector('[data-col="hour"]')?.focus({ preventScroll: true });
                this.focus = D.fromIso(day);
                this.setValue(`${day}T${time}`);
                this.render();

                return;
            }
            this.setValue(day);
            this.close();
        },

        chooseTime(col, option) {
            if (!option || option.getAttribute('aria-disabled') === 'true') return;
            const twelve = +config.hours === 12;
            const parts = this.timeParts() ?? { h: 0, m: 0 };
            let { h, m } = parts;
            const v = option.dataset.value;
            if (col === 'hour') h = twelve ? (+v % 12) + (h >= 12 ? 12 : 0) : +v;
            if (col === 'minute') m = +v;
            if (col === 'meridiem') h = (h % 12) + (v === 'pm' ? 12 : 0);
            const time = `${D.pad(h)}:${D.pad(m)}`;
            if (this.mode === 'datetime') {
                const day = this.normal(this.value).slice(0, 10) || D.iso(this.focus ?? D.today());
                this.setValue(`${day}T${time}`);
            } else {
                this.setValue(time);
            }
            this.renderTime();
            const order = ['hour', 'minute', ...(twelve ? ['meridiem'] : [])];
            const nextCol = order[order.indexOf(col) + 1];
            if (nextCol) this.$refs.popup.querySelector(`[data-col="${nextCol}"]`)?.focus({ preventScroll: true });
            else this.close();
        },

        today() {
            const now = D.today();
            switch (this.mode) {
                case 'time': {
                    const d = new Date();
                    const step = Math.max(1, +config.minuteStep || 5);
                    const time = `${D.pad(d.getHours())}:${D.pad(Math.floor(d.getMinutes() / step) * step)}`;
                    if (this.timeAllowed(time)) {
                        this.setValue(time);
                        this.close();
                    }

                    return;
                }
                case 'month': {
                    const key = D.isoMonth(now);
                    if (D.within(key, config.min ?? '', config.max ?? '')) {
                        this.setValue(key);
                        this.close();
                    } else this.moveTo(now);

                    return;
                }
                case 'week': {
                    const key = D.weekString(D.isoWeek(now));
                    if (D.within(key, config.min ?? '', config.max ?? '')) {
                        this.setValue(key);
                        this.close();
                    } else this.moveTo(now);

                    return;
                }
                case 'date':
                    if (this.dayAllowed(D.iso(now))) {
                        this.setValue(D.iso(now));
                        this.close();
                    } else this.moveTo(now);

                    return;
                default:
                    // Range and datetime: Today moves the calendar there; the day is chosen as usual.
                    this.moveTo(now);
            }
        },

        /* ---------- events (delegated from the popup) ---------- */

        click(event) {
            const target = event.target;
            const nav = target.closest('[data-nav]');
            if (nav) {
                if (nav.getAttribute('aria-disabled') !== 'true') this.shiftView(+nav.dataset.nav);

                return;
            }
            const jump = target.closest('[data-jump]');
            if (jump) {
                this.jump?.button === jump ? this.closeJump() : this.openJump(jump);

                return;
            }
            const option = target.closest('[role=option]');
            if (option) {
                const list = option.parentElement;
                if (list.matches('[data-list]')) this.chooseJump(option);
                else if (list.dataset.col) this.chooseTime(list.dataset.col, option);

                return;
            }
            if (target.closest('[data-today]')) return this.today();
            if (target.closest('[data-done]')) return this.close();
            const preset = target.closest('[data-preset]');
            if (preset) {
                const p = config.presets[+preset.dataset.preset];
                if (p.start && p.end) {
                    this.setValue({ start: p.start, end: p.end });
                    this.close();
                } else {
                    this.pending = null;
                    this.focusActive();
                }

                return;
            }
            const cell = target.closest('[data-grid] td');
            if (cell && (cell.dataset.iso || cell.dataset.month || cell.dataset.inWeek)) {
                if (this.mode === 'week' && cell.dataset.inWeek && !cell.dataset.week) {
                    this.chooseCell(cell.parentElement.querySelector('[data-week]'));

                    return;
                }
                this.chooseCell(cell);
            }
        },

        hoverCell(event) {
            if (this.mode !== 'range' || !this.pending) return;
            const day = event.target.closest?.('[data-grid] td[data-iso]')?.dataset.iso;
            if (day && day !== this.hover) {
                this.hover = day;
                this.markRange();
            }
        },

        key(event) {
            const target = event.target;
            if (target.matches('[data-list]')) return this.jumpKey(event);
            if (target.closest('[data-col]')) return this.timeKey(event, target.closest('[data-col]'));
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                if (this.pending) {
                    this.pending = null;
                    this.hover = null;
                    this.markRange();

                    return;
                }
                this.close();

                return;
            }
            if (event.key === 'Tab') return this.trap(event);
            const cell = target.closest('[data-grid] td');
            if (!cell) return;
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.chooseCell(cell);

                return;
            }
            const from = this.focus;
            const next = this.mode === 'month' ? D.monthKey(from, event.key) : (this.mode === 'week' ? D.weekKey(from, event.key, event.shiftKey) : D.dayKey(from, event.key, event.shiftKey, this.weekStart));
            if (!next) return;
            event.preventDefault();
            this.moveTo(next);
        },

        /* Tab and Shift+Tab cycle inside the popup (a modal dialog in APG terms). */
        trap(event) {
            const items = [...this.$refs.popup.querySelectorAll('button, [tabindex="0"]')].filter((el) => el.getClientRects().length && !el.closest('[hidden]'));
            if (!items.length) return;
            const index = items.indexOf(document.activeElement);
            const next = event.shiftKey ? (index <= 0 ? items.length - 1 : index - 1) : (index === items.length - 1 ? 0 : index + 1);
            event.preventDefault();
            items[next].focus({ preventScroll: true });
        },

        /* ---------- time columns (listboxes; focus on the list, aria-activedescendant on the option) ---------- */

        activate(list, option, scroll = true) {
            list.querySelector('[data-active]')?.removeAttribute('data-active');
            if (!option) {
                list.removeAttribute('aria-activedescendant');

                return;
            }
            option.setAttribute('data-active', '');
            list.setAttribute('aria-activedescendant', option.id);
            if (scroll) {
                const top = option.offsetTop - list.offsetTop;
                if (top < list.scrollTop || top + option.offsetHeight > list.scrollTop + list.clientHeight) list.scrollTop = Math.max(0, top - list.clientHeight / 2 + option.offsetHeight / 2);
            }
        },

        listKey(event, list, choose) {
            const options = [...list.querySelectorAll('[role=option]')];
            const index = options.findIndex((o) => o.hasAttribute('data-active'));
            const move = (to) => {
                event.preventDefault();
                this.activate(list, options[Math.max(0, Math.min(options.length - 1, to))]);
            };
            switch (event.key) {
                case 'ArrowDown': return move(index + 1);
                case 'ArrowUp': return move(index - 1);
                case 'Home': return move(0);
                case 'End': return move(options.length - 1);
                case 'PageDown': return move(index + 5);
                case 'PageUp': return move(index - 5);
                case 'Enter':
                case ' ':
                    event.preventDefault();
                    choose(options[index]);

                    return true;
                default: return false;
            }
        },

        timeKey(event, list) {
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                this.close();

                return;
            }
            if (event.key === 'Tab') return this.trap(event);
            if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
                const cols = [...this.$refs.popup.querySelectorAll('[data-col]')];
                const next = cols[cols.indexOf(list) + (event.key === 'ArrowRight' ? 1 : -1)];
                if (next) {
                    event.preventDefault();
                    next.focus({ preventScroll: true });
                }

                return;
            }
            this.listKey(event, list, (option) => this.chooseTime(list.dataset.col, option));
        },

        /* ---------- month and year jump (a small custom select inside the popup) ---------- */

        openJump(button) {
            this.closeJump(false);
            const list = this.$refs.popup.querySelector('[data-list]');
            const kind = button.dataset.jump;
            const y = this.view.getUTCFullYear();
            const nowYear = D.today().getUTCFullYear();
            let html = '';
            if (kind === 'month') {
                const lo = this.boundMonth('min');
                const hi = this.boundMonth('max');
                D.MONTHS.forEach((name, m) => {
                    const key = `${D.pad(y, 4)}-${D.pad(m + 1)}`;
                    const off = (lo && key < lo) || (hi && key > hi);
                    html += `<div role="option" id="${this.id(`m${m}`)}" data-value="${m}" aria-selected="${m === this.view.getUTCMonth()}"${off ? ' aria-disabled="true"' : ''}>${name}</div>`;
                });
            } else {
                const lo = +(String(config.min ?? '').slice(0, 4)) || Math.min(y, nowYear - 100);
                const hi = +(String(config.max ?? '').slice(0, 4)) || Math.max(y, nowYear + 50);
                for (let year = lo; year <= hi; year++) html += `<div role="option" id="${this.id(`y${year}`)}" data-value="${year}" aria-selected="${year === y}">${year}</div>`;
            }
            list.innerHTML = html;
            list.setAttribute('aria-label', kind === 'month' ? 'Month' : 'Year');
            list.hidden = false;
            list.style.left = `${button.offsetLeft}px`;
            list.style.top = `${button.offsetTop + button.offsetHeight + 4}px`;
            button.setAttribute('aria-expanded', 'true');
            this.jump = { button, list };
            this.activate(list, list.querySelector('[aria-selected="true"]') ?? list.firstElementChild);
            list.focus({ preventScroll: true });
        },

        closeJump(focus = true) {
            if (!this.jump) return;
            const { button, list } = this.jump;
            list.hidden = true;
            button.setAttribute('aria-expanded', 'false');
            this.jump = null;
            if (focus) button.focus({ preventScroll: true });
        },

        chooseJump(option) {
            if (!option || option.getAttribute('aria-disabled') === 'true' || !this.jump) return;
            const kind = this.jump.button.dataset.jump;
            const v = +option.dataset.value;
            const y = kind === 'year' ? v : this.view.getUTCFullYear();
            const m = kind === 'month' ? v : this.view.getUTCMonth();
            const day = this.mode === 'month' ? 1 : this.focus.getUTCDate();
            let focus = D.make(y, m, Math.min(day, D.daysIn(y, m)));
            focus = this.clampFocus(this.mode === 'week' ? D.mondayOf(focus) : focus);
            this.focus = focus;
            this.view = this.mode === 'month' ? D.make(focus.getUTCFullYear(), 0, 1) : D.firstOfMonth(this.mode === 'week' ? D.addDays(focus, 3) : focus);
            this.render();
            this.closeJump(true);
        },

        jumpKey(event) {
            const list = event.target;
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                this.closeJump(true);

                return;
            }
            if (event.key === 'Tab') {
                this.closeJump(true);
                event.preventDefault();

                return;
            }
            this.listKey(event, list, (option) => this.chooseJump(option));
        },
    }));
});

/* After a Livewire morph, a picker whose field the server re-rendered shows its value again. */
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('morphed', ({ el }) => {
        live.forEach((picker) => {
            if (el.contains(picker.$root)) picker.sync();
        });
    });
});
