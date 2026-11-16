/*
 * The upgraded x-ui.file (docs/components/forms.md, "File"), loaded lazily by files.js when
 * a file field uses any of the new props. One component per field; rows are built with
 * DOM APIs from a server-rendered <template>, names always set with textContent.
 *
 *  - Per-file checks against `accept`, `maxSize`, `maxFiles` and `maxTotalSize`: a refused
 *    file is listed with its reason and never sent.
 *  - The input's own `change` is caught in the capture phase on the field (before Livewire's
 *    wire:model listener on the input) and sent again with only the accepted files.
 *  - `paste`: files on the clipboard are added while focus is in the field.
 *  - `reorder`: the chosen files are an x-ui.sortable list (drag, Up / Down buttons and
 *    the keyboard); the new order goes to `reorderAction` (indexes in the old order), or
 *    re-orders the input's files in a plain form.
 *  - `chunked`: each file goes to the app's ChunkedUploadController in pieces, resuming
 *    from the server's offset after a network drop; the upload ids are then set on the
 *    wire:model property. Progress is reported with Livewire's own upload events, so
 *    x-ui.upload-progress.list follows it.
 */
import { storageKey } from './storage';

export function bytes(n) {
    if (!Number.isFinite(n) || n < 0) return '';
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let i = 0;
    let v = n;
    while (v >= 1000 && i < units.length - 1) {
        v /= 1000;
        i++;
    }

    return i === 0 ? `${Math.round(v)} B` : `${Number(v.toFixed(1))} ${units[i]}`;
}

function limit(n) {
    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    let i = 0;
    let v = n;
    while (v >= 1024 && i < units.length - 1) {
        v /= 1024;
        i++;
    }

    return `${Number(v.toFixed(1))} ${units[i]}`;
}

export function typeLabel(name, mime = '') {
    const m = /\.([a-z0-9]{1,8})$/i.exec(name ?? '');
    const ext = m?.[1].toLowerCase();
    if (ext === 'jpeg') return 'JPG';
    if (ext && ext.length <= 4) return ext.toUpperCase();
    const sub = /^[a-z]+\/(?:x-)?([a-z0-9]{1,4})$/i.exec(mime ?? '');

    return sub ? sub[1].toUpperCase() : 'FILE';
}

/** Does the file match the input's accept list (".pdf", "image/*", "application/pdf")? */
export function accepts(file, accept) {
    const rules = String(accept ?? '').split(',').map((r) => r.trim().toLowerCase()).filter(Boolean);
    if (!rules.length) return true;
    const name = (file.name ?? '').toLowerCase();
    const type = (file.type ?? '').toLowerCase();

    return rules.some((rule) => {
        if (rule.startsWith('.')) return name.endsWith(rule) || (rule === '.jpg' && name.endsWith('.jpeg')) || (rule === '.jpeg' && name.endsWith('.jpg'));
        if (rule.endsWith('/*')) return type.startsWith(rule.slice(0, -1));

        return type === rule;
    });
}

const plural = (n, one, many) => `${n} ${n === 1 ? one : many}`;
const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

function csrf() {
    return document.querySelector('meta[name="csrf-token"]')?.content ?? window.livewireScriptConfig?.csrf ?? '';
}

function modelOf(el) {
    const attr = [...(el?.attributes ?? [])].find((a) => a.name === 'wire:model' || a.name.startsWith('wire:model.'));

    return attr?.value ?? null;
}

let seq = 0;

