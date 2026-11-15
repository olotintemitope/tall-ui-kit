/*
 * x-ui.avatar-group tooltip (docs/components/workflow.md, "Avatar group"). One delegated
 * listener set for the whole page, no Alpine per group: hover or focus on a group shows
 * its name list above it (below when there is no room above; it is a hint, so it may
 * sit either side), `position: fixed` so a scroll box never clips it. Escape hides it
 * (WCAG 1.4.13) until the pointer or focus leaves and comes back.
 */
const GAP = 6;
let current = null;

function place(group) {
    const tip = group.querySelector('[data-avatar-tip]');
    if (!tip) return;
    group.setAttribute('data-tip-open', '');
    const box = group.getBoundingClientRect();
    const size = tip.getBoundingClientRect();
    const left = Math.max(8, Math.min(window.innerWidth - size.width - 8, box.left + box.width / 2 - size.width / 2));
    const above = box.top - size.height - GAP;
    tip.style.left = `${left}px`;
    tip.style.top = `${above >= 8 ? above : box.bottom + GAP}px`;
}

function show(group) {
    if (current && current !== group) hide(current);
    current = group;
    place(group);
}

function hide(group) {
    group?.removeAttribute('data-tip-open');
    if (current === group) current = null;
}

const groupOf = (node) => (node instanceof Element ? node.closest('[data-avatar-group]') : null);

document.addEventListener('mouseover', (event) => {
    const group = groupOf(event.target);
    if (group && !group.contains(event.relatedTarget)) show(group);
});

document.addEventListener('mouseout', (event) => {
    const group = groupOf(event.target);
    if (group && !group.contains(event.relatedTarget) && document.activeElement !== group) hide(group);
});

document.addEventListener('focusin', (event) => {
    const group = groupOf(event.target);
    if (group) show(group);
});

document.addEventListener('focusout', (event) => {
    const group = groupOf(event.target);
    if (group) hide(group);
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && current) hide(current);
});

window.addEventListener('scroll', () => current && place(current), { passive: true, capture: true });
