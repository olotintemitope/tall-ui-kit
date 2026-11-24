/*
 * x-ui.chat's controller, loaded lazily by chat.js (one per chat, none per message).
 *
 * - Opens at the newest message (measured in a frame, never during Alpine's init).
 * - Sticks to the bottom while the reader is there; your own new message always scrolls
 *   down. Scrolled up, incoming messages count into the "N new messages" button instead.
 * - "Load older messages": one Livewire call; the distance from the bottom is kept, so the
 *   messages put above do not move what the reader is looking at.
 * - Speaks each incoming message once in the polite status region; history, first render
 *   and your own sends are never announced.
 * - Images open the chat's one image dialog.
 */
const reducedMotion = () => window.matchMedia('(prefers-reduced-motion: reduce)').matches
    || document.documentElement.dataset.motionPreview === 'reduced';

function preview(el) {
    const text = el.querySelector('[data-chat-body]')?.textContent.replace(/\s+/g, ' ').trim() ?? '';
    if (text) return text.length > 160 ? `${text.slice(0, 157).trimEnd()}...` : text;

    return el.querySelector('[data-chat-image]') ? 'sent an image' : 'sent an attachment';
}

export function mountChat(component, options) {
    const root = component.$root;
    const scroller = component.$refs.scroller;
    const list = root.querySelector('[data-chat-list]');
    if (!scroller || !list) return null;

    const messages = () => list.querySelectorAll('[data-chat-message]');
    const lastKey = () => {
        const all = messages();

        return all.length ? all[all.length - 1].dataset.key : null;
    };

    let last = lastKey();
    let atBottom = true;
    let programmatic = false;
    let restore = null;
    let settle = 0;

    const distance = () => scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight;

    const toBottom = (smooth) => {
        programmatic = true;
        atBottom = true;
        component.unread = 0;
        scroller.scrollTo({ top: scroller.scrollHeight, behavior: smooth && !reducedMotion() ? 'smooth' : 'auto' });
        clearTimeout(settle);
        settle = setTimeout(() => { programmatic = false; }, smooth ? 450 : 50);
    };

    const onScroll = () => {
        if (programmatic) return;
        atBottom = distance() < 48;
        if (atBottom) component.unread = 0;
    };

    const announce = (incoming) => {
        const latest = incoming[incoming.length - 1];
        const who = latest.dataset.author || 'Someone';
        const text = incoming.length === 1
            ? `${who}: ${preview(latest)}`
            : `${incoming.length} new messages. Latest from ${who}: ${preview(latest)}`;
        component.announcement = '';
        requestAnimationFrame(() => { component.announcement = text; });
    };

    const observer = new MutationObserver(() => {
        if (restore !== null) {
            scroller.scrollTop = scroller.scrollHeight - restore;
            restore = null;
        }

        const key = lastKey();
        if (key === last) return;
        const all = [...messages()];
        const from = all.findIndex((el) => el.dataset.key === last);
        const fresh = from === -1 ? (last === null ? all : []) : all.slice(from + 1);
        last = key;
        if (!fresh.length) return;

        const mine = fresh.some((el) => el.hasAttribute('data-mine'));
        const incoming = fresh.filter((el) => !el.hasAttribute('data-mine'));
        if (mine || atBottom) {
            requestAnimationFrame(() => toBottom(!mine));
        } else {
            component.unread += incoming.length;
        }
        if (incoming.length) announce(incoming);
    });
    observer.observe(list, { childList: true, subtree: true });

    // Images loading, a message growing: stay at the bottom if the reader was there.
    const resize = new ResizeObserver(() => {
        if (atBottom && restore === null) scroller.scrollTop = scroller.scrollHeight;
    });
    resize.observe(list);

    const onClick = (event) => {
        // Reply: one listener for the chat, not a Livewire call per message (response size).
        const reply = event.target.closest?.('[data-chat-reply]');
        if (reply && root.contains(reply)) {
            const key = reply.closest('[data-chat-message]')?.dataset.key;
            if (options.replyAction && component.$wire) component.$wire.call(options.replyAction, key);
            else reply.dispatchEvent(new CustomEvent('ui-chat-reply', { detail: { key }, bubbles: true }));
            root.querySelector('[data-composer-input]:not([disabled])')?.focus({ preventScroll: true });

            return;
        }
        const image = event.target.closest?.('[data-chat-image]');
        if (!image || !root.contains(image)) return;
        component.viewer = { src: image.dataset.src, alt: image.dataset.alt || 'Image' };
        image.dispatchEvent(new CustomEvent('open-dialog', { detail: options.image, bubbles: true }));
    };

    scroller.addEventListener('scroll', onScroll, { passive: true });
    root.addEventListener('click', onClick);
    requestAnimationFrame(() => toBottom(false));

    const focusAfter = () => {
        const input = root.querySelector('[data-composer-input]:not([disabled])');
        (input ?? scroller).focus({ preventScroll: true });
    };

    return {
        jump() {
            toBottom(true);
            focusAfter();
        },

        loadOlder() {
            const button = root.querySelector('[data-chat-older]');
            const hadFocus = button && document.activeElement === button;
            if (!options.olderAction || !component.$wire) {
                root.dispatchEvent(new CustomEvent('ui-chat-older', { bubbles: true }));

                return;
            }
            if (component.loadingOlder) return;
            component.loadingOlder = true;
            restore = scroller.scrollHeight - scroller.scrollTop;
            Promise.resolve(component.$wire.call(options.olderAction)).finally(() => {
                component.loadingOlder = false;
                requestAnimationFrame(() => {
                    if (restore !== null) {
                        scroller.scrollTop = scroller.scrollHeight - restore;
                        restore = null;
                    }
                    // The button goes once there is nothing older: keep focus in the chat.
                    if (hadFocus && !root.querySelector('[data-chat-older]')) scroller.focus({ preventScroll: true });
                });
            });
        },

        destroy() {
            observer.disconnect();
            resize.disconnect();
            scroller.removeEventListener('scroll', onScroll);
            root.removeEventListener('click', onClick);
            clearTimeout(settle);
        },
    };
}
