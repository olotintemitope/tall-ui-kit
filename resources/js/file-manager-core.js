/*
 * x-ui.file-manager behaviour (docs/components/files.md, "File manager"), loaded lazily by
 * files.js. Delegated listeners on the manager: no Alpine per item.
 *
 * The items are one multi-select listbox in both views. Selection is kept here (a Set of
 * ids) and written to aria-selected; options carry wire:ignore.self, so a Livewire morph
 * updates their content but never their selection or tabindex. After each commit the
 * Set drops ids that are gone and the roving tab stop is put back.
 */

const managers = new Set();
let hooked = false;

function hook() {
    if (hooked || !window.Livewire?.hook) return;
    hooked = true;
    // An item being renamed keeps its input: the re-render that answers the rename is skipped for it.
    window.Livewire.hook('morph.updating', ({ el, skip }) => {
        if (el._fmEditing) skip();
    });
    window.Livewire.hook('commit', ({ succeed }) => succeed(() => requestAnimationFrame(() => managers.forEach((m) => m.sync(true)))));
}
document.addEventListener('livewire:init', hook);

const isTyping = (el) => !!el?.closest?.('input, textarea, [contenteditable]');

export function mount(c, o) {
    const root = c.$root;
    const selected = new Set();
    let anchor = null;
    let focusId = null;
    let afterChange = null; // { focus: id | 'first' | 'heading', say: text }
    let typed = '';
    let typedTimer = null;
    let dragDepth = 0;
    let dropTarget = null;
    let editing = null;

    const list = () => root.querySelector('[data-fm-list]');
    const options = () => [...(list()?.querySelectorAll(':scope > [role=option]') ?? [])];
    const byId = (id) => options().find((el) => el.dataset.id === String(id)) ?? null;
    const nameOf = (el) => el?.querySelector('.ui-fm-name')?.textContent ?? '';
    const isFolder = (el) => el?.hasAttribute('data-folder') ?? false;
    const disabled = (el) => el?.getAttribute('aria-disabled') === 'true';
    const heading = () => root.querySelector('[data-fm-heading]');
    const plural = (n, one = 'item', many = 'items') => `${n} ${n === 1 ? one : many}`;

    function say(text) {
        c.status = '';
        requestAnimationFrame(() => { c.status = text; });
    }

    /* Focus and announce once the re-render is on the page (the commit hook may run first). */
    function settle(change) {
        afterChange = change;
        requestAnimationFrame(() => requestAnimationFrame(() => sync()));
    }

    function call(method, ...args) {
        return Promise.resolve(c.$wire.call(method, ...args));
    }

    function toast(title) {
        window.dispatchEvent(new CustomEvent('toast', { detail: { variant: 'danger', title } }));
    }

    /* ---------- Selection ---------- */

    function paint() {
        const all = options();
        all.forEach((el) => el.setAttribute('aria-selected', selected.has(el.dataset.id) ? 'true' : 'false'));
        c.count = selected.size;
        c.loaded = all.length;
        const chosen = all.filter((el) => selected.has(el.dataset.id));
        c.canDownload = chosen.length > 0 && (!!o.download || chosen.every((el) => !isFolder(el) && el.dataset.href));
    }

    function announceSelection() {
        say(selected.size ? `${plural(selected.size)} selected.` : 'Selection cleared.');
    }

    function selectOnly(el) {
        selected.clear();
        if (el && !disabled(el)) selected.add(el.dataset.id);
        anchor = el?.dataset.id ?? null;
        paint();
        announceSelection();
    }

    function toggle(el) {
        if (!el || disabled(el)) return;
        const id = el.dataset.id;
        if (selected.has(id)) selected.delete(id);
        else selected.add(id);
        anchor = id;
        paint();
        announceSelection();
    }

    function range(el, additive = false) {
        const all = options();
        const from = all.findIndex((x) => x.dataset.id === anchor);
        const to = all.indexOf(el);
        if (to < 0) return;
        if (!additive) selected.clear();
        const [a, b] = from < 0 ? [to, to] : [Math.min(from, to), Math.max(from, to)];
        all.slice(a, b + 1).forEach((x) => !disabled(x) && selected.add(x.dataset.id));
        if (from < 0) anchor = el.dataset.id;
        paint();
        announceSelection();
    }

    function selectAll() {
        options().forEach((el) => !disabled(el) && selected.add(el.dataset.id));
        paint();
        say(`All ${plural(selected.size)} selected.`);
    }

    function clear(focusBack = false) {
        selected.clear();
        anchor = null;
        paint();
        say('Selection cleared.');
        if (focusBack) focus(byId(focusId) ?? options()[0] ?? heading());
    }

    /* ---------- Focus ---------- */

    function focus(el, scroll = true) {
        if (!el) return;
        if (el.getAttribute('role') === 'option') {
            options().forEach((x) => x.setAttribute('tabindex', x === el ? '0' : '-1'));
            focusId = el.dataset.id;
        }
        el.focus({ preventScroll: !scroll });
        if (scroll) el.scrollIntoView?.({ block: 'nearest' });
    }

    function current() {
        return document.activeElement?.closest?.('[role=option]') ?? byId(focusId) ?? options()[0] ?? null;
    }

    /* The item in the next or previous visual row (grid view), nearest in x. */
    function vertical(el, dir) {
        const all = options();
        if (c.view !== 'grid') return all[all.indexOf(el) + dir] ?? null;
        const r = el.getBoundingClientRect();
        const rows = all.map((x) => ({ x, r: x.getBoundingClientRect() }))
            .filter(({ r: q }) => (dir > 0 ? q.top > r.top + 4 : q.top < r.top - 4));
        if (!rows.length) return null;
        const rowTop = dir > 0 ? Math.min(...rows.map(({ r: q }) => q.top)) : Math.max(...rows.map(({ r: q }) => q.top));
        const row = rows.filter(({ r: q }) => Math.abs(q.top - rowTop) < 4);
        row.sort((p, q) => Math.abs(p.r.left - r.left) - Math.abs(q.r.left - r.left));

        return row[0]?.x ?? null;
    }

    /* ---------- Commands ---------- */

    function ids() {
        return options().filter((el) => selected.has(el.dataset.id)).map((el) => el.dataset.id);
    }

    /* Where focus goes once the selected items are gone: the next, else the previous, else the heading. */
    function survivor() {
        const all = options();
        const last = all.reduce((i, el, n) => (selected.has(el.dataset.id) ? n : i), -1);
        const after = all.slice(last + 1).find((el) => !selected.has(el.dataset.id));
        const before = all.slice(0, last).reverse().find((el) => !selected.has(el.dataset.id));

        return (after ?? before)?.dataset.id ?? 'heading';
    }

    function open(el) {
        if (!el || !o.open || disabled(el)) return;
        const folder = isFolder(el);
        const name = nameOf(el);
        call(o.open, el.dataset.id).then(() => {
            if (!folder) return;
            selected.clear();
            anchor = null;
            settle({ focus: 'first', say: () => `Opened ${name}. ${plural(options().length)}.` });
            focusId = null;
        });
    }

    function openSelected() {
        const [id] = ids();
        open(byId(id));
    }

    function startRename(el) {
        if (!el || !o.rename || editing || disabled(el)) return;
        const label = el.querySelector('.ui-fm-name');
        const old = nameOf(el);
        const input = document.createElement('input');
        const error = document.createElement('p');
        input.type = 'text';
        input.value = old;
        input.className = 'ui-fm-rename';
        input.setAttribute('aria-label', `Rename ${old}`);
        input.setAttribute('maxlength', '255');
        input.setAttribute('autocomplete', 'off');
        input.dataset.fmRename = '';
        error.id = `${o.id}-rename-error`;
        error.className = 'ui-fm-rename-error type-caption';
        error.hidden = true;
        label.hidden = true;
        label.after(input, error);
        editing = { el, input, error, label, old, busy: false };
        el._fmEditing = true;
        input.focus();
        const dot = old.lastIndexOf('.');
        input.setSelectionRange(0, dot > 0 && !isFolder(el) ? dot : old.length);

        const finish = (focusItem = true) => {
            if (!editing || editing.input !== input) return;
            editing = null;
            delete el._fmEditing;
            input.remove();
            error.remove();
            label.hidden = false;
            if (focusItem) focus(byId(el.dataset.id) ?? el);
        };

        const commit = () => {
            if (!editing || editing.busy) return;
            const value = input.value.trim();
            if (value === '' ) {
                show('Enter a name.');

                return;
            }
            if (value === old) {
                finish();

                return;
            }
            editing.busy = true;
            input.setAttribute('aria-busy', 'true');
            input.readOnly = true;
            call(o.rename, el.dataset.id, value).then((result) => {
                if (!editing || editing.input !== input) return;
                editing.busy = false;
                input.removeAttribute('aria-busy');
                input.readOnly = false;
                if (typeof result === 'string' && result !== '') {
                    show(result);

                    return;
                }
                label.textContent = value;
                finish();
                say(`Renamed to ${value}.`);
            }, () => {
                if (!editing) return;
                editing.busy = false;
                input.readOnly = false;
                input.removeAttribute('aria-busy');
                show('The name could not be saved. Try again.');
            });
        };

        const show = (message) => {
            error.textContent = message;
            error.hidden = false;
            input.setAttribute('aria-invalid', 'true');
            input.setAttribute('aria-describedby', error.id);
            input.focus();
            say(message);
        };

        input.addEventListener('keydown', (event) => {
            event.stopPropagation();
            if (event.key === 'Enter') {
                event.preventDefault();
                commit();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                finish();
                say('Rename cancelled.');
            }
        });
        input.addEventListener('blur', () => setTimeout(() => {
            if (editing?.input === input && !editing.busy && document.activeElement !== input) {
                if (input.value.trim() === old || input.value.trim() === '') finish(false);
                else commit();
            }
        }, 0));
        ['click', 'dblclick', 'pointerdown'].forEach((type) => input.addEventListener(type, (event) => event.stopPropagation()));
    }

    function renameSelected() {
        const [id] = ids();
        startRename(byId(id));
    }

    function download() {
        const chosen = options().filter((el) => selected.has(el.dataset.id));
        if (!chosen.length) return;
        if (chosen.length === 1 && chosen[0].dataset.href && !isFolder(chosen[0])) {
            const a = document.createElement('a');
            a.href = chosen[0].dataset.href;
            a.setAttribute('download', '');
            a.rel = 'noopener';
            document.body.append(a);
            a.click();
            a.remove();
            say(`Downloading ${nameOf(chosen[0])}.`);

            return;
        }
        if (o.download) {
            call(o.download, ids());
            say(`Downloading ${plural(chosen.length)}.`);
        }
    }

    function ensureSelection() {
        if (selected.size) return true;
        const el = current();
        if (!el || disabled(el)) return false;
        selectOnly(el);

        return true;
    }

    function askDelete() {
        if (!o.delete || !ensureSelection()) return;
        root.dispatchEvent(new CustomEvent('open-dialog', { detail: `${o.id}-delete`, bubbles: true }));
    }

    function remove() {
        const chosen = ids();
        if (!chosen.length) return;
        const next = survivor();
        const count = chosen.length;
        call(o.delete, chosen).then((result) => {
            if (typeof result === 'string' && result !== '') {
                say(result);
                toast(result);

                return;
            }
            chosen.forEach((id) => selected.delete(id));
            settle({ focus: next, say: `Deleted ${plural(count)}.` });
        }, () => {
            say('The items could not be deleted.');
        });
    }

    function askMove() {
        if (!o.move || !ensureSelection()) return;
        c.moveTarget = null;
        root.dispatchEvent(new CustomEvent('open-dialog', { detail: `${o.id}-move`, bubbles: true }));
    }

    function move(scope) {
        const target = c.moveTarget;
        if (target === null || target === undefined || scope.busy) return;
        const chosen = ids();
        if (chosen.includes(String(target))) {
            scope.error = 'A folder cannot be moved into itself.';

            return;
        }
        const label = root.querySelector(`[data-fm-move-dialog] [role=treeitem][data-id="${CSS.escape(String(target))}"] .ui-tree-label`)?.textContent ?? 'the folder';
        const next = survivor();
        scope.error = '';
        scope.busy = true;
        call(o.move, chosen, target).then((result) => {
            scope.busy = false;
            if (typeof result === 'string' && result !== '') {
                scope.error = result;

                return;
            }
            scope.close(true);
            chosen.forEach((id) => selected.delete(id));
            settle({ focus: next, say: `Moved ${plural(chosen.length)} to ${label}.` });
        }, () => {
            scope.busy = false;
            scope.error = 'The items could not be moved. Try again.';
        });
    }

    function create(scope) {
        const name = (c.newName ?? '').trim();
        if (scope.busy) return;
        if (name === '') {
            scope.error = 'Enter a folder name.';

            return;
        }
        scope.error = '';
        scope.busy = true;
        call(o.create, name).then((result) => {
            scope.busy = false;
            if (typeof result === 'string' && result !== '') {
                scope.error = result;

                return;
            }
            c.newName = '';
            scope.close(true);
            settle({ focus: { name }, say: `Created folder ${name}.` });
        }, () => {
            scope.busy = false;
            scope.error = 'The folder could not be created. Try again.';
        });
    }

    /* ---------- Uploads (Upload button and drops from the desktop) ---------- */

    const input = () => root.querySelector('[data-fm-upload-input]');

    function prepareUpload(folder) {
        if (!o.upload || !o.uploadTarget) return;
        c.$wire.$set(o.uploadTarget, folder ?? (root.dataset.folder || null), false);
    }

    function hasFiles(event) {
        return !!o.upload && [...(event.dataTransfer?.types ?? [])].includes('Files');
    }

    function setDrop(el) {
        if (dropTarget === el) return;
        dropTarget?.removeAttribute('data-drop-target');
        dropTarget = el;
        el?.setAttribute('data-drop-target', '');
        c.dropName = el ? nameOf(el) : (root.dataset.here ?? '');
    }

    function endDrag() {
        dragDepth = 0;
        setDrop(null);
        root.removeAttribute('data-dragging');
    }

    const onDragEnter = (event) => {
        if (!hasFiles(event)) return;
        event.preventDefault();
        dragDepth++;
        root.setAttribute('data-dragging', '');
    };
    const onDragOver = (event) => {
        if (!hasFiles(event)) return;
        event.preventDefault();
        event.dataTransfer.dropEffect = 'copy';
        const folder = event.target.closest?.('[role=option][data-folder]');
        setDrop(folder && !disabled(folder) ? folder : null);
    };
    const onDragLeave = (event) => {
        if (!hasFiles(event)) return;
        dragDepth = Math.max(0, dragDepth - 1);
        if (dragDepth === 0) endDrag();
    };
    const onDrop = (event) => {
        if (!hasFiles(event)) return;
        event.preventDefault();
        const folder = dropTarget;
        const files = [...(event.dataTransfer.files ?? [])];
        endDrag();
        const el = input();
        if (!files.length || !el) return;
        prepareUpload(folder ? folder.dataset.id : null);
        const transfer = new DataTransfer();
        files.forEach((file) => transfer.items.add(file));
        el.files = transfer.files;
        el.dispatchEvent(new Event('change', { bubbles: true }));
        say(`Uploading ${plural(files.length, 'file', 'files')} to ${folder ? nameOf(folder) : (root.dataset.here || 'this folder')}.`);
    };

    /* ---------- Keyboard and pointer ---------- */

    function typeahead(key) {
        clearTimeout(typedTimer);
        typed += key.toLowerCase();
        typedTimer = setTimeout(() => { typed = ''; }, 500);
        const all = options();
        const start = all.indexOf(current());
        const order = [...all.slice(start + (typed.length === 1 ? 1 : 0)), ...all.slice(0, start + (typed.length === 1 ? 1 : 0))];
        const hit = order.find((el) => nameOf(el).toLowerCase().startsWith(typed));
        if (hit) focus(hit);
    }

    const onKey = (event) => {
        const el = event.target.closest?.('[role=option]');
        if (!el || isTyping(event.target) || !list()?.contains(el)) return;
        const mod = event.ctrlKey || event.metaKey;
        const all = options();
        const i = all.indexOf(el);
        let next = null;
        switch (event.key) {
            case 'ArrowRight': if (c.view === 'grid') next = all[i + 1]; break;
            case 'ArrowLeft': if (c.view === 'grid') next = all[i - 1]; break;
            case 'ArrowDown': next = vertical(el, 1); if (!next && c.view === 'list') event.preventDefault(); break;
            case 'ArrowUp': next = vertical(el, -1); if (!next && c.view === 'list') event.preventDefault(); break;
            case 'Home': next = all[0]; break;
            case 'End': next = all[all.length - 1]; break;
            case ' ':
                event.preventDefault();
                if (event.shiftKey) range(el, true);
                else toggle(el);

                return;
            case 'Enter':
                event.preventDefault();
                open(el);

                return;
            case 'F2':
                event.preventDefault();
                startRename(el);

                return;
            case 'Delete':
            case 'Backspace':
                if (!o.delete) return;
                event.preventDefault();
                askDelete();

                return;
            case 'Escape':
                if (!selected.size) return;
                event.preventDefault();
                event.stopPropagation();
                clear();

                return;
            default:
                if (mod && (event.key === 'a' || event.key === 'A')) {
                    event.preventDefault();
                    selectAll();

                    return;
                }
                if (!mod && !event.altKey && event.key.length === 1 && /\S/.test(event.key)) typeahead(event.key);

                return;
        }
        if (['ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) event.preventDefault();
        if (!next) return;
        focus(next);
        if (event.shiftKey) range(next);
    };

    /* The check box is the item's ::before (top left in the grid, the first column in the list). */
    function onCheck(event, el) {
        const r = el.getBoundingClientRect();
        const x = event.clientX - r.left;
        const y = event.clientY - r.top;

        return event.detail > 0 && x >= 0 && x < 32 && (c.view === 'list' || y < 32);
    }

    const onClick = (event) => {
        if (event.target.closest?.('[data-fm-rename]')) return;
        const el = event.target.closest?.('[role=option]');
        if (!el || !list()?.contains(el)) return;
        focus(el, false);
        if (onCheck(event, el) || event.ctrlKey || event.metaKey) toggle(el);
        else if (event.shiftKey) range(el);
        else selectOnly(el);
    };

    const onDblClick = (event) => {
        const el = event.target.closest?.('[role=option]');
        if (el && !onCheck(event, el) && !event.target.closest('[data-fm-rename]')) open(el);
    };

    const onFocusIn = (event) => {
        const el = event.target.closest?.('[role=option]');
        if (el && el === event.target) {
            options().forEach((x) => x.setAttribute('tabindex', x === el ? '0' : '-1'));
            focusId = el.dataset.id;
        }
    };

    /* After a Livewire commit: drop gone ids, repaint, put the tab stop back, then focus and say what changed. */
    function sync() {
        if (!root.isConnected) return;
        c.sort = root.dataset.sort ?? c.sort;
        const present = new Set(options().map((el) => el.dataset.id));
        [...selected].forEach((id) => !present.has(id) && selected.delete(id));
        if (anchor && !present.has(anchor)) anchor = null;
        paint();
        const all = options();
        if (!all.some((el) => el.getAttribute('tabindex') === '0') || (focusId && !present.has(focusId))) {
            const stop = byId(focusId) ?? all[0];
            all.forEach((el) => el.setAttribute('tabindex', el === stop ? '0' : '-1'));
        }
        if (!afterChange) return;
        const { focus: where, say: text } = afterChange;
        afterChange = null;
        let target = null;
        if (where === 'first') target = all[0] ?? heading();
        else if (where === 'heading') target = heading();
        else if (typeof where === 'object' && where) target = all.find((el) => nameOf(el) === where.name) ?? null;
        else target = byId(where) ?? all[0] ?? heading();
        if (target) focus(target);
        if (text) say(typeof text === 'function' ? text() : text);
    }

    root.addEventListener('keydown', onKey);
    root.addEventListener('click', onClick);
    root.addEventListener('dblclick', onDblClick);
    root.addEventListener('focusin', onFocusIn);
    root.addEventListener('dragenter', onDragEnter);
    root.addEventListener('dragover', onDragOver);
    root.addEventListener('dragleave', onDragLeave);
    root.addEventListener('drop', onDrop);

    const api = {
        sync,
        selectAll,
        clear,
        openSelected,
        renameSelected,
        download,
        askDelete,
        remove,
        askMove,
        move,
        create,
        prepareUpload,
        viewChanged() {
            requestAnimationFrame(() => { const el = byId(focusId); if (el && document.activeElement === el) el.scrollIntoView({ block: 'nearest' }); });
        },
        destroy() {
            managers.delete(api);
            root.removeEventListener('keydown', onKey);
            root.removeEventListener('click', onClick);
            root.removeEventListener('dblclick', onDblClick);
            root.removeEventListener('focusin', onFocusIn);
            root.removeEventListener('dragenter', onDragEnter);
            root.removeEventListener('dragover', onDragOver);
            root.removeEventListener('dragleave', onDragLeave);
            root.removeEventListener('drop', onDrop);
        },
    };
    managers.add(api);
    hook();
    paint();

    return api;
}
