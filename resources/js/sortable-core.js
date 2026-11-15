/*
 * x-ui.sortable behaviour (docs/components/workflow.md, "Sortable"), loaded lazily by
 * widgets.js the first time a page has a sortable list. Native pointer events, no
 * library: one set of delegated listeners serves every list, so rows carry no Alpine.
 *
 * Pointer: the row follows the pointer with a transform; the rows it passes slide by
 * the lifted row's height (transform, --motion-sm). On release the row is moved in the
 * DOM and the transforms are cleared in the same frame, so nothing jumps.
 * Keyboard: Space/Enter picks up, Up/Down/Home/End move (the DOM node moves; focus is
 * put back on its handle), Space/Enter drops, Escape cancels, focus leaving drops.
 * Either way, a changed order is sent once: $wire.call(action, keys) on the closest
 * Livewire component, plus a bubbling `ui-sort` event.
 */

const EDGE = 48;
let drag = null;
let keyboard = null;

const items = (list) => [...list.querySelectorAll(':scope > [data-sortable-item]')];
const labelOf = (item) => item.dataset.label || item.textContent.trim();
const reduced = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches || document.documentElement.dataset.motionPreview === 'reduced';

function announce(list, text) {
    const live = list.closest('[data-sortable-root]')?.querySelector('[data-sortable-live]');
    if (!live) return;
    live.textContent = '';
    requestAnimationFrame(() => {
        live.textContent = text;
    });
}

function commit(list, item, from, to) {
    if (from === to) return;
    const order = items(list).map((el) => el.dataset.key);
    list.dispatchEvent(new CustomEvent('ui-sort', { bubbles: true, detail: { order, key: item.dataset.key, from, to } }));
    const action = list.dataset.sortableAction;
    const host = list.closest('[wire\\:id]');
    if (!action || !host || !window.Livewire) return;
    const wire = window.Livewire.find(host.getAttribute('wire:id'));
    if (!wire) return;
    Promise.resolve(typeof wire.call === 'function' ? wire.call(action, order) : wire[action](order)).catch(() => {
        announce(list, 'The new order could not be saved.');
    });
}

function moveTo(list, item, index) {
    const rest = items(list).filter((el) => el !== item);
    const before = rest[index] ?? null;
    if (before) list.insertBefore(item, before);
    else list.appendChild(item);
}

/* ---------- Pointer ---------- */

function pointerDown(event) {
    const handle = event.target.closest?.('[data-sortable-handle]');
    if (!handle || handle.disabled || event.button > 0 || keyboard) return;
    const list = handle.closest('[data-sortable]');
    const item = handle.closest('[data-sortable-item]');
    if (!list || !item || list.hasAttribute('data-sortable-disabled')) return;
    event.preventDefault();
    handle.setPointerCapture?.(event.pointerId);
    const all = items(list);
    const rects = all.map((el) => {
        const r = el.getBoundingClientRect();

        return { top: r.top + window.scrollY, height: r.height };
    });
    const from = all.indexOf(item);
    const gap = rects.length > 1 ? rects[1].top - (rects[0].top + rects[0].height) : 0;
    drag = { list, item, handle, all, rects, from, to: from, pointerId: event.pointerId, startY: event.clientY + window.scrollY, lastY: event.clientY, slot: rects[from].height + gap, frame: 0 };
    list.setAttribute('data-sorting', '');
    item.setAttribute('data-dragging', '');
    all.forEach((el) => el !== item && el.setAttribute('data-sort-shift', ''));
    announce(list, `Picked up ${labelOf(item)}. Position ${from + 1} of ${all.length}.`);
}

function update() {
    if (!drag) return;
    const { item, all, rects, from, slot } = drag;
    const pageY = drag.lastY + window.scrollY;
    const dy = pageY - drag.startY;
    item.style.transform = `translateY(${dy}px)`;
    const center = rects[from].top + dy + rects[from].height / 2;
    let to = 0;
    all.forEach((el, j) => {
        if (j !== from && rects[j].top + rects[j].height / 2 < center) to++;
    });
    drag.to = to;
    all.forEach((el, j) => {
        if (j === from) return;
        let shift = 0;
        if (from < j && j <= to) shift = -slot;
        else if (to <= j && j < from) shift = slot;
        el.style.transform = shift ? `translateY(${shift}px)` : '';
    });
}

function autoScroll() {
    if (!drag) return;
    const y = drag.lastY;
    const speed = y < EDGE ? -(EDGE - y) / 3 : y > window.innerHeight - EDGE ? (y - (window.innerHeight - EDGE)) / 3 : 0;
    if (speed) {
        window.scrollBy(0, speed);
        update();
    }
    drag.frame = requestAnimationFrame(autoScroll);
}

