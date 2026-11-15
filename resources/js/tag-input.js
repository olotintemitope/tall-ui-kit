/*
 * x-ui.tag-input (docs/components/workflow.md, "Tag input"). One Alpine component per
 * field; the chips are an x-for inside it (no component per tag). `tags` is the
 * x-modelable value, so wire:model / x-model on the component bind the array.
 */

const norm = (text, caseSensitive) => (caseSensitive ? text : text.toLocaleLowerCase());

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiTagInput', (o = {}) => ({
        tags: [],
        query: '',
        open: false,
        active: -1,
        status: '',
        problem: false,
        regex: null,

        init() {
            // Handlers can run on a chip that is already gone (its x-for row removed), where
            // $root and $refs no longer resolve: keep the elements in a closure.
            const root = this.$root;
            const input = this.$refs.input;
            this.el = () => ({ root, input });
            // The initial value sits in data-value, not in x-data, so a re-render with a new
            // value never re-initialises the component (focus and open lists survive).
            if (!this.tags.length) {
                try {
                    const initial = JSON.parse(this.$root.dataset.value ?? '[]');
                    if (Array.isArray(initial)) this.tags = initial.map(String);
                } catch {
                    // Malformed: start empty.
                }
            }
            this.$root.querySelector('[data-tag-ssr]')?.remove();
            if (o.pattern) {
                try {
                    this.regex = new RegExp(`^(?:${o.pattern})$`, 'u');
                } catch {
                    this.regex = null;
                }
            }
            // wire:model may hand over null or a non-array before the first sync.
            this.$watch('tags', (value) => {
                if (!Array.isArray(value)) this.tags = value == null ? [] : [String(value)];
            });
        },

        get results() {
            const chosen = new Set(this.tags.map((t) => norm(t, o.caseSensitive)));
            const q = norm(this.query.trim(), false);

            return (o.suggestions ?? []).filter((s) => !chosen.has(norm(s.value, o.caseSensitive)) && (q === '' || s.value.toLocaleLowerCase().includes(q)));
        },

        get activeId() {
            return this.open && this.active >= 0 && this.results[this.active] ? this.optionId(this.active) : null;
        },

        optionId(index) {
            return `${o.id}-option-${index}`;
        },

        say(text, problem = false) {
            this.problem = problem;
            this.status = '';
            // A new node for the same text, so a repeated message is announced again.
            this.$nextTick(() => {
                this.status = text;
            });
        },

        /* Why `text` cannot be added, or null. */
        reason(text, pending = []) {
            const all = [...this.tags, ...pending];
            if (text === '') return 'empty';
            if (o.max !== null && all.length >= o.max) return `You can add up to ${o.max} tags.`;
            if (o.maxLength && [...text].length > o.maxLength) return `Tags can be up to ${o.maxLength} characters.`;
            if (this.regex && !this.regex.test(text)) return o.patternMessage ?? `"${text}" is not in the expected format.`;
            if (all.some((t) => norm(t, o.caseSensitive) === norm(text, o.caseSensitive))) return `"${text}" is already added.`;
            if (!o.allowCustom && !(o.suggestions ?? []).some((s) => norm(s.value, o.caseSensitive) === norm(text, o.caseSensitive))) return `Choose "${text}" from the list.`;

            return null;
        },

        add(raw) {
            const text = String(raw ?? '').trim();
            const why = this.reason(text);
            if (why === 'empty') return false;
            if (why) {
                this.say(why, true);

                return false;
            }
            this.tags = [...this.tags, text];
            this.query = '';
            this.active = -1;
            this.say(`Added ${text}.`);
            this.changed();

            return true;
        },

        /* Adds several at once (paste); one message for the lot. */
        addMany(parts) {
            const added = [];
            const skipped = [];
            for (const part of parts.map((p) => p.trim()).filter(Boolean)) {
                const why = this.reason(part, added);
                if (why) skipped.push(why);
                else added.push(part);
            }
            if (added.length) {
                this.tags = [...this.tags, ...added];
                this.changed();
            }
            const words = [];
            if (added.length) words.push(`Added ${added.length} ${added.length === 1 ? 'tag' : 'tags'}.`);
            if (skipped.length) words.push(`${skipped.length} skipped: ${skipped[0]}`);
            this.say(words.join(' '), skipped.length > 0);
        },

        remove(index, fromButton = false) {
            const tag = this.tags[index];
            if (tag === undefined) return;
            this.tags = this.tags.filter((_, i) => i !== index);
            this.say(`Removed ${tag}.`);
            this.changed();
            if (!fromButton) return;
            // Focus: the previous tag, else the next, else the input. Never <body>.
            this.$nextTick(() => this.focusTag(Math.min(index - 1 >= 0 ? index - 1 : 0, this.tags.length - 1)));
        },

        /* Fires input and change on the input so x-ui.form's dirty flag and listeners see it. */
        changed() {
            this.el().input.dispatchEvent(new Event('change', { bubbles: true }));
        },

        buttons() {
            return [...this.el().root.querySelectorAll('[data-tag-list] [data-tag-remove]')];
        },

        focusTag(index) {
            const list = this.buttons();
            if (index < 0 || index >= list.length) {
                this.el().input.focus();

                return;
            }
            list[index].focus();
        },

        typed() {
            this.problem = false;
            this.status = '';
            const sep = o.separator;
            if (sep && this.query.includes(sep)) {
                const parts = this.query.split(sep);
                const rest = parts.pop();
                parts.forEach((p) => this.add(p));
                this.query = rest;
            }
            if (o.suggestions?.length) {
                this.open = true;
                this.active = this.results.length ? 0 : -1;
            }
        },

        show() {
            if (o.suggestions?.length) this.open = true;
        },

        close() {
            this.open = false;
            this.active = -1;
        },

        pick(index) {
            const option = this.results[index];
            if (!option) return;
            this.add(option.value);
            this.el().input.focus();
            if (!this.results.length) this.close();
            else this.active = Math.min(index, this.results.length - 1);
        },

        move(delta) {
            if (!o.suggestions?.length) return;
            if (!this.open) {
                this.open = true;
                this.active = delta > 0 ? 0 : this.results.length - 1;

                return;
            }
            const n = this.results.length;
            if (!n) return;
            this.active = (this.active + delta + n) % n;
            this.$nextTick(() => document.getElementById(this.optionId(this.active))?.scrollIntoView({ block: 'nearest' }));
        },

        key(event) {
            const input = this.$refs.input;
            const atStart = input.selectionStart === 0 && input.selectionEnd === 0;
            switch (event.key) {
                case 'Enter':
                    if (this.open && this.active >= 0 && this.results[this.active]) {
                        event.preventDefault();
                        this.pick(this.active);
                    } else if (this.query.trim() !== '') {
                        event.preventDefault();
                        this.add(this.query);
                    }
                    break;
                case 'ArrowDown':
                    event.preventDefault();
                    this.move(1);
                    break;
                case 'ArrowUp':
                    event.preventDefault();
                    this.move(-1);
                    break;
                case 'Escape':
                    if (this.open) {
                        event.preventDefault();
                        event.stopPropagation();
                        this.close();
                    }
                    break;
                case 'Backspace':
                    if (this.query === '' && this.tags.length) {
                        event.preventDefault();
                        this.remove(this.tags.length - 1);
                    }
                    break;
                case 'ArrowLeft':
                    if (atStart && this.tags.length) {
                        event.preventDefault();
                        this.close();
                        this.focusTag(this.tags.length - 1);
                    }
                    break;
                default:
            }
        },

        tagKey(event, index) {
            const last = this.tags.length - 1;
            const go = {
                ArrowLeft: Math.max(0, index - 1),
                ArrowRight: index + 1,
                Home: 0,
                End: last,
            };
            if (event.key in go) {
                event.preventDefault();
                this.focusTag(go[event.key] > last ? -1 : go[event.key]);
            } else if (event.key === 'Backspace' || event.key === 'Delete') {
                event.preventDefault();
                this.remove(index, true);
            } else if (event.key === 'Escape') {
                event.preventDefault();
                this.el().input.focus();
            }
        },

        paste(event) {
            const text = event.clipboardData?.getData('text') ?? '';
            const sep = o.separator || ',';
            if (!text.includes(sep) && !/[\n\r]/.test(text)) return;
            event.preventDefault();
            this.addMany(text.split(new RegExp(`[\\n\\r${sep.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}]+`)));
        },

        blur(event) {
            if (this.$root.contains(event.relatedTarget)) return;
            this.close();
            if (o.addOnBlur && this.query.trim() !== '') this.add(this.query);
        },
    }));
});
