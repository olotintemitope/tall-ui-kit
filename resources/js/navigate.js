/*
 * wire:navigate in the shells (docs/components/navigation.md "Page changes").
 *
 * The sidebar's links use wire:navigate and x-ui.sidebar is persisted (x-persist), so a
 * page change swaps <body> but keeps the very same sidebar element: its nav scroll,
 * collapse, open sections and Alpine state survive, and only <main> cross-fades
 * (wire:transition.navigate). Three things a full page load did for free are done here,
 * inside Livewire's swap (the `onSwap` callback of `livewire:navigating`, which runs
 * before the view transition takes its "new" snapshot, so nothing flashes):
 *
 * 1. <html> state. Livewire copies the new page's <html> attributes over the current
 *    ones and drops the rest, which would lose what the head scripts and the stores set
 *    on it: data-sidebar (collapsed rail), data-focus (focus mode), data-privacy,
 *    data-input (no ring after a pointer action) and data-motion-preview. Every data-*
 *    attribute the new page does not set itself is put back.
 * 2. The current item. The persisted sidebar still marks the old page, so each
 *    [data-nav-match] link is re-marked from the URL with SidebarTones::isActive's rule
 *    ('/' and `data-nav-exact` hrefs match exactly, others also match their
 *    sub-pages): aria-current, data-active and the tone classes from data-nav-tones.
 *    Groups follow their children and open when one becomes current.
 * 3. Another area. A page from another shell (the login page after the session ended,
 *    an area switch through history) is loaded in full, so its own sidebar, storage
 *    keys and head scripts apply.
 * 4. Scroll on back and forward. Livewire keeps each page's HTML for history visits and
 *    restores the scroll of <body>, which never scrolls in a standards-mode document
 *    (the window does). The window's offset is written on <html> as the page is left,
 *    so it is part of that saved HTML, and put back when history brings the page back.
 */

const AREA = '[data-sidebar-area]';

function area() {
    return document.querySelector(AREA)?.getAttribute('data-sidebar-area') ?? null;
}

const SCROLL = 'data-ui-scroll';

function htmlState() {
    return [...document.documentElement.attributes]
        .filter((attribute) => attribute.name.startsWith('data-') && attribute.name !== SCROLL)
        .map((attribute) => [attribute.name, attribute.value]);
}

function restoreHtml(saved) {
    const html = document.documentElement;
    for (const [name, value] of saved) {
        if (!html.hasAttribute(name)) html.setAttribute(name, value);
    }
}

function normalise(path) {
    const clean = path.replace(/\/+$/, '');

    return clean === '' ? '/' : clean;
}

function classes(list) {
    return String(list ?? '').split(/\s+/).filter(Boolean);
}

function swap(el, off, on, active) {
    if (!el) return;
    el.classList.remove(...classes(active ? off : on));
    el.classList.add(...classes(active ? on : off));
}

function isCurrent(href, path, exact) {
    if (href === '/' || exact.includes(href)) return path === href;

    return path === href || path.startsWith(`${href}/`);
}

function markItem(link, active, tones) {
    if (link.hasAttribute('aria-current') === active) return;
    swap(link, tones.item, tones.itemActive, active);
    swap(link.querySelector(':scope > [data-nav-icon]'), tones.icon, tones.iconActive, active);
    link.toggleAttribute('data-active', active);
    if (active) link.setAttribute('aria-current', 'page');
    else link.removeAttribute('aria-current');
}

function markGroup(group, tones) {
    const active = Boolean(group.querySelector('a[data-nav-item][aria-current="page"]'));
    if (group.hasAttribute('data-active') === active) return;
    const button = group.querySelector(':scope > [data-nav-item]');
    // A group parent keeps its idle classes and adds the active ones (x-ui.sidebar.group).
    classes(tones.groupActive).forEach((name) => button?.classList.toggle(name, active));
    swap(button?.querySelector(':scope > [data-nav-icon]'), tones.icon, tones.iconActive, active);
    group.toggleAttribute('data-active', active);
    if (active && window.Alpine?.$data) {
        try {
            window.Alpine.$data(group).open = true;
        } catch {
            // Not started yet (a deferred drawer copy): it renders open from its markup next time.
        }
    }
}

export function syncSidebars(root = document) {
    const path = normalise(window.location.pathname);
    root.querySelectorAll('[data-nav-sync]').forEach((sidebar) => {
        let tones;
        let exact;
        try {
            tones = JSON.parse(sidebar.getAttribute('data-nav-tones') || '{}');
            exact = JSON.parse(sidebar.getAttribute('data-nav-exact') || '[]').map(normalise);
        } catch {
            return;
        }
        sidebar.querySelectorAll('a[data-nav-match][href]').forEach((link) => {
            let href;
            try {
                href = new URL(link.getAttribute('href'), window.location.href);
            } catch {
                return;
            }
            const active = href.origin === window.location.origin && isCurrent(normalise(href.pathname), path, exact);
            markItem(link, active, tones);
        });
        sidebar.querySelectorAll('[data-nav-group][data-nav-match]').forEach((group) => markGroup(group, tones));
    });
}

let fromArea = null;
let fromHistory = false;
let restoreTo = null;

// Fired before every visit: `history` is true for back and forward.
document.addEventListener('livewire:navigate', (event) => {
    fromHistory = Boolean(event.detail?.history);
});

document.addEventListener('livewire:navigating', (event) => {
    // Written before Livewire saves this page's HTML for the back button.
    document.documentElement.setAttribute(SCROLL, `${Math.round(window.scrollX)},${Math.round(window.scrollY)}`);
    const saved = htmlState();
    fromArea = area();
    const history = fromHistory;
    fromHistory = false;
    event.detail?.onSwap?.(() => {
        const offset = document.documentElement.getAttribute(SCROLL);
        document.documentElement.removeAttribute(SCROLL);
        restoreHtml(saved);
        if (fromArea && area() !== fromArea) {
            window.location.reload();

            return;
        }
        syncSidebars();
        restoreTo = history && offset ? offset.split(',').map(Number) : null;
    });
});

document.addEventListener('livewire:navigated', () => {
    if (!restoreTo) return;
    const [x, y] = restoreTo;
    restoreTo = null;
    window.scrollTo({ left: x, top: y, behavior: 'instant' });
});

window.uiNavigate = { syncSidebars };
