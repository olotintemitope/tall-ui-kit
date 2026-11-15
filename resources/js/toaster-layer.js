/*
 * Toasts over modals (x-ui.toaster; docs/ui-kit.md "Toasts").
 *
 * Every kit modal opens with showModal(): the dialog goes into the top layer and the
 * rest of the document becomes inert. A toaster left in the layout would then sit
 * under the ::backdrop, could not be clicked, and its live regions (inert) may not
 * be announced. So while any modal <dialog> is open, the toaster's own node moves
 * into the topmost one (appended last, inside its subtree: not inert, painted above
 * its backdrop), follows nested modals, and goes back to a placeholder at its home
 * when the last one closes. It is the same node, moved inside Alpine.mutateDom: the
 * store, timers, x-for rows, transitions and the live regions are untouched.
 *
 * Not popover="manual": a popover is outside the modal's subtree, and the HTML spec
 * makes everything outside the modal inert even when it is painted above it.
 *
 * Livewire: the toaster lives outside every component (layouts/app). Inside a dialog
 * a morph would remove it as an unknown child, so the morph hooks keep it, and if
 * the dialog itself is morphed away the toaster goes home first.
 *
 * Placement inside a modal: position: fixed, so it stays on the viewport corner and
 * never shifts the dialog. It waits for the dialog's open transition (a transformed
 * dialog would be its containing block), then avoids the dialog's footer buttons
 * and Close: the configured corner if clear, else beside the dialog, else the top
 * (full-height sheets and phones).
 */

const GAP = 16;
// Room kept for a toast before one renders, so the stack does not jump on arrival.
const RESERVE = 96;
const controllers = new Set();

function isOpenModal(dialog) {
    return dialog instanceof HTMLDialogElement && dialog.isConnected && dialog.open && dialog.matches(':modal');
}

function overlaps(a, b) {
    return a.left < b.right && a.right > b.left && a.top < b.bottom && a.bottom > b.top;
}

function rects(dialog, selector) {
    return [...dialog.querySelectorAll(selector)]
        .filter((el) => el.closest('dialog') === dialog && el.getClientRects().length > 0)
        .map((el) => el.getBoundingClientRect());
}

/* Waits for the dialog's own open transition, so it is no longer transformed. */
function settled(dialog) {
    const running = dialog.getAnimations().map((animation) => animation.finished.catch(() => null));

    return running.length ? Promise.race([Promise.all(running), new Promise((done) => setTimeout(done, 600))]) : Promise.resolve();
}

