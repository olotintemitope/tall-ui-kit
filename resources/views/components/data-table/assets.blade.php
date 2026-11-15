{{--
    Shared CSS and behaviour for x-data-table, printed once on the first render inside a
    wire:ignore block (so later Livewire responses stay small and morphs keep it).
    Everything is scoped to [data-dt] and uses kit tokens (CSS variables) only.

    CSS: sticky header (inside the table's own scroll box), sticky first column, 1px `line`
    rules, hover fill (`panel-footer`), selected tint (`primary` at 5%), density, the
    stacked card layout below 640px, the <mark> highlight and the lean row checkbox.

    window.uiDataTable($el): the Alpine controller on the table root. It
    owns focus after every Livewire re-render (docs/ui-kit-tables.md, Focus):
      - remembers the last focused control by its data-fk key and puts focus back on it
        if a morph dropped focus to the body;
      - intents set before a request: heading (pagination), token (removed filter
        token: next, previous, then search), rows (row or bulk action: the target row,
        else the next row, the previous row, then the heading), key (a given control);
      - the shared row action menu (one per table, positioned under its trigger): roving
        focus, arrows, Home, End, Escape and Tab; focus returns to the row's trigger;
      - confirm dialogs (x-ui.confirm-dialog): focus is put on the trigger before the
        dialog opens, so the dialog's trap returns it there.
    Local equivalents of the kit's planned focusAfter / returnFocus helpers: migrate
    them to resources/js/ui.js once they land.
--}}
<style>
    /* Positions: with a checkbox column the title column is the 2nd cell, else the 1st;
       with row actions the actions cell is the last. Rows carry no position classes. */
    [data-dt] .dt-scroll { overflow: auto; scroll-padding-top: 2.25rem; scroll-padding-left: 12rem; }
    /* The sticky bulk bar never hides a focused row or control: scroll padding is the bar's measured height (--dt-bulk-h, set by the bar; it wraps to several lines at 320px). */
    [data-dt][data-selection] .dt-scroll { scroll-padding-bottom: calc(var(--dt-bulk-h, 4rem) + 0.5rem); }
    html:has([data-dt-bulk-bar]) { scroll-padding-bottom: calc(var(--dt-bulk-h, 4rem) + 1rem); }
    @media (min-width: 640px) { [data-dt] .dt-scroll { max-height: max(24rem, calc(100dvh - 15rem)); } }
    [data-dt] table { border-collapse: separate; border-spacing: 0; width: 100%; }
    [data-dt] thead th { position: sticky; top: 0; z-index: 2; background: var(--color-panel-footer); border-bottom: 1px solid var(--color-line); height: 2.25rem; padding: 0 1rem; text-align: left; white-space: nowrap; }
    [data-dt] tbody td { height: 2.5rem; padding: 0.5rem 1rem; border-bottom: 1px solid var(--color-line); background: var(--color-surface); white-space: normal; overflow-wrap: break-word; transition: background-color 100ms; }
    /* Text is never cut off: cells wrap and rows grow; numbers and dates stay on one line. */
    [data-dt] tbody td.text-right, [data-dt] tbody td .type-numeric { white-space: nowrap; }
    [data-dt] tbody tr:last-child td { border-bottom: 0; }
    [data-dt] tbody td[data-wrap] { padding-block: 0.5rem; }
    [data-dt][data-density='compact'] tbody td { height: 2rem; padding-top: 0.25rem; padding-bottom: 0.25rem; }
    [data-dt][data-density='compact'] tbody td[data-wrap] { padding-block: 0.25rem; }
    [data-dt] tbody tr:hover td { background: var(--color-panel-footer); }
    [data-dt] tbody tr[data-selected] td { background: color-mix(in srgb, var(--color-primary) 5%, var(--color-surface)); }
    [data-dt] th[data-n], [data-dt] td[data-n] { text-align: right; }
    [data-dt][data-has-check] tr > :first-child { width: 2.5rem; padding-right: 0; position: sticky; left: 0; z-index: 1; overflow: visible; }
    [data-dt][data-has-check] tr > :nth-child(2), [data-dt]:not([data-has-check]) tr > :first-child { position: sticky; left: 0; z-index: 1; }
    [data-dt][data-has-check] tr > :nth-child(2) { left: 2.5rem; }
    [data-dt] thead tr > :first-child, [data-dt][data-has-check] thead tr > :nth-child(2) { z-index: 3; }
    [data-dt][data-has-actions] tr > :last-child { width: 3rem; padding-right: 0.5rem; text-align: right; overflow: visible; }
    [data-dt] .dt-t { display: block; min-width: 0; overflow-wrap: break-word; }
    [data-dt] mark { background: color-mix(in srgb, var(--color-primary) 18%, var(--color-surface)); color: var(--color-ink); border-radius: 2px; padding: 0 1px; }
    [data-dt] .dt-check { appearance: none; width: 1rem; height: 1rem; margin: 0; display: block; border: 1px solid var(--color-control); border-radius: 4px; background: var(--color-surface) center / 0.75rem no-repeat; cursor: pointer; transition: background-color 100ms, border-color 100ms; }
    [data-dt] .dt-check:hover { border-color: var(--color-control-hover); }
    [data-dt] .dt-check:checked { background-color: var(--color-secondary); border-color: var(--color-secondary); background-image: var(--ui-check-image); }
    [data-dt] .dt-check:indeterminate { background-color: var(--color-secondary); border-color: var(--color-secondary); background-image: var(--ui-dash-image); }
    [data-dt] .dt-check[aria-disabled='true'] { border-style: dashed; background-color: var(--color-surface-subtle); cursor: not-allowed; }
    [data-dt] .dt-check:focus-visible, [data-dt] .dt-sort:focus-visible, [data-dt] .dt-act:focus-visible { outline: 2px solid var(--color-focus); outline-offset: 2px; }
    [data-dt] .dt-sort { display: inline-flex; align-items: center; gap: 0.25rem; min-height: 1.5rem; margin: 0 -0.25rem; padding: 0 0.25rem; border-radius: 4px; text-transform: uppercase; letter-spacing: inherit; font: inherit; color: inherit; }
    [data-dt] .dt-sort:hover { color: var(--color-ink); }
    [data-dt] .dt-act { display: inline-flex; width: 2rem; height: 2rem; align-items: center; justify-content: center; border-radius: 6px; color: var(--color-ink-muted); transition: background-color 100ms, color 100ms; }
    [data-dt] .dt-act:hover, [data-dt] .dt-act[aria-expanded='true'] { background: var(--color-surface-muted); color: var(--color-ink); }
    [data-dt] .dt-act svg { width: 1rem; height: 1rem; }
    [data-dt] .dt-sort-idle { opacity: 0; transition: opacity 100ms; }
    [data-dt] .dt-sort:hover .dt-sort-idle, [data-dt] .dt-sort:focus-visible .dt-sort-idle { opacity: 1; }
    [data-dt] [data-dt-heading]:focus { outline: none; }
    @media (forced-colors: active) {
        [data-dt] .dt-check { appearance: auto; }
        [data-dt] .dt-sort-idle { opacity: 1; }
    }
    @media (prefers-reduced-motion: reduce) {
        [data-dt] *, [data-dt] .dt-sort-idle { transition: none !important; }
    }
    @media (max-width: 639.98px) {
        [data-dt][data-layout='stack'] .dt-scroll { overflow: visible; max-height: none; }
        [data-dt][data-layout='stack'] thead { display: none; }
        [data-dt][data-layout='stack'] table, [data-dt][data-layout='stack'] tbody { display: block; }
        [data-dt][data-layout='stack'] tbody tr { display: flex; flex-direction: column; gap: 0.25rem; position: relative; padding: 0.75rem 1rem; border-bottom: 1px solid var(--color-line); background: var(--color-surface); }
        [data-dt][data-layout='stack'][data-has-check] tbody tr { padding-left: 2.75rem; }
        [data-dt][data-layout='stack'][data-has-actions] tbody tr { padding-right: 3rem; }
        [data-dt][data-layout='stack'] tbody tr:last-child { border-bottom: 0; }
        [data-dt][data-layout='stack'] tbody tr:hover td { background: transparent; } [data-dt][data-layout='stack'] tbody tr:not([data-selected]):hover { background: var(--color-panel-footer); }
        [data-dt][data-layout='stack'] tbody tr[data-selected] { background: color-mix(in srgb, var(--color-primary) 5%, var(--color-surface)); }
        [data-dt][data-layout='stack'] tbody td { position: static; display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; width: auto; height: auto; min-height: 0; padding: 0; border: 0; background: transparent; white-space: normal; text-align: right; max-width: none; overflow: visible; overflow-wrap: anywhere; min-width: 0; }
        [data-dt][data-layout='stack'] tbody td > * { min-width: 0; overflow-wrap: anywhere; }
        [data-dt][data-layout='stack'] tbody td[data-wrap] { display: block; }
        [data-dt][data-layout='stack'] tbody td[data-wrap]::before { float: left; margin-right: 0.75rem; }
        [data-dt][data-layout='stack'] tbody td[data-label]::before { content: attr(data-label); flex: none; text-align: left; font-family: var(--font-body); font-size: var(--text-label); line-height: var(--text-label--line-height); font-weight: var(--text-label--font-weight); letter-spacing: var(--text-label--letter-spacing); text-transform: uppercase; color: var(--color-ink-muted); }
        [data-dt][data-layout='stack']:not([data-has-check]) tbody td:first-child, [data-dt][data-layout='stack'][data-has-check] tbody td:nth-child(2) { display: block; text-align: left; }
        [data-dt][data-layout='stack'][data-has-check] tbody td:first-child { position: absolute; left: 1rem; top: 0.875rem; width: auto; padding: 0; }
        [data-dt][data-layout='stack'][data-has-actions] tbody td:last-child { position: absolute; right: 0.5rem; top: 0.375rem; width: auto; padding: 0; }
    }
    /* Below 1024px a stack table whose columns do not fit is stacked too (scrollRegion() sets data-stacked), so tablets never scroll sideways. */
    [data-dt][data-layout='stack'][data-stacked] .dt-scroll { overflow: visible; max-height: none; }
    [data-dt][data-layout='stack'][data-stacked] thead { display: none; }
    [data-dt][data-layout='stack'][data-stacked] table, [data-dt][data-layout='stack'][data-stacked] tbody { display: block; }
    [data-dt][data-layout='stack'][data-stacked] tbody tr { display: flex; flex-direction: column; gap: 0.25rem; position: relative; padding: 0.75rem 1rem; border-bottom: 1px solid var(--color-line); background: var(--color-surface); }
    [data-dt][data-layout='stack'][data-stacked][data-has-check] tbody tr { padding-left: 2.75rem; }
    [data-dt][data-layout='stack'][data-stacked][data-has-actions] tbody tr { padding-right: 3rem; }
    [data-dt][data-layout='stack'][data-stacked] tbody tr:last-child { border-bottom: 0; }
    [data-dt][data-layout='stack'][data-stacked] tbody tr:hover td { background: transparent; } [data-dt][data-layout='stack'][data-stacked] tbody tr:not([data-selected]):hover { background: var(--color-panel-footer); }
    [data-dt][data-layout='stack'][data-stacked] tbody tr[data-selected] { background: color-mix(in srgb, var(--color-primary) 5%, var(--color-surface)); }
    [data-dt][data-layout='stack'][data-stacked] tbody td { position: static; display: flex; align-items: baseline; justify-content: space-between; gap: 0.75rem; width: auto; height: auto; min-height: 0; padding: 0; border: 0; background: transparent; white-space: normal; text-align: right; max-width: none; overflow: visible; overflow-wrap: anywhere; min-width: 0; }
    [data-dt][data-layout='stack'][data-stacked] tbody td > * { min-width: 0; overflow-wrap: anywhere; }
    [data-dt][data-layout='stack'][data-stacked] tbody td[data-wrap] { display: block; }
    [data-dt][data-layout='stack'][data-stacked] tbody td[data-wrap]::before { float: left; margin-right: 0.75rem; }
    [data-dt][data-layout='stack'][data-stacked] tbody td[data-label]::before { content: attr(data-label); flex: none; text-align: left; font-family: var(--font-body); font-size: var(--text-label); line-height: var(--text-label--line-height); font-weight: var(--text-label--font-weight); letter-spacing: var(--text-label--letter-spacing); text-transform: uppercase; color: var(--color-ink-muted); }
    [data-dt][data-layout='stack'][data-stacked]:not([data-has-check]) tbody td:first-child, [data-dt][data-layout='stack'][data-stacked][data-has-check] tbody td:nth-child(2) { display: block; text-align: left; }
    [data-dt][data-layout='stack'][data-stacked][data-has-check] tbody td:first-child { position: absolute; left: 1rem; top: 0.875rem; width: auto; padding: 0; }
    [data-dt][data-layout='stack'][data-stacked][data-has-actions] tbody td:last-child { position: absolute; right: 0.5rem; top: 0.375rem; width: auto; padding: 0; }
</style>
<svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
    <symbol id="dt-i-ellipsis" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/><circle cx="5" cy="12" r="1"/></symbol>
    <symbol id="dt-i-sort" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7 15 5 5 5-5"/><path d="m7 9 5-5 5 5"/></symbol>
    <symbol id="dt-i-left" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></symbol>
    <symbol id="dt-i-right" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></symbol>
    <symbol id="dt-i-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></symbol>
</svg>
<script>
    window.uiDataTable = window.uiDataTable || function (root) {
        // root is the table's root element; its dataset carries dt (name), defaultColumns and
        // requiredColumns (comma lists). Focus state (S) lives on the element, not on the Alpine
        // object: a morph can leave the root with a cloned scope, and the morph hook must see
        // what the clicks set.
        const config = root.dataset;
        const list = (value) => (value || '').split(',').filter(Boolean);
        const defaults = list(config.defaultColumns);
        const required = list(config.requiredColumns);
        const S = root._dtState ??= { intent: null, lastKey: null, rowOrder: [], pendingRow: null };
        return {
            name: config.dt,
            menu: { open: false, id: null, actions: [], hrefs: {}, trigger: null, label: '', style: '' },
            setIntent(intent) {
                S.intent = intent;
            },

            init() {
                this.decorate();
                S.rowOrder = this.rowIds();
                const live = () => window.Alpine.$data(root);
                this.scrollRegion();
                if (root._dtBound) return;
                root._dtBound = true;
                // After every morph of this component: decorate rows, then settle focus. Runs in
                // the morph's own task (before paint, and before the kit's 60ms focus settle,
                // which then sees focus already placed and leaves it).
                const hook = () => window.Livewire.hook('morphed', ({ el }) => {
                    if (el === root || el.contains(root)) live().afterRender();
                });
                this.unhook = window.Livewire ? hook() : null;
                if (! this.unhook) document.addEventListener('livewire:init', () => { this.unhook = hook() }, { once: true });
                root._dtUnhook = () => typeof this.unhook === 'function' && this.unhook();
                root.addEventListener('focusin', (event) => {
                    const key = live().keyOf(event.target);
                    if (key) S.lastKey = key;
                });
                // Pagination (rule 6): after the new page renders, focus the caption.
                root.addEventListener('click', (event) => {
                    if (event.target.closest('[data-dt-pager] [data-page]')) S.intent = { kind: 'heading' };
                }, true);
                // Row action triggers (delegated, so rows carry no Alpine bindings).
                root.addEventListener('click', (event) => {
                    const trigger = event.target.closest('.dt-act');
                    if (trigger && root.contains(trigger)) live().openMenu(trigger);
                });
            },

            // Live state for the controls rendered once inside wire:ignore.
            filterHas(key, value) {
                const current = (this.$wire.filters || {})[key];
                return Array.isArray(current) ? current.includes(value) : (current && typeof current === 'object' ? Object.values(current).includes(value) : false);
            },
            filterCount(key) {
                const current = (this.$wire.filters || {})[key];
                return Array.isArray(current) ? current.length : (current && typeof current === 'object' ? Object.keys(current).length : 0);
            },
            colShown(key) {
                const chosen = (this.$wire.columns || '').split(',').filter(Boolean);
                return (chosen.length ? chosen : defaults).includes(key) || required.includes(key);
            },

            // Stacked cards read each cell's label from data-label; explicit table roles keep
            // the table semantics when CSS changes the display of rows and cells. Both are
            // derived from the header after every morph, so the rows sent stay small.
            decorate() {
                const table = root.querySelector('[data-dt-scroll] > table');
                if (! table || ! table.tHead) return;
                const stack = root.dataset.layout === 'stack';
                const heads = [...table.tHead.rows[0].cells];
                const check = root.hasAttribute('data-has-check');
                const actions = root.hasAttribute('data-has-actions');
                const title = check ? 1 : 0;
                const labels = heads.map((th, i) => (i <= title || (actions && i === heads.length - 1)) ? '' : th.textContent.trim());
                if (stack) {
                    table.setAttribute('role', 'table');
                    [table.tHead, ...table.tBodies].forEach((group) => group.setAttribute('role', 'rowgroup'));
                    [...table.rows].forEach((tr) => tr.setAttribute('role', 'row'));
                    heads.forEach((th) => th.setAttribute('role', 'columnheader'));
                }
                const classes = heads.map((th) => th.dataset.cell);
                [...table.tBodies[0].rows].filter((tr) => tr.dataset.row).forEach((tr) => {
                    [...tr.children].forEach((td, i) => {
                        // Rows arrive without per-cell classes; each column's live on its th.
                        if (classes[i] !== undefined && td.className !== classes[i]) td.className = classes[i];
                        if (stack) td.setAttribute('role', 'cell');
                        if (labels[i]) td.dataset.label = labels[i];
                    });
                });
            },

            destroy() {
                if (! root.isConnected && root._dtUnhook) { root._dtUnhook(); root._dtBound = false; }
            },

            rowIds() {
                return [...root.querySelectorAll('[data-dt-scroll] tbody tr[data-row]')].map((row) => row.dataset.row);
            },
            // Focus keys: data-fk on toolbar and footer controls; row-{id}-check and
            // row-{id}-actions for the row controls (derived, so rows stay small).
            keyOf(el) {
                const keyed = el.closest('[data-fk]');
                if (keyed && root.contains(keyed)) return keyed.dataset.fk;
                const row = el.closest('tr[data-row]');
                if (! row || ! root.contains(row)) return null;
                if (el.matches('.dt-check')) return 'row-' + row.dataset.row + '-check';
                if (el.matches('.dt-act')) return 'row-' + row.dataset.row + '-actions';
                return null;
            },
            byKey(key) {
                if (! key) return null;
                const row = key.match(/^row-(\d+)-(check|actions)$/);
                if (row) return root.querySelector('tr[data-row="' + row[1] + '"] ' + (row[2] === 'check' ? '.dt-check' : '.dt-act'));
                return root.querySelector('[data-fk="' + CSS.escape(key) + '"]');
            },
            heading() {
                return root.querySelector('[data-dt-heading]');
            },
            search() {
                return root.querySelector('[data-fk="search"]');
            },
            focus(el) {
                if (el && el.getClientRects().length) { el.focus(); return true; }
                return false;
            },
            firstInRow(id) {
                const row = root.querySelector('tbody tr[data-row="' + CSS.escape(id) + '"]');
                return row ? row.querySelector('input, button, a[href], [tabindex="0"]') : null;
            },
            // Rule 3: the target row if it is still there, else the next row, the previous row, then the heading.
            focusNear(oldOrder, ids, preferKey) {
                if (preferKey && this.focus(this.byKey(preferKey))) return;
                const now = new Set(this.rowIds());
                const indexes = ids.map((id) => oldOrder.indexOf(id)).filter((i) => i >= 0);
                if (! indexes.length) { this.focus(this.heading()); return; }
                const first = Math.min(...indexes);
                const last = Math.max(...indexes);
                if (now.has(oldOrder[first]) && this.focus(this.firstInRow(oldOrder[first]))) return;
                for (let i = last + 1; i < oldOrder.length; i++) {
                    if (now.has(oldOrder[i]) && this.focus(this.firstInRow(oldOrder[i]))) return;
                }
                for (let i = first - 1; i >= 0; i--) {
                    if (now.has(oldOrder[i]) && this.focus(this.firstInRow(oldOrder[i]))) return;
                }
                this.focus(this.heading());
            },

            // A table box that scrolls becomes a focusable region named by the caption, so it can
            // be scrolled by keyboard (as x-ui.table does); checked on resize and after each morph.
            scrollRegion() {
                const box = root.querySelector('[data-dt-scroll]');
                if (! box) return;
                this.fitLayout(box);
                const scrolls = box.scrollWidth > box.clientWidth + 1 || box.scrollHeight > box.clientHeight + 1;
                if (scrolls) { box.setAttribute('tabindex', '0'); box.setAttribute('role', 'region'); box.setAttribute('aria-label', box.dataset.dtScroll); }
                else { box.removeAttribute('tabindex'); box.removeAttribute('role'); box.removeAttribute('aria-label'); }
                // wire:navigate leaves the old page's table detached: its observer stops there.
                if (! box._dtObserved) { box._dtObserved = true; const watcher = new ResizeObserver(() => { if (! root.isConnected) { watcher.disconnect(); return; } window.Alpine.$data(root).scrollRegion?.(); }); watcher.observe(box); }
            },

            // A stack table stacks below 640px (CSS) and, below 1024px, whenever its columns do not
            // fit its box: measured unstacked in the same task, so nothing paints in between.
            fitLayout(box) {
                if (root.dataset.layout !== 'stack') return;
                const width = box.clientWidth;
                root.removeAttribute('data-stacked');
                const table = box.querySelector('table');
                const overflows = width >= 640 && width < 1024 && table && table.scrollWidth > width + 1;
                if (overflows) root.setAttribute('data-stacked', '');
            },

            // Called from the morphed hook. decorate() runs at once, in the same task as the
            // morph, so rows never paint without their classes.
            afterRender() {
                this.decorate();
                this.scrollRegion();
                this.$nextTick(() => {
                    const intent = S.intent;
                    const oldOrder = S.rowOrder;
                    S.intent = null;
                    S.rowOrder = this.rowIds();
                    const active = document.activeElement;
                    const lost = ! active || active === document.body || ! document.contains(active);

                    if (intent && intent.kind === 'heading') return this.focus(this.heading());
                    if (intent && intent.kind === 'token') {
                        const tokens = [...root.querySelectorAll('[data-dt-token] button')];
                        return this.focus(tokens[intent.index]) || this.focus(tokens[intent.index - 1]) || this.focus(this.search());
                    }
                    if (intent && intent.kind === 'key') return this.focus(this.byKey(intent.key)) || this.focus(this.heading());
                    if (intent && intent.kind === 'rows') return this.focusNear(oldOrder, intent.ids, intent.key);
                    if (! lost) return;

                    if (this.focus(this.byKey(S.lastKey))) return;
                    const row = (S.lastKey || '').match(/^row-(\d+)-/);
                    if (row) return this.focusNear(oldOrder, [row[1]]);
                    this.focus(this.heading());
                });
            },

            removeToken(button) {
                const tokens = [...root.querySelectorAll('[data-dt-token] button')];
                S.intent = { kind: 'token', index: tokens.indexOf(button) };
            },
            clearSelectionIntent() {
                S.intent = { kind: 'key', key: 'select-page' };
            },
            selectedOnPage() {
                return [...root.querySelectorAll('tbody tr[data-selected]')].map((row) => row.dataset.row);
            },

            // Bulk actions: confirm first when needed; afterwards rule 3 on the selected rows.
            bulk(key, needsConfirm, trigger) {
                if (needsConfirm) {
                    trigger.focus();
                    this.$dispatch('open-confirm', this.name + '-bulk-' + key);
                    return;
                }
                S.intent = { kind: 'rows', ids: this.selectedOnPage() };
                this.$wire.runBulkAction(key);
            },

            // Shared row action menu.
            openMenu(button) {
                if (this.menu.open && this.menu.trigger === button) return this.closeMenu(true);
                button.dataset.rowId = button.closest('tr[data-row]').dataset.row;
                // The menu is as wide as its longest label (never narrower than 12rem, never wider
                // than the screen), so labels sit on one line; it is measured once shown, then its
                // right edge is aligned with the trigger's and kept on screen.
                const place = () => {
                    const rect = button.getBoundingClientRect();
                    const width = this.$refs.rowMenu.offsetWidth || 192;
                    const left = Math.max(8, Math.min(window.innerWidth - width - 8, rect.right - width));
                    return 'top:' + (rect.bottom + 4) + 'px;left:' + left + 'px';
                };
                this.menu = {
                    open: true,
                    id: button.dataset.rowId,
                    actions: (button.dataset.actions || '').split(' ').filter(Boolean),
                    hrefs: JSON.parse(button.dataset.hrefs || '{}'),
                    trigger: button,
                    label: button.getAttribute('aria-label'),
                    style: 'top:' + (button.getBoundingClientRect().bottom + 4) + 'px;left:8px;visibility:hidden',
                };
                root.querySelectorAll('.dt-act[aria-expanded=true]').forEach((other) => other.setAttribute('aria-expanded', 'false'));
                button.setAttribute('aria-expanded', 'true');
                this.$nextTick(() => {
                    this.menu = { ...this.menu, style: place() };
                    this.menuItems()[0]?.focus({ preventScroll: true });
                    // Always below the trigger: scroll to make room rather than open upwards.
                    window.uiRevealBelow?.(this.$refs.rowMenu, button).then((scrolled) => {
                        if (scrolled && this.menu.open && this.menu.trigger === button) {
                            this.menu = { ...this.menu, style: place() };
                        }
                    });
                });
            },
            closeMenu(returnFocus) {
                if (! this.menu.open) return;
                const trigger = this.menu.trigger;
                trigger?.setAttribute('aria-expanded', 'false');
                this.menu = { ...this.menu, open: false };
                if (returnFocus && trigger && document.contains(trigger)) trigger.focus();
            },
            menuItems() {
                return [...this.$refs.rowMenu.querySelectorAll('[role=menuitem]')].filter((item) => item.getClientRects().length);
            },
            menuMove(step) {
                const items = this.menuItems();
                const index = items.indexOf(document.activeElement);
                items[(index + step + items.length) % items.length]?.focus();
            },
            rowAction(key, needsConfirm) {
                const id = this.menu.id;
                const triggerKey = 'row-' + id + '-actions';
                this.closeMenu(true);
                if (needsConfirm) {
                    S.pendingRow = id;
                    this.$dispatch('open-confirm', this.name + '-row-' + key);
                    return;
                }
                S.intent = { kind: 'rows', ids: [id], key: triggerKey };
                this.$wire.runRowAction(key, id);
            },

            // x-ui.confirm-dialog dispatches `confirmed` with its name.
            confirmed(name) {
                const bulk = this.name + '-bulk-';
                const row = this.name + '-row-';
                if (typeof name !== 'string') return;
                if (name.startsWith(bulk)) {
                    S.intent = { kind: 'rows', ids: this.selectedOnPage() };
                    this.$wire.runBulkAction(name.slice(bulk.length), true);
                } else if (name.startsWith(row) && S.pendingRow) {
                    const id = S.pendingRow;
                    S.pendingRow = null;
                    S.intent = { kind: 'rows', ids: [id], key: 'row-' + id + '-actions' };
                    this.$wire.runRowAction(name.slice(row.length), id, true);
                }
            },
        };
    };
</script>
