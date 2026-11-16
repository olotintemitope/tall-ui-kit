/*
 * Chat (docs/components/community.md): x-ui.chat, x-ui.chat.composer, x-ui.chat.inbox and
 * x-ui.comment-thread.
 *
 * One controller per chat, none per message. The chat's scrolling, new-message pill,
 * announcements and image dialog live in chat-core.js, fetched with import() when the
 * first chat starts (until then the server-rendered list shows as usual). The composer,
 * the thread and the inbox keys are small and stay here.
 */
let core = null;
const loadCore = () => (core ??= import('./chat-core'));

/** An idempotency key: one per draft, kept until the server accepts it. */
export function draftKey() {
    if (window.crypto?.randomUUID) return window.crypto.randomUUID();
    const bytes = new Uint8Array(16);
    window.crypto.getRandomValues(bytes);

    return [...bytes].map((b) => b.toString(16).padStart(2, '0')).join('');
}

const number = new Intl.NumberFormat('en');

document.addEventListener('alpine:init', () => {
    const Alpine = window.Alpine;

    Alpine.data('uiChat', (options = {}) => ({
        unread: 0,
        loadingOlder: false,
        announcement: '',
        viewer: { src: '', alt: '' },

        init() {
            loadCore().then((m) => {
                if (this.$root.isConnected) this._chat = m.mountChat(this, options);
            });
        },

        loadOlder() {
            this._chat?.loadOlder();
        },

        jump() {
            this._chat?.jump();
        },

        destroy() {
            this._chat?.destroy();
        },
    }));

    Alpine.data('uiComposer', (options = {}) => ({
        body: '',
        sending: false,
        failed: false,
        hasFiles: false,
        key: draftKey(),

        get counter() {
            return `${number.format(this.body.length)} / ${number.format(options.maxlength ?? 0)}`;
        },

        get nearLimit() {
            return !!options.maxlength && this.body.length >= options.maxlength * 0.9;
        },

        get canSend() {
            if (options.disabled || this.sending) return false;
            if (options.maxlength && this.body.length > options.maxlength) return false;

            return this.body.trim() !== '' || this.hasFiles;
        },

        init() {
            const files = this.$refs.files;
            files?.addEventListener('change', () => {
                this.hasFiles = files.files.length > 0;
            });
            if (this.$root.querySelector('[autofocus]')) this.$nextTick(() => this.$refs.input?.focus());
        },

        keydown(event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                event.stopPropagation();
                this.$dispatch('ui-composer-cancel', { params: this.params() });

                return;
            }
            if (event.key !== 'Enter' || event.isComposing || event.keyCode === 229) return;
            const modifier = event.metaKey || event.ctrlKey;
            const sends = options.enterSends === false ? modifier : !event.shiftKey && !event.altKey && !modifier;
            if (!sends) return;
            event.preventDefault();
            this.send();
        },

        grow() {
            const el = this.$refs.input;
            if (!el) return;
            const style = getComputedStyle(el);
            const line = parseFloat(style.lineHeight) || 20;
            const edges = parseFloat(style.borderTopWidth) + parseFloat(style.borderBottomWidth);
            const max = line * 6 + parseFloat(style.paddingTop) + parseFloat(style.paddingBottom) + edges;
            el.style.height = 'auto';
            const wanted = el.scrollHeight + edges;
            el.style.height = `${Math.min(wanted, max)}px`;
            el.style.overflowY = wanted > max ? 'auto' : 'hidden';
        },

        params() {
            if (!options.replySlot) return options.params ?? [];

            return [this.$root.closest('[data-reply-slot]')?.dataset.replySlot ?? null];
        },

        send() {
            if (!this.canSend) return;
            const body = this.body.trim();
            const key = this.key;
            const params = this.params();

            if (!options.action || !this.$wire) {
                this.$dispatch('ui-chat-send', { body, key, params });
                this.done(body, key);

                return;
            }

            this.sending = true;
            this.failed = false;
            Promise.resolve(this.$wire.call(options.action, ...params, body, key))
                .then((result) => {
                    if (result === false) this.failed = true;
                    else this.done(body, key);
                })
                .catch(() => {
                    this.failed = true;
                })
                .finally(() => {
                    this.sending = false;
                });
        },

        done(body, key) {
            this.body = '';
            this.key = draftKey();
            this.failed = false;
            this.hasFiles = false;
            if (this.$refs.files) this.$refs.files.value = '';
            this.$nextTick(() => this.grow());
            const params = this.params();
            this.$dispatch('ui-composer-sent', { body, key, params });
            if (this.$refs.input?.isConnected && !options.replySlot) this.$refs.input.focus({ preventScroll: true });
        },
    }));

    // x-ui.comment-thread: which comment has the reply box open, and which threads are collapsed.
    Alpine.data('uiThread', () => ({
        replyTo: null,
        collapsed: {},

        init() {
            // Methods run from a comment's button, where $el is that button: keep the thread.
            this._root = this.$el;
            const close = (event) => {
                const parent = event.detail?.params?.[0];
                if (parent === null || parent === undefined) return;
                this.cancel(String(parent));
            };
            this.$el.addEventListener('ui-composer-sent', close);
            this.$el.addEventListener('ui-composer-cancel', close);
        },

        slots() {
            return [...this._root.querySelectorAll('[data-reply-slot]')];
        },

        reply(key) {
            const wasOpen = this.replyTo === key;
            this.slots().forEach((slot) => slot.replaceChildren());
            this.replyTo = wasOpen ? null : key;
            const slot = this.slots().find((el) => el.dataset.replySlot === key);
            const template = this._root.querySelector('template[data-reply-template]');
            if (wasOpen || !slot || !template) return;
            // One reply box for the thread: copy it under this comment with ids of its own.
            const box = template.content.firstElementChild.cloneNode(true);
            const suffix = `-${key.replace(/[^\w-]/g, '')}`;
            box.querySelectorAll('[id]').forEach((el) => { el.id += suffix; });
            box.querySelectorAll('label[for]').forEach((el) => { el.htmlFor += suffix; });
            box.querySelectorAll('[aria-describedby]').forEach((el) => {
                el.setAttribute('aria-describedby', el.getAttribute('aria-describedby').trim().split(/\s+/).map((id) => id + suffix).join(' '));
            });
            const label = box.querySelector('label');
            if (label) label.textContent = `Reply to ${slot.dataset.replyName || 'comment'}`;
            slot.appendChild(box);
        },

        cancel(key) {
            this.replyTo = null;
            this.slots().forEach((slot) => slot.replaceChildren());
            this.$nextTick(() => {
                const button = [...this._root.querySelectorAll('[data-reply-to]')].find((el) => el.dataset.replyTo === key);
                button?.focus({ preventScroll: true });
            });
        },

        toggle(key) {
            this.collapsed = { ...this.collapsed, [key]: !this.collapsed[key] };
        },
    }));
});

/* x-ui.chat.inbox: one Tab stop, arrows move between rows (roving tabindex). */
window.uiInboxKey = (event) => {
    if (!['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) return;
    const rows = [...event.currentTarget.querySelectorAll('[data-inbox-row]')];
    const from = rows.indexOf(event.target.closest?.('[data-inbox-row]'));
    if (from < 0) return;
    event.preventDefault();
    const to = { ArrowDown: Math.min(from + 1, rows.length - 1), ArrowUp: Math.max(from - 1, 0), Home: 0, End: rows.length - 1 }[event.key];
    rows.forEach((row, i) => { row.tabIndex = i === to ? 0 : -1; });
    rows[to].focus();
};