function attach(region) {
    const Alpine = window.Alpine;
    const home = document.createComment('toaster home');
    region.before(home);
    let stack = [...document.querySelectorAll('dialog')].filter(isOpenModal);
    let wanted = null;
    let resize = null;

    const move = (parent) => {
        if (parent ? region.parentElement === parent && !region.nextSibling : region.previousSibling === home) {
            return;
        }
        const active = document.activeElement;
        const hadFocus = region.contains(active);
        Alpine.mutateDom(() => (parent ? parent.append(region) : home.after(region)));
        if (hadFocus && active.isConnected) {
            active.focus({ preventScroll: true });
        }
    };

    const place = () => {
        const style = region.style;
        ['top', 'bottom', 'left', 'right', 'translate'].forEach((name) => style.removeProperty(name));
        const dialog = region.parentElement instanceof HTMLDialogElement ? region.parentElement : null;
        region.dataset.toasterIn = dialog ? 'dialog' : 'page';
        delete region.dataset.toasterPlacement;
        if (!dialog) {
            return;
        }

        const width = region.offsetWidth;
        const height = Math.max(region.offsetHeight, RESERVE);
        const vw = document.documentElement.clientWidth;
        const vh = window.innerHeight;
        const now = region.getBoundingClientRect();
        const bottomAnchored = getComputedStyle(region).bottom !== 'auto';
        const footer = rects(dialog, '[data-dialog-footer] button, [data-dialog-footer] a[href]');
        const all = [...footer, ...rects(dialog, '[data-dialog-close]')];
        const box = (left, top) => ({ left, right: left + width, top, bottom: top + height });
        const frame = dialog.getBoundingClientRect();
        const candidates = [
            { name: 'corner', box: box(now.left, bottomAnchored ? now.bottom - height : now.top), avoid: all, style: {} },
        ];
        if (frame.left - 2 * GAP >= width) {
            const left = frame.left - GAP - width;
            candidates.push({ name: 'beside', box: box(left, vh - GAP - height), avoid: all, style: { left: `${left}px`, right: 'auto', bottom: `${GAP}px`, top: 'auto' } });
        } else if (vw - frame.right - 2 * GAP >= width) {
            const left = frame.right + GAP;
            candidates.push({ name: 'beside', box: box(left, vh - GAP - height), avoid: all, style: { left: `${left}px`, right: 'auto', bottom: `${GAP}px`, top: 'auto' } });
        }
        // The top: may cover a full-height sheet's title, never its actions.
        candidates.push({ name: 'top', box: box(now.left, GAP), avoid: footer, style: { left: `${now.left}px`, right: 'auto', top: `${GAP}px`, bottom: 'auto' } });

        const chosen = candidates.find((candidate) => !candidate.avoid.some((rect) => overlaps(candidate.box, rect))) ?? candidates[candidates.length - 1];
        region.dataset.toasterPlacement = chosen.name;
        if (chosen.name !== 'corner') {
            Object.assign(style, chosen.style, { translate: 'none' });
        }
    };

    const watch = (dialog) => {
        resize?.disconnect();
        resize = null;
        if (dialog && window.ResizeObserver) {
            resize = new ResizeObserver(() => place());
            resize.observe(dialog);
        }
    };

    const sync = async () => {
        stack = stack.filter(isOpenModal);
        const target = stack[stack.length - 1] ?? null;
        if (target === wanted && (target ? region.parentElement === target : region.previousSibling === home)) {
            return;
        }
        wanted = target;
        // Leave a dialog that has closed (or is closing) at once; the page is live again.
        if (!isOpenModal(region.parentElement) && region.previousSibling !== home) {
            move(null);
            place();
        }
        if (!target) {
            watch(null);

            return;
        }
        await settled(target);
        if (wanted !== target || !isOpenModal(target)) {
            return;
        }
        move(target);
        place();
        watch(target);
    };

    const observer = new MutationObserver((records) => {
        for (const record of records) {
            if (record.type === 'attributes' && isOpenModal(record.target)) {
                stack = [...stack.filter((dialog) => dialog !== record.target), record.target];
            }
        }
        sync();
    });
    observer.observe(document.documentElement, { subtree: true, childList: true, attributes: true, attributeFilter: ['open'] });

    const onResize = () => place();
    window.addEventListener('resize', onResize);
    // More toasts make the stack taller: check the footer again once they render.
    const stopEffect = Alpine.effect(() => {
        Alpine.store('toasts').visible.length;
        requestAnimationFrame(() => region.parentElement instanceof HTMLDialogElement && place());
    });

    const controller = {
        region,
        goHome() {
            wanted = null;
            if (region.previousSibling !== home) {
                move(null);
                place();
            }
            watch(null);
            queueMicrotask(sync);
        },
        detach() {
            observer.disconnect();
            watch(null);
            window.removeEventListener('resize', onResize);
            Alpine.release?.(stopEffect);
            home.remove();
            controllers.delete(controller);
        },
    };
    controllers.add(controller);
    sync();

    return controller;
}

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiToaster', () => ({
        init() {
            this.layer = attach(this.$el);
        },
        destroy() {
            this.layer?.detach();
        },
    }));
});

document.addEventListener('livewire:init', () => {
    // A morph of the dialog's children would drop the toaster as an unknown node.
    window.Livewire.hook('morph.updating', ({ el, skip }) => {
        for (const controller of controllers) {
            if (el === controller.region) skip();
        }
    });
    window.Livewire.hook('morph.removing', ({ el, skip }) => {
        for (const controller of controllers) {
            if (el === controller.region) {
                skip();
            } else if (el instanceof Element && el.contains(controller.region)) {
                controller.goHome();
            }
        }
    });
});

export { attach as attachToaster };
