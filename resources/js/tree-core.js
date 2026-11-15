/*
 * x-ui.tree behaviour (docs/components/workflow.md, "Tree"), loaded lazily by widgets.js.
 * WAI-ARIA APG tree view: roving tabindex, arrow keys, Home/End, *, typeahead, Enter and
 * Space. One set of listeners per tree (on the role=tree list), no Alpine per row.
 * `component` is the Alpine object; its `value` is x-modelable.
 */

const MAX_CHILDREN = 500;
const icon = (name) => {
    // The same Lucide paths x-ui.icon prints (chevron-right, check, minus).
    const paths = { 'chevron-right': '<path d="m9 18 6-6-6-6"/>', check: '<path d="M20 6 9 17l-5-5"/>', minus: '<path d="M5 12h14"/>' };
    const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    svg.setAttribute('viewBox', '0 0 24 24');
    svg.setAttribute('fill', 'none');
    svg.setAttribute('stroke', 'currentColor');
    svg.setAttribute('stroke-width', name === 'chevron-right' ? '2' : '3');
    svg.setAttribute('stroke-linecap', 'round');
    svg.setAttribute('stroke-linejoin', 'round');
    svg.setAttribute('aria-hidden', 'true');
    svg.innerHTML = paths[name];

    return svg;
};