function pointerMove(event) {
    if (!drag || event.pointerId !== drag.pointerId) return;
    event.preventDefault();
    drag.lastY = event.clientY;
    if (!drag.frame) drag.frame = requestAnimationFrame(autoScroll);
    update();
}

function finishPointer(cancel) {
    if (!drag) return;
    const { list, item, handle, all, from, to } = drag;
    cancelAnimationFrame(drag.frame);
    drag = null;
    // Move first, then clear the transforms without a transition: the rows land where they are drawn.
    all.forEach((el) => {
        el.removeAttribute('data-sort-shift');
        el.style.transform = '';
    });
    item.removeAttribute('data-dragging');
    list.removeAttribute('data-sorting');
    const target = cancel ? from : to;
    if (target !== from) moveTo(list, item, target);
    handle.focus({ preventScroll: true });
    if (cancel || target === from) {
        announce(list, `${labelOf(item)} dropped. Position ${from + 1} of ${all.length}, unchanged.`);

        return;
    }
    announce(list, `${labelOf(item)} dropped. Position ${target + 1} of ${all.length}.`);
    commit(list, item, from, target);
}

/* ---------- Keyboard ---------- */

function keyDown(event) {
    if (drag && event.key === 'Escape') {
        event.preventDefault();
        finishPointer(true);

        return;
    }
    const handle = event.target.closest?.('[data-sortable-handle]');
    if (!handle || handle.disabled) return;
    const list = handle.closest('[data-sortable]');
    const item = handle.closest('[data-sortable-item]');
    if (!list || !item || list.hasAttribute('data-sortable-disabled')) return;
    const all = items(list);
    const index = all.indexOf(item);
    const pick = event.key === ' ' || event.key === 'Enter';

    if (!keyboard) {
        if (!pick) return;
        event.preventDefault();
        keyboard = { list, item, handle, from: index, moving: false };
        item.setAttribute('data-grabbed', '');
        handle.setAttribute('aria-pressed', 'true');
        announce(list, `Picked up ${labelOf(item)}. Position ${index + 1} of ${all.length}. Use the arrow keys to move, Space to drop, Escape to cancel.`);

        return;
    }
    if (keyboard.item !== item) return;

    const target = { ArrowUp: index - 1, ArrowDown: index + 1, Home: 0, End: all.length - 1 }[event.key];
    if (target !== undefined) {
        event.preventDefault();
        if (target < 0 || target >= all.length || target === index) {
            announce(list, `${labelOf(item)} is at position ${index + 1} of ${all.length}.`);

            return;
        }
        slide(list, item, handle, target);
        announce(list, `${labelOf(item)} moved to position ${target + 1} of ${all.length}.`);
    } else if (pick) {
        event.preventDefault();
        drop(false);
    } else if (event.key === 'Escape') {
        event.preventDefault();
        event.stopPropagation();
        drop(true);
    } else if (event.key === 'Tab') {
        drop(false);
    }
}

/* Moves the grabbed row in the DOM and slides it from where it was (FLIP, transform only). */
function slide(list, item, handle, target) {
    const before = item.getBoundingClientRect().top;
    keyboard.moving = true;
    moveTo(list, item, target);
    handle.focus({ preventScroll: true });
    keyboard.moving = false;
    item.scrollIntoView({ block: 'nearest' });
    if (reduced()) return;
    const after = item.getBoundingClientRect().top;
    item.animate?.([{ transform: `translateY(${before - after}px)` }, { transform: 'none' }], { duration: 150, easing: 'cubic-bezier(0.2, 0, 0, 1)' });
}

function drop(cancel) {
    if (!keyboard) return;
    const { list, item, handle, from } = keyboard;
    keyboard = null;
    item.removeAttribute('data-grabbed');
    handle.removeAttribute('aria-pressed');
    const total = items(list).length;
    if (cancel) {
        moveTo(list, item, from);
        handle.focus({ preventScroll: true });
        announce(list, `Cancelled. ${labelOf(item)} is back at position ${from + 1} of ${total}.`);

        return;
    }
    const to = items(list).indexOf(item);
    announce(list, `${labelOf(item)} dropped. Position ${to + 1} of ${total}.`);
    commit(list, item, from, to);
}

function focusOut(event) {
    if (!keyboard || keyboard.moving) return;
    if (event.target === keyboard.handle && event.relatedTarget !== keyboard.handle) drop(false);
}

export function start() {
    if (window.uiSortable) return;
    window.uiSortable = { active: () => Boolean(drag || keyboard) };
    document.addEventListener('pointerdown', pointerDown);
    document.addEventListener('pointermove', pointerMove, { passive: false });
    document.addEventListener('pointerup', (e) => drag && e.pointerId === drag.pointerId && finishPointer(false));
    document.addEventListener('pointercancel', (e) => drag && e.pointerId === drag.pointerId && finishPointer(true));
    document.addEventListener('keydown', keyDown);
    document.addEventListener('focusout', focusOut);
}
