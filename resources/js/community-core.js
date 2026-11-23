/*
 * The community components' behaviour, loaded lazily by community.js: optimistic likes,
 * reactions and follows with rollback, the reaction picker (placed below its button,
 * arrow keys) and poll voting. Each action is one Livewire call on the closest
 * component; a false return or a failed request puts the state back and shows a toast.
 */
const UNITS = ['', 'k', 'M', 'B', 'T'];
const full = new Intl.NumberFormat('en');

/** The same compact format as TallUiKit\Support\Format::compact: 950, 1.2k, 12k, 3.4M. */
export function compact(value) {
    const abs = Math.abs(value);
    const sign = value < 0 ? '-' : '';
    if (abs < 1000) return sign + String(Math.round(abs));
    let tier = Math.min(Math.floor(Math.log10(abs) / 3), UNITS.length - 1);
    let scaled = Math.round((abs / 1000 ** tier) * 10) / 10;
    if (scaled >= 1000 && tier < UNITS.length - 1) {
        tier += 1;
        scaled = Math.round((abs / 1000 ** tier) * 10) / 10;
    }

    return sign + String(scaled).replace(/\.0$/, '') + UNITS[tier];
}

const toast = (title, variant = 'danger') => window.dispatchEvent(new CustomEvent('toast', { detail: { variant, title } }));
const emit = (el, name, detail) => el.dispatchEvent(new CustomEvent(name, { bubbles: true, detail }));

/** Call `action` on the closest Livewire component; roll back on false or a failure. */
function call(el, action, args, rollback, message) {
    if (!action) return Promise.resolve(null);
    const host = el.closest('[wire\\:id]');
    const wire = host && window.Livewire?.find(host.getAttribute('wire:id'));
    if (!wire) return Promise.resolve(null);
    const fail = () => {
        rollback();
        toast(message);
    };

    return Promise.resolve(wire.call(action, ...args))
        .then((result) => {
            if (result === false) fail();

            return result;
        })
        .catch(fail);
}

/* ---------- Likes ---------- */

function setLike(button, on, count) {
    button.setAttribute('aria-pressed', on ? 'true' : 'false');
    button.dataset.count = String(count);
    const shown = button.querySelector('[data-post-like-count]');
    const spoken = button.querySelector('[data-post-like-spoken]');
    if (shown) shown.textContent = compact(count);
    if (spoken) spoken.textContent = `${full.format(count)} ${count === 1 ? 'like' : 'likes'}`;
}

function like(button) {
    // A post, or a comment of x-ui.comment-thread: both carry data-key and data-like-action.
    const post = button.closest('[data-comment], [data-post]');
    const was = button.getAttribute('aria-pressed') === 'true';
    const count = Number(button.dataset.count) || 0;
    setLike(button, !was, Math.max(0, count + (was ? -1 : 1)));
    emit(button, 'ui-like', { key: post?.dataset.key, on: !was });
    call(button, post?.dataset.likeAction, [post?.dataset.key, !was], () => setLike(button, was, count), 'Your like was not saved. Try again.');
}

/* ---------- Reactions ---------- */

function setReaction(button, on, count) {
    button.setAttribute('aria-pressed', on ? 'true' : 'false');
    button.dataset.count = String(count);
    button.hidden = count < 1;
    const shown = button.querySelector('[data-reaction-count]');
    if (shown) shown.textContent = compact(count);
}

function react(button) {
    const group = button.closest('[data-reactions]');
    const was = button.getAttribute('aria-pressed') === 'true';
    const count = Number(button.dataset.count) || 0;
    const reaction = button.dataset.reaction;
    const next = Math.max(0, count + (was ? -1 : 1));
    setReaction(button, !was, next);
    if (next < 1) group.querySelector('[data-reaction-trigger]')?.focus();
    emit(button, 'ui-react', { subject: group.dataset.subject ?? null, reaction, on: !was });
    call(button, group.dataset.action, [group.dataset.subject ?? null, reaction], () => setReaction(button, was, count), 'Your reaction was not saved. Try again.');
}

function pick(option) {
    const group = option.closest('[data-reactions]');
    const picker = option.closest('[data-reaction-picker]');
    const reaction = option.dataset.reactionPick;
    picker?.hidePopover?.();
    group.querySelector('[data-reaction-trigger]')?.focus();
    const existing = [...group.querySelectorAll('[data-reaction]')].find((el) => el.dataset.reaction === reaction);
    if (existing) {
        if (existing.getAttribute('aria-pressed') !== 'true' || existing.hidden) react(existing);

        return;
    }
    emit(group, 'ui-react', { subject: group.dataset.subject ?? null, reaction, on: true });
    call(group, group.dataset.action, [group.dataset.subject ?? null, reaction], () => {}, 'Your reaction was not saved. Try again.');
}

const open = new Map();

function place(picker, trigger) {
    const r = trigger.getBoundingClientRect();
    const width = picker.offsetWidth;
    picker.style.position = 'fixed';
    picker.style.inset = 'auto';
    picker.style.margin = '0';
    picker.style.top = `${r.bottom + 6}px`;
    picker.style.left = `${Math.max(8, Math.min(r.left, window.innerWidth - width - 8))}px`;
}

