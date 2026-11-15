/*
 * Date and time helpers for the custom pickers (datepicker.js). Pure functions, no DOM,
 * so tests/js/date-core.test.mjs runs them under `node --test` (DatePickerTest).
 *
 * Calendar dates are UTC midnight Date objects: adding days or months never meets a
 * daylight-saving gap. Values are the strings a native input would post:
 *   date 2026-11-15   time 18:05   month 2026-11   week 2026-W47   datetime 2026-11-15T18:05
 * and every bound (min, max) compares as a string, as ISO strings sort.
 */

export const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
export const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
const DAY = 86400000;

export const pad = (n, width = 2) => String(n).padStart(width, '0');

/* A UTC date for year, month (0-11), day, or null when that day does not exist. */
export function make(y, m, d) {
    if (![y, m, d].every(Number.isInteger) || y < 1 || y > 9999) return null;
    const date = new Date(0);
    date.setUTCFullYear(y, m, d);

    return date.getUTCFullYear() === y && date.getUTCMonth() === m && date.getUTCDate() === d ? date : null;
}

export function daysIn(y, m) {
    const date = new Date(0);
    date.setUTCFullYear(y, m + 1, 0);

    return date.getUTCDate();
}

export const iso = (date) => `${pad(date.getUTCFullYear(), 4)}-${pad(date.getUTCMonth() + 1)}-${pad(date.getUTCDate())}`;
export const isoMonth = (date) => iso(date).slice(0, 7);

export function fromIso(value) {
    const m = /^(\d{4})-(\d{2})-(\d{2})/.exec(String(value ?? ''));

    return m ? make(+m[1], +m[2] - 1, +m[3]) : null;
}

export function today(now = new Date()) {
    return make(now.getFullYear(), now.getMonth(), now.getDate());
}

export function addDays(date, n) {
    const next = new Date(date.getTime());
    next.setUTCDate(next.getUTCDate() + n);

    return next;
}

/* Same day in another month, kept inside that month (31 Jan + 1 month = 28 or 29 Feb). */
export function addMonths(date, n) {
    const total = date.getUTCMonth() + n;
    const y = date.getUTCFullYear() + Math.floor(total / 12);
    const m = ((total % 12) + 12) % 12;

    return make(y, m, Math.min(date.getUTCDate(), daysIn(y, m))) ?? date;
}

export const firstOfMonth = (date) => make(date.getUTCFullYear(), date.getUTCMonth(), 1);

/* ISO 8601 week: weeks start on Monday and week 1 holds the year's first Thursday. */
export function isoWeek(date) {
    const thursday = addDays(date, 3 - ((date.getUTCDay() + 6) % 7));
    const year = thursday.getUTCFullYear();
    const first = weekMonday(year, 1);

    return { year, week: 1 + Math.round((thursday - addDays(first, 3)) / (7 * DAY)) };
}

export function weekMonday(year, week) {
    const jan4 = make(year, 0, 4);

    return addDays(jan4, (week - 1) * 7 - ((jan4.getUTCDay() + 6) % 7));
}

export const weeksInYear = (year) => isoWeek(make(year, 11, 28)).week;
export const weekString = ({ year, week }) => `${pad(year, 4)}-W${pad(week)}`;

export function fromWeek(value) {
    const m = /^(\d{4})-W(\d{2})$/.exec(String(value ?? ''));
    if (!m) return null;
    const year = +m[1];
    const week = +m[2];

    return week >= 1 && week <= weeksInYear(year) ? { year, week } : null;
}

/* Six rows of seven days covering the month, starting on `weekStart` (0 Sunday ... 6 Saturday). */
export function monthRows(date, weekStart = 1) {
    const first = firstOfMonth(date);
    let day = addDays(first, -((first.getUTCDay() - weekStart + 7) % 7));
    const rows = [];
    for (let r = 0; r < 6; r++) {
        const row = [];
        for (let c = 0; c < 7; c++) {
            row.push(day);
            day = addDays(day, 1);
        }
        rows.push(row);
    }

    return rows;
}

