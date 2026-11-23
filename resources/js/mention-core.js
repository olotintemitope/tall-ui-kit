/*
 * @mentions for x-ui.chat.composer (docs/components/community.md, "Composer").
 * Pure functions, no DOM: tested by tests/js/mention-core.test.mjs through `node --test`.
 */

/**
 * The mention being typed at the caret: an "@" at the start of the text or after
 * whitespace, followed by name characters up to the caret. Null when there is none
 * (an email address such as ada@example.com is not a mention).
 *
 * @returns {{ start: number, query: string } | null} start is the index of the "@"
 */
export function mentionAt(text, caret) {
    const before = String(text ?? '').slice(0, Math.max(0, caret ?? 0));
    const match = before.match(/(^|\s)@([\p{L}\p{N}_.'-]{0,40})$/u);
    if (!match) return null;

    return { start: before.length - match[2].length - 1, query: match[2] };
}

/** People whose name (or key) starts a word with the query, best first, at most `limit`. */
export function matchPeople(people, query, limit = 6) {
    const q = String(query ?? '').toLocaleLowerCase();
    const list = Array.isArray(people) ? people : [];
    if (q === '') return list.slice(0, limit);

    const scored = [];
    for (const person of list) {
        const name = String(person?.name ?? '').toLocaleLowerCase();
        const key = String(person?.key ?? '').toLocaleLowerCase();
        let score = -1;
        if (name.startsWith(q) || key.startsWith(q)) score = 0;
        else if (name.split(/\s+/).some((word) => word.startsWith(q))) score = 1;
        else if (name.includes(q)) score = 2;
        if (score >= 0) scored.push([score, person]);
    }
    scored.sort((a, b) => a[0] - b[0]);

    return scored.slice(0, limit).map(([, person]) => person);
}

/**
 * Replaces the mention being typed (from `start` to the caret) with "@Name " and
 * returns the new text and caret.
 */
export function insertMention(text, start, caret, name) {
    const value = String(text ?? '');
    const token = `@${String(name ?? '').trim()} `;
    const after = value.slice(caret).replace(/^\s+/, '');

    return { text: value.slice(0, start) + token + after, caret: start + token.length };
}
