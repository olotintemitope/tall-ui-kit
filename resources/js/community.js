/*
 * Community components (docs/components/community.md): x-ui.post likes and share,
 * x-ui.reactions (toggles and the picker), x-ui.follow-button and x-ui.poll.
 *
 * No Alpine per post, reaction row, card or poll: these few listeners on the document
 * hand each event to community-core.js, fetched with import() as soon as a page has one
 * of them (or the pointer or focus reaches one first). The picker opens without it
 * (the Popover API); the core places it below its button.
 */
let core = null;
let mod = null;
const load = () => (core ??= import('./community-core').then((m) => { mod = m; return m; }));
const run = (fn) => (mod ? fn(mod) : load().then(fn));

const HOSTS = '[data-post], [data-reactions], [data-poll], [data-follow]';
const ACTIONS = '[data-post-like], [data-reaction], [data-reaction-pick], [data-follow], [data-post-share], [data-poll-submit]';

const scan = () => {
    if (document.querySelector(HOSTS)) load();
};

document.addEventListener('alpine:initialized', scan);
document.addEventListener('livewire:navigated', scan);
['pointerover', 'focusin'].forEach((type) => document.addEventListener(type, (event) => {
    if (!core && event.target.closest?.(HOSTS)) load();
}, { passive: true }));

document.addEventListener('click', (event) => {
    const target = event.target.closest?.(ACTIONS);
    if (target) run((m) => m.click(target));
});

// A poll is a plain form whose Vote is a button (so the kit's form focus rules stay out of
// it); Enter in it never submits natively, even before the core arrives.
document.addEventListener('submit', (event) => {
    const form = event.target.closest?.('[data-poll-form]');
    if (!form) return;
    event.preventDefault();
    run((m) => m.vote(form));
}, true);

// `toggle` does not bubble: listen in the capture phase.
document.addEventListener('toggle', (event) => {
    if (event.target.matches?.('[data-reaction-picker]')) run((m) => m.toggle(event.target, event.newState));
}, true);

document.addEventListener('keydown', (event) => {
    if (mod && event.target.closest?.('[data-reaction-picker]')) mod.pickerKey(event);
});