/* The first day of the week for a locale (Intl week info), else Monday. */
export function localeWeekStart(locale) {
    try {
        const loc = new Intl.Locale(locale);
        const info = typeof loc.getWeekInfo === 'function' ? loc.getWeekInfo() : loc.weekInfo;
        if (info?.firstDay) return info.firstDay % 7;
    } catch {
        // No Intl.Locale or an unknown tag.
    }

    return 1;
}

/* ---------- Display ---------- */

export const formatDate = (value) => {
    const date = fromIso(value);

    return date ? `${date.getUTCDate()} ${MONTHS[date.getUTCMonth()].slice(0, 3)} ${date.getUTCFullYear()}` : '';
};

export function formatTime(value, hours = 24) {
    const m = /^(\d{2}):(\d{2})/.exec(String(value ?? ''));
    if (!m) return '';
    if (+hours !== 12) return `${m[1]}:${m[2]}`;
    const h = +m[1];

    return `${h % 12 || 12}:${m[2]} ${h < 12 ? 'am' : 'pm'}`;
}

export function formatMonth(value) {
    const m = /^(\d{4})-(\d{2})$/.exec(String(value ?? ''));

    return m && +m[2] >= 1 && +m[2] <= 12 ? `${MONTHS[+m[2] - 1]} ${+m[1]}` : '';
}

export function formatWeek(value) {
    const week = fromWeek(value);

    return week ? `Week ${week.week}, ${week.year}` : '';
}

export function formatDateTime(value, hours = 24) {
    const [date, time] = String(value ?? '').split('T');
    const day = formatDate(date);

    return day && time ? `${day}, ${formatTime(time, hours)}` : day;
}

export function format(mode, value, options = {}) {
    switch (mode) {
        case 'time': return formatTime(value, options.hours);
        case 'month': return formatMonth(value);
        case 'week': return formatWeek(value);
        case 'datetime': return formatDateTime(value, options.hours);
        case 'range': {
            const start = formatDate(value?.start);
            const end = formatDate(value?.end);

            return start && end ? `${start} – ${end}` : start;
        }
        default: return formatDate(value);
    }
}

/* ---------- Lenient parsing (typed text to the value format, or null) ---------- */

const clean = (text) => String(text ?? '').trim().toLowerCase()
    .replace(/(\d)(st|nd|rd|th)\b/g, '$1')
    .replace(/,/g, ' ')
    .replace(/\s+/g, ' ');

const fullYear = (text, ref) => {
    if (text === undefined) return ref.getUTCFullYear();

    return text.length === 2 ? 2000 + +text : +text;
};

/* "nov", "november", "sept" to 0-11, or -1. Three letters at least. */
export function monthIndex(word) {
    const w = String(word ?? '').replace(/\.$/, '');
    if (w.length < 3) return -1;
    if (w === 'sept') return 8;

    return MONTHS.findIndex((name) => name.toLowerCase().startsWith(w));
}

/*
 * Accepts ISO (2026-11-15), day first with / - . or spaces (15/11/2026, 15.11.26), month
 * first when day first cannot be a date (11/25/2026), month names either way round
 * (15 Nov 2026, November 15, 2026, 15-Nov-2026), a day and month alone (this year),
 * eight digits (20261115 or 15112026) and today, tomorrow, yesterday.
 */
export function parseDate(text, ref = today()) {
    const s = clean(text);
    if (!s) return null;
    const out = (date) => (date ? iso(date) : null);
    const words = { today: 0, tomorrow: 1, yesterday: -1 };
    if (s in words) return out(addDays(ref, words[s]));

    let m;
    if ((m = /^(\d{4})[-/. ](\d{1,2})[-/. ](\d{1,2})$/.exec(s))) return out(make(+m[1], +m[2] - 1, +m[3]));
    if ((m = /^(\d{1,2})[-/. ](\d{1,2})[-/. ](\d{2}|\d{4})$/.exec(s))) {
        const y = fullYear(m[3], ref);

        return out(make(y, +m[2] - 1, +m[1]) ?? make(y, +m[1] - 1, +m[2]));
    }
    if ((m = /^(\d{1,2})[-/. ](\d{1,2})$/.exec(s))) return out(make(ref.getUTCFullYear(), +m[2] - 1, +m[1]));
    if ((m = /^(\d{4})(\d{2})(\d{2})$/.exec(s)) && make(+m[1], +m[2] - 1, +m[3])) return out(make(+m[1], +m[2] - 1, +m[3]));
    if ((m = /^(\d{2})(\d{2})(\d{4})$/.exec(s))) return out(make(+m[3], +m[2] - 1, +m[1]));
    if ((m = /^(\d{1,2})[-/. ]([a-z]+\.?)(?:[-/. ](\d{2}|\d{4}))?$/.exec(s))) {
        const month = monthIndex(m[2]);

        return month < 0 ? null : out(make(fullYear(m[3], ref), month, +m[1]));
    }
    if ((m = /^([a-z]+\.?) (\d{1,2})(?: (\d{2}|\d{4}))?$/.exec(s))) {
        const month = monthIndex(m[1]);

        return month < 0 ? null : out(make(fullYear(m[3], ref), month, +m[2]));
    }

    return null;
}