export function toggle(picker, state) {
    const group = picker.closest('[data-reactions]');
    const trigger = group?.querySelector('[data-reaction-trigger]');
    if (!trigger) return;
    trigger.setAttribute('aria-expanded', state === 'open' ? 'true' : 'false');

    if (state === 'open') {
        place(picker, trigger);
        // Below, never flipped: scroll the page so the whole picker shows.
        const overflow = picker.getBoundingClientRect().bottom - (window.innerHeight - 8);
        if (overflow > 0) {
            window.scrollBy({ top: overflow, behavior: 'instant' });
            place(picker, trigger);
        }
        const follow = () => place(picker, trigger);
        window.addEventListener('scroll', follow, { passive: true, capture: true });
        window.addEventListener('resize', follow, { passive: true });
        open.set(picker, follow);
        (picker.querySelector('[data-reaction-pick][tabindex="0"]') ?? picker.querySelector('[data-reaction-pick]'))?.focus();
    } else {
        const follow = open.get(picker);
        if (follow) {
            window.removeEventListener('scroll', follow, { capture: true });
            window.removeEventListener('resize', follow);
            open.delete(picker);
        }
        if (document.activeElement === document.body || picker.contains(document.activeElement)) trigger.focus();
    }
}

export function pickerKey(event) {
    const options = [...event.target.closest('[data-reaction-picker]').querySelectorAll('[data-reaction-pick]')];
    const from = options.indexOf(event.target.closest('[data-reaction-pick]'));
    if (from < 0) return;
    const to = {
        ArrowRight: (from + 1) % options.length,
        ArrowDown: (from + 1) % options.length,
        ArrowLeft: (from - 1 + options.length) % options.length,
        ArrowUp: (from - 1 + options.length) % options.length,
        Home: 0,
        End: options.length - 1,
    }[event.key];
    if (to === undefined) {
        if (event.key === 'Tab') event.target.closest('[data-reaction-picker]').hidePopover?.();

        return;
    }
    event.preventDefault();
    options.forEach((el, i) => { el.tabIndex = i === to ? 0 : -1; });
    options[to].focus();
}

/* ---------- Follow ---------- */

function setFollow(button, on) {
    button.setAttribute('aria-pressed', on ? 'true' : 'false');
}

function follow(button) {
    const host = button.closest('[data-follow-action]');
    const was = button.getAttribute('aria-pressed') === 'true';
    setFollow(button, !was);
    emit(button, 'ui-follow', { key: button.dataset.key, on: !was });
    call(button, host?.dataset.followAction, [button.dataset.key, !was], () => setFollow(button, was), was ? 'You are still following. Try again.' : 'The follow was not saved. Try again.');
}

/* ---------- Share ---------- */

function share(button) {
    const url = button.dataset.url;
    if (!url) return;
    if (navigator.share) {
        navigator.share({ url, title: button.dataset.title || document.title }).catch(() => {});

        return;
    }
    Promise.resolve(navigator.clipboard?.writeText(url))
        .then(() => toast('Link copied.', 'success'))
        .catch(() => toast('The link could not be copied.'));
}

/* ---------- Poll ---------- */

export function vote(form) {
    const poll = form.closest('[data-poll]');
    const error = poll.querySelector('[data-poll-error]');
    const choices = [...form.querySelectorAll('[data-poll-choice]:checked')].map((input) => input.value);
    if (!choices.length) {
        error.textContent = 'Choose an option.';
        form.querySelector('[data-poll-choice]')?.focus();

        return;
    }
    error.textContent = '';
    const submit = form.querySelector('[data-poll-submit]');
    if (submit?.getAttribute('aria-busy') === 'true') return;
    submit?.setAttribute('aria-busy', 'true');
    emit(form, 'ui-vote', { key: poll.dataset.key, choices });
    const id = poll.id;
    call(form, poll.dataset.action, [poll.dataset.key, choices], () => {}, 'Your vote was not saved. Try again.').finally(() => {
        submit?.removeAttribute('aria-busy');
        // The form is replaced by the results: focus follows once the morph lands (the kit's
        // focus keeper may have parked focus on a heading meanwhile; never take it from a control).
        let tries = 0;
        const focusResults = () => {
            const results = document.getElementById(id)?.querySelector('[data-poll-results]');
            const active = document.activeElement;
            const parked = !active || active === document.body || active.getAttribute('tabindex') === '-1' || form.contains(active);
            if (results && parked) results.focus();
            else if (!results && tries++ < 120) requestAnimationFrame(focusResults);
        };
        requestAnimationFrame(focusResults);
    });
}

export function click(target) {
    if (target.matches('[data-post-like]')) like(target);
    else if (target.matches('[data-reaction]')) react(target);
    else if (target.matches('[data-reaction-pick]')) pick(target);
    else if (target.matches('[data-follow]')) follow(target);
    else if (target.matches('[data-post-share]')) share(target);
    else if (target.matches('[data-poll-submit]')) vote(target.closest('[data-poll-form]'));
}
