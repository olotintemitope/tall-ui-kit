/*
 * Chat (docs/components/community.md): x-ui.chat, x-ui.chat.composer, x-ui.chat.inbox and
 * x-ui.comment-thread.
 *
 * One controller per chat, none per message. The chat's scrolling, new-message pill,
 * announcements and image dialog live in chat-core.js, fetched with import() when the
 * first chat starts (until then the server-rendered list shows as usual). The composer,
 * the thread and the inbox keys are small and stay here.
 */
import { mentionAt, matchPeople, insertMention } from './mention-core';

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
        // `draft`: text to start with (a draft the app kept), so it is not lost on a reload.
        body: typeof options.draft === 'string' ? options.draft : '',
        sending: false,
        failed: false,
        hasFiles: false,
        key: draftKey(),
        // @mentions: the mention being typed (start of the "@", the query) and the
        // highlighted suggestion. Null when no suggestion list is open.
        mention: null,
        active: 0,
        typedAt: 0,
        // Everyone who can be mentioned (grows with mentionSearch results) and the
        // people picked in this draft; their keys go to `action` with the text.
        people: [...(options.mentions ?? [])],
        picked: [],
        searchTimer: null,
        searched: new Set(),
        // The one exception to "popups open below" (docs/rules.md, rule 6): a composer
        // docked at the foot of a clipped, full-height chat opens its list above.
        mentionAbove: false,

        get suggestions() {
            return this.mention ? matchPeople(this.people, this.mention.query) : [];
        },

        get mentionsEnabled() {
            return !!(options.mentions?.length || options.mentionSearch);
        },

        /** Keys of the picked people whose "@Name" is still in the text. */
        get mentionKeys() {
            return this.picked.filter((person) => this.body.includes(`@${person.name}`)).map((person) => person.key);
        },

        get mentionOpen() {
            return this.mention !== null && this.suggestions.length > 0;
        },

        get activeId() {
            return this.mentionOpen ? `${this.$root.id}-mention-${this.active}` : null;
        },

        get mentionStatus() {
            if (!this.mentionOpen) return '';
            const count = this.suggestions.length;

            return `${count} ${count === 1 ? 'person' : 'people'}`;
        },

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
            if (this.body !== '') this.$nextTick(() => this.grow());
        },

        /** Called on every input and click: opens, narrows or closes the mention list. */
        detect() {
            if (!this.mentionsEnabled) return;
            const input = this.$refs.input;
            const found = input ? mentionAt(this.body, input.selectionStart) : null;
            if (found?.query !== this.mention?.query || found?.start !== this.mention?.start) this.active = 0;
            this.mention = found;
            if (found && found.query !== '') this.search(found.query);
        },

        /**
         * mentionSearch: ask the server for people beyond the loaded list, 300ms after
         * typing stops, once per query. Results ({ key, name, avatar }) join the list.
         */
        search(query) {
            if (!options.mentionSearch || !this.$wire) return;
            clearTimeout(this.searchTimer);
            this.searchTimer = setTimeout(async () => {
                if (this.searched.has(query)) return;
                this.searched.add(query);
                const found = await this.$wire.call(options.mentionSearch, query).catch(() => []);
                const known = new Set(this.people.map((person) => String(person.key)));
                for (const person of Array.isArray(found) ? found : []) {
                    const key = String(person?.key ?? '');
                    if (key === '' || !person?.name || known.has(key)) continue;
                    known.add(key);
                    this.people.push({ key, name: String(person.name), avatar: person.avatar ?? null });
                }
            }, 300);
        },

        /**
         * Where the mention list goes. Below, revealed by scrolling, like every popup;
         * above only when the composer sits inside a container that clips it (overflow
         * not visible) and there is not room below inside that container, which is a
         * chat docked at the bottom of the screen: scrolling there would push the whole
         * chat up and still not show the list.
         */
        placeMentions(list) {
            requestAnimationFrame(() => {
                if (!list || list.getClientRects().length === 0) return;
                const form = this.$root;
                let clip = null;
                for (let node = form.parentElement; node && node !== document.body; node = node.parentElement) {
                    const style = getComputedStyle(node);
                    if (style.overflowY !== 'visible' || style.overflow !== 'visible') { clip = node; break; }
                }
                const limit = Math.min(window.innerHeight, clip ? clip.getBoundingClientRect().bottom : Infinity);
                const room = limit - form.getBoundingClientRect().bottom;
                const above = clip !== null && room < list.offsetHeight + 8;
                this.mentionAbove = above;
                if (!above) this.$nextTick(() => this.$revealBelow(list, this.$refs.input));
            });
        },

        /** Inserts text at the caret (emoji pickers and other tools). */
        insert(text) {
            const input = this.$refs.input;
            const caret = input ? input.selectionStart ?? this.body.length : this.body.length;
            const end = input ? input.selectionEnd ?? caret : caret;
            this.body = this.body.slice(0, caret) + text + this.body.slice(end);
            const next = caret + String(text).length;
            if (!input) return;
            // Write the value and caret now, not a tick later: keys typed in between
            // would land before the caret moves.
            input.value = this.body;
            input.focus({ preventScroll: true });
            input.setSelectionRange(next, next);
            this.grow();
            this.detect();
        },

        /** The "@" tool: starts a mention at the caret, with a space before it when needed. */
        startMention() {
            const input = this.$refs.input;
            const caret = input ? input.selectionStart ?? this.body.length : this.body.length;
            const before = this.body.slice(0, caret);
            this.insert(before === '' || /\s$/.test(before) ? '@' : ' @');
        },

        moveMention(step) {
            const count = this.suggestions.length;
            if (!count) return;
            this.active = (this.active + step + count) % count;
        },

        pickMention(index = this.active) {
            const person = this.suggestions[index];
            const input = this.$refs.input;
            if (!person || !this.mention || !input) return;
            const next = insertMention(this.body, this.mention.start, input.selectionStart, person.name);
            this.body = next.text;
            this.mention = null;
            if (!this.picked.some((p) => p.key === person.key)) this.picked.push({ key: person.key, name: person.name });
            // Value and caret now, not a tick later (see insert()).
            input.value = next.text;
            input.focus({ preventScroll: true });
            input.setSelectionRange(next.caret, next.caret);
            this.grow();
            this.$dispatch('ui-composer-mention', { key: person.key, name: person.name, params: this.params() });
        },

        /** At most one typing call every 3 seconds, and none for an empty box. */
        typing() {
            if (!options.typingAction || !this.$wire || this.body.trim() === '') return;
            const now = Date.now();
            if (now - this.typedAt < 3000) return;
            this.typedAt = now;
            this.$wire.call(options.typingAction, ...this.params());
        },

        keydown(event) {
            if (this.mentionOpen) {
                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    this.moveMention(event.key === 'ArrowDown' ? 1 : -1);

                    return;
                }
                if ((event.key === 'Enter' || event.key === 'Tab') && !event.isComposing && event.keyCode !== 229) {
                    event.preventDefault();
                    this.pickMention();

                    return;
                }
                if (event.key === 'Escape') {
                    // Closes the list only; the draft and the composer stay.
                    event.preventDefault();
                    event.stopPropagation();
                    this.mention = null;

                    return;
                }
            }
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
            const mentions = this.mentionKeys;

            if (!options.action || !this.$wire) {
                this.$dispatch('ui-chat-send', { body, key, params, mentions });
                this.done(body, key);

                return;
            }

            this.sending = true;
            this.failed = false;
            // With mentions on, the picked people's keys follow the text and the key.
            const args = this.mentionsEnabled ? [...params, body, key, mentions] : [...params, body, key];
            Promise.resolve(this.$wire.call(options.action, ...args))
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
            this.mention = null;
            this.picked = [];
            this.key = draftKey();
            this.failed = false;
            this.hasFiles = false;
            if (this.$refs.files) this.$refs.files.value = '';
            // The message took its attachments: clear the upload rows under the box.
            this.$root.querySelectorAll('[data-upload-list]').forEach((el) => {
                const list = window.Alpine.$data(el);
                if (list) list.files = [];
            });
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