/* 18:05, 18.05, 1805, 930, 18, 6pm, 6:30 pm, 12am; seconds are dropped. */
export function parseTime(text) {
    const s = clean(text).replace(/\s/g, '').replace(/([ap])\.?m\.?$/, '$1m');
    const m = /^(\d{1,2})(?:[:.h]?(\d{2}))?(?::\d{2})?(am|pm|a|p)?$/.exec(s);
    if (!m) return null;
    let h = +m[1];
    const min = m[2] === undefined ? 0 : +m[2];
    if (min > 59) return null;
    if (m[3]) {
        if (h < 1 || h > 12) return null;
        h = (h % 12) + (m[3].startsWith('p') ? 12 : 0);
    }
    if (h > 23) return null;

    return `${pad(h)}:${pad(min)}`;
}

/* 2026-11, 11/2026, Nov 2026, November 26, nov (this year), or any date's month. */
export function parseMonth(text, ref = today()) {
    const s = clean(text);
    if (!s) return null;
    let m;
    if ((m = /^(\d{4})[-/. ](\d{1,2})$/.exec(s))) return +m[2] >= 1 && +m[2] <= 12 ? `${m[1]}-${pad(+m[2])}` : null;
    if ((m = /^(\d{1,2})[-/. ](\d{4})$/.exec(s))) return +m[1] >= 1 && +m[1] <= 12 ? `${m[2]}-${pad(+m[1])}` : null;
    if ((m = /^([a-z]+\.?)(?:[-/. ](\d{2}|\d{4}))?$/.exec(s))) {
        const month = monthIndex(m[1]);

        return month < 0 ? null : `${pad(fullYear(m[2], ref), 4)}-${pad(month + 1)}`;
    }
    const date = parseDate(s, ref);

    return date ? date.slice(0, 7) : null;
}

/* 2026-W47, 2026 W47, W47 2026, week 47 2026, W47 (this ISO year), or any date's week. */
export function parseWeek(text, ref = today()) {
    const s = clean(text);
    if (!s) return null;
    const out = (year, week) => (week >= 1 && week <= weeksInYear(year) ? weekString({ year, week }) : null);
    let m;
    if ((m = /^(\d{4})[- ]?w(?:eek)? ?(\d{1,2})$/.exec(s))) return out(+m[1], +m[2]);
    if ((m = /^w(?:eek|k)? ?(\d{1,2})(?:[ /-](\d{4}))?$/.exec(s))) return out(m[2] ? +m[2] : isoWeek(ref).year, +m[1]);
    const date = fromIso(parseDate(s, ref));

    return date ? weekString(isoWeek(date)) : null;
}

/* A date, then a time with a colon, a dot or am/pm; ISO 2026-11-15T18:05. A date alone takes `defaultTime`. */
export function parseDateTime(text, ref = today(), defaultTime = '09:00') {
    const raw = String(text ?? '').trim().replace(/\s+at\s+|\s*@\s*/i, ' ');
    let m;
    if ((m = /^(\d{4}-\d{2}-\d{2})[t ](\d{2}:\d{2})(?::\d{2})?$/i.exec(raw))) {
        return fromIso(m[1]) && parseTime(m[2]) ? `${m[1]}T${parseTime(m[2])}` : null;
    }
    if ((m = /^(.*?)[\s,]+(\d{1,2}(?:[:.]\d{2})?\s*(?:[ap]\.?m?\.?)?)$/i.exec(raw)) && /[:.]|[ap]/i.test(m[2])) {
        const date = parseDate(m[1], ref);
        const time = parseTime(m[2]);
        if (date && time) return `${date}T${time}`;
    }
    const date = parseDate(raw, ref);

    return date ? `${date}T${defaultTime}` : null;
}

