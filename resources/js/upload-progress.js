/*
 * x-ui.upload-progress.list (docs/components/workflow.md, "Upload progress"): follows
 * Livewire's upload events for one wire:model property. Livewire fires them on the file
 * input and they bubble (livewire.laravel.com/docs/4.x/uploads, "Loading and progress
 * indicators"): livewire-upload-start, -progress (detail.progress), -finish, -error,
 * -cancel. One Alpine component per list; the rows are an x-for.
 */

export function formatBytes(n) {
    if (!Number.isFinite(n)) return '';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let i = 0;
    let v = n;
    while (v >= 1000 && i < units.length - 1) {
        v /= 1000;
        i++;
    }

    return i === 0 ? `${Math.round(v)} B` : `${Number(v.toFixed(1))} ${units[i]}`;
}

/* The wire:model property of an input, with or without modifiers. */
function modelOf(el) {
    if (!(el instanceof Element)) return null;
    const attr = [...el.attributes].find((a) => a.name === 'wire:model' || a.name.startsWith('wire:model.'));

    return attr?.value ?? null;
}

let seq = 0;

document.addEventListener('alpine:init', () => {
    window.Alpine.data('uiUploadProgress', ({ model, multiple = false, removeAction = null } = {}) => ({
        files: [],
        pending: [],
        status: '',
        handlers: [],

        init() {
            const on = (type, fn) => {
                const handler = (event) => modelOf(event.target) === model && fn(event);
                window.addEventListener(type, handler, true);
                this.handlers.push([type, handler]);
            };
            // The chosen files (name, size, the File for Retry), before Livewire starts.
            on('change', (event) => {
                const chosen = [...(event.target.files ?? [])];
                if (!chosen.length) return;
                this.pending = chosen;
            });
            // x-ui.file sends the files it accepted (after its own checks, or before a chunked upload).
            on('ui-upload-files', (event) => {
                this.pending = [...(event.detail?.files ?? [])];
            });
            on('livewire-upload-start', () => {
                const batch = this.pending.map((file) => ({ id: ++seq, name: file.name, size: file.size, progress: 0, status: 'uploading', file }));
                this.files = multiple ? [...this.files.filter((f) => f.status === 'done'), ...batch] : batch;
                this.say(`Uploading ${batch.length === 1 ? batch[0].name : `${batch.length} files`}.`);
            });
            on('livewire-upload-progress', (event) => {
                const p = Math.max(0, Math.min(100, Number(event.detail?.progress ?? 0)));
                this.files.forEach((f) => {
                    if (f.status === 'uploading') f.progress = p;
                });
            });
            on('livewire-upload-finish', () => this.settle('done', (names) => `${names} uploaded.`));
            on('livewire-upload-error', () => this.settle('failed', (names) => `Upload failed: ${names}.`));
            on('livewire-upload-cancel', () => {
                this.files = this.files.filter((f) => f.status !== 'uploading');
                this.say('Upload cancelled.');
            });
        },

        destroy() {
            this.handlers.forEach(([type, handler]) => window.removeEventListener(type, handler, true));
        },

        settle(status, message) {
            const batch = this.files.filter((f) => f.status === 'uploading');
            batch.forEach((f) => {
                f.status = status;
                if (status === 'done') f.progress = 100;
            });
            this.say(message(batch.map((f) => f.name).join(', ')));
        },

        say(text) {
            this.status = '';
            this.$nextTick(() => {
                this.status = text;
            });
        },

        get overall() {
            if (!this.files.length) return 0;

            return Math.round(this.files.reduce((sum, f) => sum + (f.status === 'done' ? 100 : f.progress), 0) / this.files.length);
        },

        get overallText() {
            const n = this.files.length;
            const busy = this.files.some((f) => f.status === 'uploading');

            return busy ? `Uploading ${n} files, ${this.overall}%` : `${this.files.filter((f) => f.status === 'done').length} of ${n} files uploaded`;
        },

        word(file) {
            return { uploading: `Uploading, ${file.progress}%`, done: 'Uploaded', failed: 'Failed' }[file.status] ?? '';
        },

        type(file) {
            const m = /\.([a-z0-9]{1,8})$/i.exec(file.name ?? '');
            const ext = m?.[1].toLowerCase();

            return ext === 'jpeg' ? 'JPG' : ext && ext.length <= 4 ? ext.toUpperCase() : 'FILE';
        },

        meta(file) {
            return `${formatBytes(file.size)} · ${this.word(file)}`;
        },

        /* The bound input, when it uploads in chunks (x-ui.file chunked): Cancel and Retry go to it. */
        chunkedInput() {
            return [...document.querySelectorAll('input[type=file][data-chunked]')].find((el) => modelOf(el) === model) ?? null;
        },

        cancel() {
            const chunked = this.chunkedInput();
            if (chunked) chunked.dispatchEvent(new CustomEvent('ui-chunk-cancel'));
            else this.$wire.cancelUpload(model);
        },

        retry() {
            const failed = this.files.filter((f) => f.status === 'failed');
            if (!failed.length) return;
            const chunked = this.chunkedInput();
            if (chunked) {
                chunked.dispatchEvent(new CustomEvent('ui-chunk-retry'));

                return;
            }
            failed.forEach((f) => {
                f.status = 'uploading';
                f.progress = 0;
            });
            this.retrying = true;
            this.say(`Uploading ${failed.map((f) => f.name).join(', ')} again.`);
            // $wire.upload reports through callbacks, not the input's events.
            const finish = () => this.settle('done', (names) => `${names} uploaded.`);
            const error = () => this.settle('failed', (names) => `Upload failed: ${names}.`);
            const progress = (event) => failed.forEach((f) => {
                f.progress = Math.max(0, Math.min(100, Number(event?.detail?.progress ?? 0)));
            });
            const cancel = () => {
                this.files = this.files.filter((f) => f.status !== 'uploading');
            };
            if (multiple) this.$wire.uploadMultiple(model, failed.map((f) => f.file), finish, error, progress, cancel);
            else this.$wire.upload(model, failed[0].file, finish, error, progress, cancel);
        },

        remove(index) {
            const file = this.files[index];
            if (!file) return;
            const next = this.$root.querySelectorAll('[data-upload-row]')[index + 1] ?? this.$root.querySelectorAll('[data-upload-row]')[index - 1];
            this.files = this.files.filter((_, i) => i !== index);
            if (file.status === 'done') {
                if (!multiple) this.$wire.set(model, null);
                else if (removeAction) this.$wire.call(removeAction, index);
            }
            this.say(`${file.name} removed.`);
            // Focus: the next row's first action, else the previous, else the file input.
            this.$nextTick(() => {
                const target = next?.isConnected ? next.querySelector('button:not([style*="display: none"])') : document.querySelector(`[wire\\:model="${model}"], [wire\\:model\\.live="${model}"]`);
                (window.uiFocus?.focusAfter ?? ((el) => el?.focus()))(target);
            });
        },
    }));
});
