/*
 * Docs site behaviour: the theme switch (swaps the theme stylesheet and remembers it)
 * and the client-side search over window.docsIndex (written by `composer docs:build`).
 * Reading never needs this file; search shows only once it runs.
 */
const THEME_KEY = 'tallui_docs_theme';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('docsTheme', () => ({
        theme: document.documentElement.dataset.docsTheme === 'newbanger' ? 'newbanger' : 'neutral',
        init() {
            this.$watch('theme', (value) => {
                const link = document.getElementById('docs-theme');
                link.href = value === 'newbanger' ? link.dataset.newbanger : link.dataset.neutral;
                document.documentElement.dataset.docsTheme = value;
                try { localStorage.setItem(THEME_KEY, value); } catch (e) {}
            });
        },
    }));
});

function score(entry, words) {
    const title = entry.t.toLowerCase();
    const keys = entry.k.toLowerCase();
    let total = 0;
    for (const word of words) {
        if (title === word) total += 100;
        else if (title.startsWith(word)) total += 40;
        else if (title.includes(word)) total += 20;
        else if (keys.includes(word)) total += 5;
        else return 0;
    }
    return total;
}

export function search(index, query, limit = 8) {
    const words = query.toLowerCase().trim().split(/\s+/).filter(Boolean);
    if (!words.length) return [];
    return index
        .map((entry) => ({ entry, score: score(entry, words) }))
        .filter((r) => r.score > 0)
        .sort((a, b) => b.score - a.score || a.entry.t.localeCompare(b.entry.t))
        .slice(0, limit)
        .map((r) => r.entry);
}

function startSearch() {
    const box = document.querySelector('[data-docs-search]');
    const input = document.getElementById('docs-search');
    const list = document.getElementById('docs-search-results');
    const status = document.getElementById('docs-search-status');
    if (!box || !input || !list || box.dataset.ready) return;
    box.dataset.ready = 'true';
    box.hidden = false;

    const root = document.documentElement.dataset;
    const href = (path) => root.docsBase + (root.docsExt === '' && path === 'index' ? '' : path + root.docsExt);
    let results = [];
    let active = -1;

    const close = () => {
        list.classList.add('hidden');
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        active = -1;
    };

    const paint = () => {
        list.replaceChildren(...results.map((entry, i) => {
            const li = document.createElement('li');
            li.id = 'docs-result-' + i;
            li.setAttribute('role', 'option');
            li.setAttribute('aria-selected', i === active ? 'true' : 'false');
            li.className = 'flex cursor-pointer flex-col rounded-md px-2 py-1.5 aria-selected:bg-surface-subtle hover:bg-surface-subtle';
            const title = document.createElement('span');
            title.className = 'type-body-strong';
            title.textContent = entry.t;
            const section = document.createElement('span');
            section.className = 'type-caption';
            section.textContent = entry.s;
            li.append(title, section);
            li.addEventListener('mousedown', (e) => { e.preventDefault(); window.location.href = href(entry.u); });
            return li;
        }));
        if (active >= 0) input.setAttribute('aria-activedescendant', 'docs-result-' + active);
        else input.removeAttribute('aria-activedescendant');
    };

    input.addEventListener('input', () => {
        results = search(window.docsIndex ?? [], input.value);
        active = results.length ? 0 : -1;
        status.textContent = input.value.trim() === '' ? '' : results.length + (results.length === 1 ? ' result' : ' results');
        if (!results.length) { close(); return; }
        paint();
        list.classList.remove('hidden');
        input.setAttribute('aria-expanded', 'true');
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (!list.classList.contains('hidden')) { e.preventDefault(); close(); } else if (input.value) { e.preventDefault(); input.value = ''; status.textContent = ''; }
            return;
        }
        if (!results.length || list.classList.contains('hidden')) return;
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            active = (active + (e.key === 'ArrowDown' ? 1 : -1) + results.length) % results.length;
            paint();
            document.getElementById('docs-result-' + active)?.scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'Enter' && active >= 0) {
            e.preventDefault();
            window.location.href = href(results[active].u);
        }
    });

    input.addEventListener('blur', close);

    // "/" focuses search, as on most docs sites, unless typing somewhere.
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && !e.target.closest('input, textarea, [contenteditable]')) {
            e.preventDefault();
            input.focus();
        }
    });
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', startSearch);
else startSearch();