/* "start – end", "start - end" or "start to end"; reversed dates are swapped. */
export function parseRange(text, ref = today()) {
    const parts = String(text ?? '').trim().split(/\s+(?:-|–|—|to|until)\s+/i);
    if (parts.length !== 2) {
        const single = parseDate(text, ref);

        return single ? { start: single, end: single } : null;
    }
    const start = parseDate(parts[0], ref);
    const end = parseDate(parts[1], ref);
    if (!start || !end) return null;

    return start <= end ? { start, end } : { start: end, end: start };
}

export function parse(mode, text, ref = today(), options = {}) {
    switch (mode) {
        case 'time': return parseTime(text);
        case 'month': return parseMonth(text, ref);
        case 'week': return parseWeek(text, ref);
        case 'datetime': return parseDateTime(text, ref, options.defaultTime);
        case 'range': return parseRange(text, ref);
        default: return parseDate(text, ref);
    }
}

/* ---------- Bounds ---------- */

/* Inside min and max (either may be empty); values compare as strings. */
export const within = (value, min, max) => Boolean(value) && (!min || value >= min) && (!max || value <= max);

export function clampDate(date, min, max) {
    const lo = fromIso(min);
    const hi = fromIso(max);
    if (lo && date < lo) return lo;
    if (hi && date > hi) return hi;

    return date;
}

/* ---------- Keyboard (WAI-ARIA APG date picker dialog) ---------- */

/*
 * Where focus goes in the day grid: arrows by day and week, PageUp/PageDown by month,
 * Shift+PageUp/PageDown by year, Home/End to the start and end of the week. Null for any
 * other key.
 */
export function dayKey(date, key, shift = false, weekStart = 1) {
    const offset = (date.getUTCDay() - weekStart + 7) % 7;
    switch (key) {
        case 'ArrowLeft': return addDays(date, -1);
        case 'ArrowRight': return addDays(date, 1);
        case 'ArrowUp': return addDays(date, -7);
        case 'ArrowDown': return addDays(date, 7);
        case 'PageUp': return addMonths(date, shift ? -12 : -1);
        case 'PageDown': return addMonths(date, shift ? 12 : 1);
        case 'Home': return addDays(date, -offset);
        case 'End': return addDays(date, 6 - offset);
        default: return null;
    }
}

/* Month grid (3 columns): arrows by month and row, PageUp/PageDown by year, Home/End along the row. */
export function monthKey(date, key) {
    const col = date.getUTCMonth() % 3;
    switch (key) {
        case 'ArrowLeft': return addMonths(date, -1);
        case 'ArrowRight': return addMonths(date, 1);
        case 'ArrowUp': return addMonths(date, -3);
        case 'ArrowDown': return addMonths(date, 3);
        case 'PageUp': return addMonths(date, -12);
        case 'PageDown': return addMonths(date, 12);
        case 'Home': return addMonths(date, -col);
        case 'End': return addMonths(date, 2 - col);
        default: return null;
    }
}

/* Week grid: Up/Down (and Left/Right) by week, PageUp/PageDown by month, Shift by year, Home/End to the month's first and last week. */
export function weekKey(monday, key, shift = false) {
    const month = addDays(monday, 3);
    switch (key) {
        case 'ArrowUp':
        case 'ArrowLeft': return addDays(monday, -7);
        case 'ArrowDown':
        case 'ArrowRight': return addDays(monday, 7);
        case 'PageUp': return mondayOf(addMonths(month, shift ? -12 : -1));
        case 'PageDown': return mondayOf(addMonths(month, shift ? 12 : 1));
        case 'Home': return mondayOf(firstOfMonth(month));
        case 'End': return mondayOf(make(month.getUTCFullYear(), month.getUTCMonth(), daysIn(month.getUTCFullYear(), month.getUTCMonth())));
        default: return null;
    }
}

export const mondayOf = (date) => addDays(date, -((date.getUTCDay() + 6) % 7));