export function mount(component, o) {
    const root = component.$root;
    // Started again on the same element (a re-initialised component): keep one set of listeners.
    if (root._uiTree) {
        root._uiTree.adopt(component);

        return;
    }
    const tree = root.querySelector('[role=tree]');
    const live = root.querySelector('[data-tree-live]');
    let buffer = '';
    let bufferTimer = null;
    let counter = 0;

    const items = () => [...tree.querySelectorAll('[role=treeitem]')];
    const visible = () => items().filter((el) => el.getClientRects().length > 0);
    const labelOf = (item) => item.querySelector(':scope > .ui-tree-row .ui-tree-label')?.textContent ?? '';
    const parentOf = (item) => item.parentElement?.closest('[role=treeitem]') ?? null;
    const childrenOf = (item) => [...(item.querySelector(':scope > [role=group]')?.children ?? [])].filter((el) => el.getAttribute('role') === 'treeitem');
    const isParent = (item) => item.hasAttribute('aria-expanded');
    const isOpen = (item) => item.getAttribute('aria-expanded') === 'true';
    const disabled = (item) => item.getAttribute('aria-disabled') === 'true';

    function say(text) {
        live.textContent = '';
        requestAnimationFrame(() => {
            live.textContent = text;
        });
    }

    function focus(item) {
        if (!item) return;
        items().forEach((el) => el.setAttribute('tabindex', el === item ? '0' : '-1'));
        item.focus();
    }

    /* ---------- Lazy children ---------- */

    function wire() {
        try {
            return component.$wire ?? null;
        } catch {
            return null;
        }
    }

    function build(node, level, setsize, posinset, checked) {
        const li = document.createElement('li');
        const id = `${o.id}-l${++counter}`;
        const lazy = !Array.isArray(node.children) && node.hasChildren === true;
        li.setAttribute('role', 'treeitem');
        li.id = id;
        li.setAttribute('aria-labelledby', `${id}-label`);
        li.setAttribute('aria-level', String(level));
        li.setAttribute('aria-setsize', String(setsize));
        li.setAttribute('aria-posinset', String(posinset));
        li.setAttribute('tabindex', '-1');
        li.className = 'ui-tree-item';
        li.dataset.id = String(node.id);
        li.style.setProperty('--tree-level', String(level - 1));
        const kids = Array.isArray(node.children) ? node.children.slice(0, MAX_CHILDREN) : [];
        if (lazy || kids.length) li.setAttribute('aria-expanded', 'false');
        if (lazy) li.dataset.lazy = '';
        if (o.mode === 'select') li.setAttribute('aria-selected', String(String(node.id) === String(component.value)));
        if (o.mode === 'check') li.setAttribute('aria-checked', checked ? 'true' : 'false');
        if (node.disabled === true) li.setAttribute('aria-disabled', 'true');
        if (typeof node.href === 'string' && /^(https?:|\/|#)/.test(node.href)) li.dataset.href = node.href;

        const row = document.createElement('div');
        row.className = 'ui-tree-row';
        const twisty = document.createElement('span');
        twisty.className = 'ui-tree-twisty';
        twisty.setAttribute('aria-hidden', 'true');
        if (lazy || kids.length) twisty.append(icon('chevron-right'));
        row.append(twisty);
        if (o.mode === 'check') {
            const box = document.createElement('span');
            box.className = 'ui-tree-box';
            box.setAttribute('aria-hidden', 'true');
            const check = icon('check');
            check.classList.add('ui-tree-check');
            const dash = icon('minus');
            dash.classList.add('ui-tree-dash');
            box.append(check, dash);
            row.append(box);
        }
        const label = document.createElement('span');
        label.id = `${id}-label`;
        label.className = 'ui-tree-label';
        label.textContent = String(node.label ?? '');
        const status = document.createElement('span');
        status.className = 'ui-tree-status type-caption';
        status.setAttribute('aria-hidden', 'true');
        status.dataset.treeStatus = '';
        row.append(label, status);
        li.append(row);
        if (lazy || kids.length) {
            const group = document.createElement('ul');
            group.setAttribute('role', 'group');
            kids.filter(valid).forEach((child, i, all) => group.append(build(child, level + 1, all.length, i + 1, checked)));
            li.append(group);
        }

        return li;
    }

    const valid = (node) => node && typeof node === 'object' && (typeof node.id === 'string' || typeof node.id === 'number') && typeof node.label === 'string';

    async function load(item) {
        if (!o.lazy || item.dataset.loading === '1') return;
        const server = wire();
        const status = item.querySelector(':scope > .ui-tree-row [data-tree-status]');
        if (!server) return;
        item.dataset.loading = '1';
        item.setAttribute('aria-busy', 'true');
        status.textContent = 'Loading';
        try {
            const result = await server.call(o.lazy, item.dataset.id);
            const list = (Array.isArray(result) ? result : []).filter(valid).slice(0, MAX_CHILDREN);
            const group = item.querySelector(':scope > [role=group]');
            const level = Number(item.getAttribute('aria-level')) + 1;
            const checked = item.getAttribute('aria-checked') === 'true';
            group.replaceChildren(...list.map((node, i) => build(node, level, list.length, i + 1, checked)));
            delete item.dataset.lazy;
            status.textContent = '';
            if (!list.length) {
                item.removeAttribute('aria-expanded');
                item.querySelector(':scope > .ui-tree-row .ui-tree-twisty').replaceChildren();
            }
            say(list.length ? `${labelOf(item)}: ${list.length} ${list.length === 1 ? 'item' : 'items'} loaded.` : `${labelOf(item)} is empty.`);
            if (o.mode === 'check') syncChecks();
        } catch {
            item.setAttribute('aria-expanded', 'false');
            status.textContent = 'Could not load';
            say(`Could not load ${labelOf(item)}. Press Right arrow to try again.`);
        } finally {
            item.removeAttribute('aria-busy');
            delete item.dataset.loading;
        }
    }

    function setOpen(item, open) {
        if (!isParent(item) || isOpen(item) === open) return;
        item.setAttribute('aria-expanded', String(open));
        if (open && item.hasAttribute('data-lazy')) load(item);
    }

    /* ---------- Selection and checks ---------- */

    function select(item) {
        if (disabled(item)) return;
        if (o.mode === 'select') {
            items().forEach((el) => el.setAttribute('aria-selected', String(el === item)));
            component.value = item.dataset.id;
            root.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: item.dataset.id } }));
        } else if (o.mode === 'check') {
            toggleCheck(item);
        } else if (item.dataset.href) {
            window.location.assign(item.dataset.href);
        } else if (isParent(item)) {
            setOpen(item, !isOpen(item));
        }
    }

    function toggleCheck(item) {
        const next = item.getAttribute('aria-checked') !== 'true';
        [item, ...item.querySelectorAll('[role=treeitem]')].forEach((el) => !disabled(el) && el.setAttribute('aria-checked', String(next)));
        syncChecks();
        commitChecks();
    }

    /* Parents follow their loaded children: all checked, none, or mixed. */
    function syncChecks() {
        const all = items().reverse();
        all.forEach((item) => {
            const kids = childrenOf(item);
            if (!kids.length) return;
            const states = kids.map((k) => k.getAttribute('aria-checked'));
            item.setAttribute('aria-checked', states.every((s) => s === 'true') ? 'true' : states.some((s) => s !== 'false') ? 'mixed' : 'false');
        });
    }

    function commitChecks() {
        const ids = items().filter((el) => el.getAttribute('aria-checked') === 'true').map((el) => el.dataset.id);
        component.value = ids;
        root.dispatchEvent(new CustomEvent('change', { bubbles: true, detail: { value: ids } }));
    }

    function applyValue(value) {
        if (o.mode === 'select') {
            items().forEach((el) => el.setAttribute('aria-selected', String(el.dataset.id === String(value ?? ''))));
        } else if (o.mode === 'check') {
            const set = new Set((Array.isArray(value) ? value : []).map(String));
            const current = items().filter((el) => el.getAttribute('aria-checked') === 'true').map((el) => el.dataset.id);
            if (current.length === set.size && current.every((id) => set.has(id))) return;
            // Leaves and lazy (unloaded) parents take the value; loaded parents follow their children.
            items().forEach((el) => {
                if (!childrenOf(el).length) el.setAttribute('aria-checked', String(set.has(el.dataset.id) || (parentOf(el)?.getAttribute('aria-checked') === 'true' && set.has(parentOf(el).dataset.id))));
            });
            syncChecks();
        }
    }

    /* ---------- Events ---------- */

    tree.addEventListener('click', (event) => {
        const item = event.target.closest('[role=treeitem]');
        if (!item || !tree.contains(item)) return;
        focus(item);
        if (event.target.closest('.ui-tree-twisty') && isParent(item)) {
            setOpen(item, !isOpen(item));

            return;
        }
        if (item.hasAttribute('data-lazy') && !isOpen(item) && o.mode === 'none') {
            setOpen(item, true);

            return;
        }
        select(item);
    });

    tree.addEventListener('keydown', (event) => {
        const item = event.target.closest('[role=treeitem]');
        if (!item || event.altKey || event.ctrlKey || event.metaKey) return;
        const rows = visible();
        const at = rows.indexOf(item);
        let handled = true;
        switch (event.key) {
            case 'ArrowDown':
                focus(rows[at + 1]);
                break;
            case 'ArrowUp':
                focus(rows[at - 1]);
                break;
            case 'Home':
                focus(rows[0]);
                break;
            case 'End':
                focus(rows[rows.length - 1]);
                break;
            case 'ArrowRight':
                if (isParent(item) && !isOpen(item)) setOpen(item, true);
                else if (isParent(item) && item.hasAttribute('data-lazy')) load(item);
                else if (isOpen(item)) focus(childrenOf(item)[0]);
                break;
            case 'ArrowLeft':
                if (isOpen(item)) setOpen(item, false);
                else focus(parentOf(item));
                break;
            case 'Enter':
                if (o.mode === 'check' && isParent(item)) setOpen(item, !isOpen(item));
                else select(item);
                break;
            case ' ':
                if (o.mode === 'none') {
                    if (isParent(item)) setOpen(item, !isOpen(item));
                } else select(item);
                break;
            case '*':
                (parentOf(item) ? childrenOf(parentOf(item)) : [...tree.children]).forEach((el) => setOpen(el, true));
                break;
            default:
                handled = false;
                if (event.key.length === 1 && /\S/.test(event.key)) {
                    handled = true;
                    clearTimeout(bufferTimer);
                    buffer += event.key.toLocaleLowerCase();
                    bufferTimer = setTimeout(() => {
                        buffer = '';
                    }, 500);
                    const same = buffer.length > 1 && buffer.split('').every((c) => c === buffer[0]);
                    const prefix = same ? buffer[0] : buffer;
                    const ordered = [...rows.slice(at + 1), ...rows.slice(0, at + 1)];
                    const start = same || buffer.length === 1 ? ordered : [item, ...ordered];
                    focus(start.find((el) => labelOf(el).toLocaleLowerCase().startsWith(prefix)));
                }
        }
        if (handled) {
            event.preventDefault();
            event.stopPropagation();
        }
    });

    component.$watch('value', (value) => applyValue(value));
    root._uiTree = {
        adopt(next) {
            component = next;
            component.$watch('value', (value) => applyValue(value));
            applyValue(component.value);
        },
    };
    if (o.mode === 'check') syncChecks();
    if (!tree.querySelector('[role=treeitem][tabindex="0"]')) items()[0]?.setAttribute('tabindex', '0');
}