export function mount(c, o) {
    const root = c.$root;
    const input = root.querySelector('[data-ui="file"]');
    const list = root.querySelector('[data-file-list]');
    const refused = root.querySelector('[data-file-rejected]');
    const template = root.querySelector('template[data-file-row]');
    const model = modelOf(input);
    const multiple = input.multiple;
    let accepted = []; // { key, file, name, size, uploadId? }
    let rejected = []; // { key, name, size, reason }
    let resending = false;
    let chunkJob = null;

    function say(text) {
        c.status = '';
        requestAnimationFrame(() => { c.status = text; });
    }

    function emit(type, detail = {}) {
        input.dispatchEvent(new CustomEvent(type, { bubbles: true, detail: { property: model, ...detail } }));
    }

    /* ---------- Rows ---------- */

    function row(item, invalid) {
        const li = template.content.firstElementChild.cloneNode(true);
        li.dataset.key = item.key;
        li.dataset.label = item.name;
        li.querySelector('[data-type]').textContent = typeLabel(item.name, item.type);
        li.querySelector('[data-name]').textContent = item.name;
        li.querySelector('[data-meta]').textContent = bytes(item.size);
        const error = li.querySelector('[data-error]');
        const remove = li.querySelector('[data-file-remove]');
        remove.setAttribute('aria-label', `Remove ${item.name}`);
        li.querySelectorAll('[data-sortable-handle]').forEach((b) => b.setAttribute('aria-label', `Reorder ${item.name}`));
        li.querySelectorAll('[data-sortable-move="up"]').forEach((b) => b.setAttribute('aria-label', `Move ${item.name} up`));
        li.querySelectorAll('[data-sortable-move="down"]').forEach((b) => b.setAttribute('aria-label', `Move ${item.name} down`));
        const img = li.querySelector('[data-thumb]');
        if (img) {
            if (item.url) {
                img.src = item.url;
                img.alt = item.name;
            } else img.remove();
        }
        if (invalid) {
            li.setAttribute('data-invalid', '');
            li.removeAttribute('data-sortable-item');
            li.querySelectorAll('[data-sortable-handle], [data-sortable-move]').forEach((b) => b.remove());
            error.textContent = item.reason;
            error.hidden = false;
            error.id = `${input.id}-err-${item.key}`;
            remove.setAttribute('aria-describedby', error.id);
            remove.dataset.fileDismiss = '';
        }

        return li;
    }

    function render() {
        list.replaceChildren(...accepted.map((item) => row(item, false)));
        list.hidden = accepted.length === 0 || !!o.slot;
        if (refused) {
            refused.replaceChildren(...rejected.map((item) => row(item, true)));
            refused.hidden = rejected.length === 0;
        }
        c.files = accepted.map(({ key, name, size }) => ({ key, name, size }));
        c.rejected = rejected.map(({ key, name, reason }) => ({ key, name, reason }));
    }

    /* ---------- Checks ---------- */

    function check(files) {
        const valid = [];
        const bad = [];
        let count = multiple ? accepted.length : 0;
        let total = multiple ? accepted.reduce((sum, f) => sum + f.size, 0) : 0;
        files.forEach((file) => {
            let reason = null;
            if (o.validateType && !accepts(file, input.accept)) reason = 'This type of file is not accepted.';
            else if (o.maxSize && file.size > o.maxSize) reason = `Larger than ${limit(o.maxSize)}.`;
            else if (o.maxFiles && count + 1 > o.maxFiles) reason = `Only ${plural(o.maxFiles, 'file', 'files')} can be added.`;
            else if (o.maxTotal && total + file.size > o.maxTotal) reason = `Goes over the ${limit(o.maxTotal)} total.`;
            const item = { key: `f${++seq}`, file, name: file.name, size: file.size, type: file.type, url: o.images && file.type.startsWith('image/') ? URL.createObjectURL(file) : null };
            if (reason) bad.push({ ...item, reason });
            else {
                valid.push(item);
                count++;
                total += file.size;
            }
        });

        return { valid, bad };
    }

    function ingest(files) {
        if (!files.length || input.disabled) return;
        if (!multiple) files = files.slice(0, 1);
        const { valid, bad } = check(files);
        if (!multiple && valid.length) {
            accepted.forEach((f) => f.url && URL.revokeObjectURL(f.url));
            accepted = [];
        }
        accepted.push(...valid);
        rejected = [...rejected.filter((r) => multiple), ...bad];
        render();
        const parts = [];
        if (valid.length) parts.push(`${plural(valid.length, 'file', 'files')} added.`);
        if (bad.length) parts.push(`${plural(bad.length, 'file was', 'files were')} not added: ${bad.map((b) => `${b.name}, ${b.reason}`).join(' ')}`);
        say(parts.join(' '));
        if (valid.length) send(valid);
        else if (!o.chunked) resetInput();
    }

    function setFiles(files) {
        const transfer = new DataTransfer();
        files.forEach((f) => transfer.items.add(f));
        input.files = transfer.files;
    }

    function resetInput() {
        resending = true;
        setFiles(model || o.chunked ? [] : accepted.map((a) => a.file));
        resending = false;
    }

    function send(batch) {
        if (o.chunked) {
            chunked(batch);

            return;
        }
        // Livewire appends each multiple selection on the server, so it gets the new batch;
        // a plain form posts the input, so it holds every accepted file.
        setFiles(model ? batch.map((b) => b.file) : accepted.map((a) => a.file));
        resending = true;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        resending = false;
    }

    /* ---------- Remove, dismiss and reorder ---------- */

    function focusAfter(container, index) {
        requestAnimationFrame(() => {
            const buttons = [...root.querySelectorAll('[data-file-remove]')];
            const inList = [...container.querySelectorAll('[data-file-remove]')];
            (inList[Math.min(index, inList.length - 1)] ?? buttons[0] ?? input).focus();
        });
    }

    function remove(index) {
        const item = accepted[index];
        if (!item) return;
        accepted.splice(index, 1);
        if (item.url) URL.revokeObjectURL(item.url);
        render();
        say(`${item.name} removed.`);
        focusAfter(list, index);
        const wire = c.$wire;
        if (o.chunked) {
            if (item.uploadId) {
                fetch(`${o.chunked}/${encodeURIComponent(item.uploadId)}`, { method: 'DELETE', headers: headers(), credentials: 'same-origin' }).catch(() => {});
                if (model && wire) wire.set(model, multiple ? accepted.map((a) => a.uploadId).filter(Boolean) : null);
            }

            return;
        }
        if (model && wire) {
            if (o.removeAction) wire.call(o.removeAction, index);
            else if (!multiple) wire.set(model, null);
            else {
                const value = wire.get(model);
                const tmp = Array.isArray(value) ? String(value[index] ?? '').replace(/^livewire-file:/, '') : '';
                if (tmp) wire.removeUpload(model, tmp);
            }

            return;
        }
        resetInput();
    }

    function dismiss(index) {
        const item = rejected[index];
        if (!item) return;
        rejected.splice(index, 1);
        render();
        say(`${item.name} dismissed.`);
        focusAfter(refused, index);
    }

    const onSort = (event) => {
        if (!list.contains(event.target)) return;
        const order = event.detail?.order ?? [];
        const before = accepted.map((a) => a.key);
        accepted = order.map((key) => accepted.find((a) => a.key === key)).filter(Boolean);
        c.files = accepted.map(({ key, name, size }) => ({ key, name, size }));
        const indexes = order.map((key) => before.indexOf(key));
        root.dispatchEvent(new CustomEvent('ui-file-reorder', { bubbles: true, detail: { order: indexes, names: accepted.map((a) => a.name) } }));
        if (o.chunked) {
            if (model && c.$wire) c.$wire.set(model, accepted.map((a) => a.uploadId).filter(Boolean));
        } else if (model && c.$wire) {
            if (o.reorderAction) c.$wire.call(o.reorderAction, indexes);
        } else resetInput();
    };

    /* ---------- Chunked uploads ---------- */

    function headers(extra = {}) {
        return { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf(), ...extra };
    }

    async function request(method, url, body = null, extra = {}) {
        const response = await fetch(url, { method, body, headers: headers(extra), credentials: 'same-origin' });
        const data = await response.json().catch(() => ({}));

        return { status: response.status, ok: response.ok, data };
    }

    const resumeKey = (file) => storageKey(`upload:${o.chunked}:${file.name}:${file.size}:${file.lastModified}`);
    const remember = (file, id) => { try { id ? sessionStorage.setItem(resumeKey(file), id) : sessionStorage.removeItem(resumeKey(file)); } catch { /* storage unavailable: no resume across reloads */ } };
    const recall = (file) => { try { return sessionStorage.getItem(resumeKey(file)); } catch { return null; } };

    /* Waits for the network: the `online` event, or the backoff delay, whichever is first. */
    function backoff(attempt) {
        const delay = Math.min(30000, 1000 * 2 ** attempt);

        return new Promise((resolve) => {
            const done = () => { window.removeEventListener('online', done); clearTimeout(timer); resolve(); };
            const timer = setTimeout(done, delay);
            window.addEventListener('online', done);
        });
    }

    async function uploadOne(item, job, report) {
        const file = item.file;
        let id = recall(file);
        let offset = 0;
        let size = o.chunkSize;
        if (id) {
            const res = await request('GET', `${o.chunked}/${encodeURIComponent(id)}`).catch(() => null);
            if (res?.ok && !res.data.done) offset = Number(res.data.offset) || 0;
            else if (res?.ok && res.data.done) {
                item.uploadId = id;
                remember(file, null);

                return id;
            }
            else id = null;
        }
        if (!id) {
            const res = await request('POST', o.chunked, JSON.stringify({ name: file.name, size: file.size, type: file.type }), { 'Content-Type': 'application/json' });
            if (!res.ok) throw new Error(res.data?.message ?? 'The upload could not start.');
            id = res.data.id;
            size = Number(res.data.chunk_size) || size;
            remember(file, id);
        }
        item.uploadId = id;
        let attempt = 0;
        while (offset < file.size) {
            if (job.cancelled) throw new Error('cancelled');
            const piece = file.slice(offset, Math.min(file.size, offset + size));
            let res = null;
            try {
                res = await request('PATCH', `${o.chunked}/${encodeURIComponent(id)}`, piece, { 'Content-Type': 'application/octet-stream', 'Upload-Offset': String(offset) });
            } catch {
                res = null; // network drop
            }
            if (res?.ok) {
                offset = Number(res.data.offset);
                attempt = 0;
                report(item, offset);
                continue;
            }
            if (res?.status === 409 && Number.isFinite(Number(res.data.offset))) {
                offset = Number(res.data.offset); // the server has more (or less) than we thought: carry on from there
                continue;
            }
            if (res && res.status >= 400 && res.status < 500 && res.status !== 408 && res.status !== 429) {
                throw new Error(res.data?.message ?? 'The upload was refused.');
            }
            if (++attempt > 8) throw new Error('The connection dropped. Retry to resume.');
            say(`Connection lost. Resuming ${file.name} when it is back.`);
            await backoff(attempt);
            const status = await request('GET', `${o.chunked}/${encodeURIComponent(id)}`).catch(() => null);
            if (status?.ok) offset = Number(status.data.offset) || offset;
        }
        remember(file, null);

        return id;
    }

    async function chunked(batch) {
        if (chunkJob && !chunkJob.failed) {
            chunkJob.queue.push(...batch);

            return;
        }
        const job = { queue: [...(chunkJob?.failed ? chunkJob.queue : []), ...batch], cancelled: false };
        chunkJob = job;
        emit('ui-upload-files', { files: job.queue.map((item) => item.file) });
        input.setAttribute('data-uploading', '');
        resetInput();
        const totalBytes = () => job.queue.reduce((sum, item) => sum + item.size, 0) || 1;
        const sent = new Map();
        const report = (item, offset) => {
            sent.set(item.key, offset);
            const done = [...sent.values()].reduce((a, b) => a + b, 0);
            const progress = Math.min(100, Math.floor((done * 100) / totalBytes()));
            c.progress = progress;
            emit('livewire-upload-progress', { progress });
        };
        emit('livewire-upload-start');
        c.progress = 0;
        try {
            for (let i = 0; i < job.queue.length; i++) {
                const item = job.queue[i];
                if (item.done) {
                    report(item, item.size);
                    continue;
                }
                await uploadOne(item, job, report);
                item.done = true;
                report(item, item.size);
            }
            const ids = accepted.map((a) => a.uploadId).filter(Boolean);
            if (model && c.$wire) await c.$wire.set(model, multiple ? ids : (ids[0] ?? null));
            emit('livewire-upload-finish');
            say(`${plural(job.queue.length, 'file', 'files')} uploaded.`);
            chunkJob = null;
        } catch (error) {
            if (job.cancelled) {
                emit('livewire-upload-cancel');
                say('Upload cancelled.');
                accepted = accepted.filter((a) => !job.queue.includes(a) || a.done);
                render();
                chunkJob = null;
            } else {
                emit('livewire-upload-error', { message: error.message });
                say(`Upload failed: ${error.message}`);
                chunkJob = { ...job, failed: true };
            }
        } finally {
            c.progress = null;
            input.removeAttribute('data-uploading');
        }
    }

    function retryChunks() {
        if (chunkJob?.failed) chunked([]);
    }

    function cancelChunks() {
        if (chunkJob && !chunkJob.failed) chunkJob.cancelled = true;
        else if (chunkJob?.failed) {
            chunkJob.queue.forEach((item) => {
                if (item.uploadId) fetch(`${o.chunked}/${encodeURIComponent(item.uploadId)}`, { method: 'DELETE', headers: headers(), credentials: 'same-origin' }).catch(() => {});
                remember(item.file, null);
            });
            accepted = accepted.filter((a) => !chunkJob.queue.includes(a));
            chunkJob = null;
            render();
            emit('livewire-upload-cancel');
        }
    }

    /* ---------- Listeners ---------- */

    // Capture on the field runs before Livewire's listener on the input itself.
    const onChange = (event) => {
        if (event.target !== input || resending) return;
        event.stopPropagation();
        ingest([...(input.files ?? [])]);
    };

    const onPaste = (event) => {
        if (!o.paste || !root.contains(document.activeElement)) return;
        const files = [...(event.clipboardData?.files ?? [])];
        if (!files.length) return;
        event.preventDefault();
        ingest(files);
    };

    const onClick = (event) => {
        const button = event.target.closest?.('[data-file-remove]');
        if (!button || !root.contains(button)) return;
        const li = button.closest('li');
        const container = li?.parentElement;
        const index = [...(container?.children ?? [])].indexOf(li);
        if (container === refused) dismiss(index);
        else remove(index);
    };

    // The input covers the area: a drop lands on it and fires change by itself.
    const onDrop = () => {
        c.dragging = false;
    };

    root.addEventListener('change', onChange, true);
    root.addEventListener('click', onClick);
    document.addEventListener('paste', onPaste);
    root.addEventListener('drop', onDrop, true);
    root.addEventListener('ui-sort', onSort);
    input.addEventListener('ui-chunk-retry', retryChunks);
    input.addEventListener('ui-chunk-cancel', cancelChunks);

    render();

    return {
        bytes,
        remove,
        dismiss,
        destroy() {
            root.removeEventListener('change', onChange, true);
            root.removeEventListener('click', onClick);
            document.removeEventListener('paste', onPaste);
            root.removeEventListener('drop', onDrop, true);
            root.removeEventListener('ui-sort', onSort);
            input.removeEventListener('ui-chunk-retry', retryChunks);
            input.removeEventListener('ui-chunk-cancel', cancelChunks);
            accepted.forEach((f) => f.url && URL.revokeObjectURL(f.url));
            if (chunkJob) chunkJob.cancelled = true;
        },
    };
}
