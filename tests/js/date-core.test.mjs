// Run by tests/Feature/DatePickerTest.php through `node --test` (no npm packages needed).
import { test } from 'node:test';
import assert from 'node:assert/strict';
import * as D from '../../resources/js/date-core.js';

const ref = D.make(2026, 10, 15); // Sunday 15 November 2026

test('parses dates leniently into ISO', () => {
    const cases = {
        '2026-11-15': '2026-11-15',
        '15/11/2026': '2026-11-15',
        '15-11-2026': '2026-11-15',
        '15.11.26': '2026-11-15',
        '11/25/2026': '2026-11-25',
        '15 Nov 2026': '2026-11-15',
        '15 November 2026': '2026-11-15',
        'Nov 15, 2026': '2026-11-15',
        'November 15th 2026': '2026-11-15',
        '15-nov-2026': '2026-11-15',
        '1 sept 2026': '2026-09-01',
        '3 Dec': '2026-12-03',
        '20261115': '2026-11-15',
        '15112026': '2026-11-15',
        today: '2026-11-15',
        tomorrow: '2026-11-16',
        yesterday: '2026-11-14',
        '29/02/2028': '2028-02-29',
    };
    for (const [text, expected] of Object.entries(cases)) assert.equal(D.parseDate(text, ref), expected, text);
    for (const bad of ['', '31/02/2026', '32/1/2026', 'soon', '15 Foo 2026', '2026-13-01', '29/02/2027']) assert.equal(D.parseDate(bad, ref), null, bad);
});

test('parses times, months, weeks, datetimes and ranges', () => {
    assert.equal(D.parseTime('18:05'), '18:05');
    assert.equal(D.parseTime('1805'), '18:05');
    assert.equal(D.parseTime('930'), '09:30');
    assert.equal(D.parseTime('6pm'), '18:00');
    assert.equal(D.parseTime('6:30 p.m.'), '18:30');
    assert.equal(D.parseTime('12am'), '00:00');
    assert.equal(D.parseTime('12:15 pm'), '12:15');
    assert.equal(D.parseTime('18.05'), '18:05');
    for (const bad of ['24:00', '13pm', '10:61', 'noon']) assert.equal(D.parseTime(bad), null, bad);

    assert.equal(D.parseMonth('2026-11', ref), '2026-11');
    assert.equal(D.parseMonth('11/2026', ref), '2026-11');
    assert.equal(D.parseMonth('Nov 2026', ref), '2026-11');
    assert.equal(D.parseMonth('march', ref), '2026-03');
    assert.equal(D.parseMonth('2026-13', ref), null);

    assert.equal(D.parseWeek('2026-W47', ref), '2026-W47');
    assert.equal(D.parseWeek('w47 2026', ref), '2026-W47');
    assert.equal(D.parseWeek('week 3', ref), '2026-W03');
    assert.equal(D.parseWeek('2026-W53', ref), '2026-W53');
    assert.equal(D.parseWeek('2027-W53', ref), null);
    assert.equal(D.parseWeek('15/11/2026', ref), '2026-W46');

    assert.equal(D.parseDateTime('2026-11-15T18:05', ref), '2026-11-15T18:05');
    assert.equal(D.parseDateTime('15/11/2026 6pm', ref), '2026-11-15T18:00');
    assert.equal(D.parseDateTime('15 Nov 2026, 18:30', ref), '2026-11-15T18:30');
    assert.equal(D.parseDateTime('15 Nov 2026 at 9:15', ref), '2026-11-15T09:15');
    assert.equal(D.parseDateTime('15 Nov 2026', ref, '09:00'), '2026-11-15T09:00');

    assert.deepEqual(D.parseRange('1 Nov 2026 – 15 Nov 2026', ref), { start: '2026-11-01', end: '2026-11-15' });
    assert.deepEqual(D.parseRange('15/11/2026 to 01/11/2026', ref), { start: '2026-11-01', end: '2026-11-15' });
    assert.equal(D.parseRange('1 Nov – never', ref), null);
});

test('formats every value the way the server renders it', () => {
    assert.equal(D.formatDate('2026-11-05'), '5 Nov 2026');
    assert.equal(D.formatTime('18:05'), '18:05');
    assert.equal(D.formatTime('18:05', 12), '6:05 pm');
    assert.equal(D.formatTime('00:30', 12), '12:30 am');
    assert.equal(D.formatMonth('2026-11'), 'November 2026');
    assert.equal(D.formatWeek('2026-W07'), 'Week 7, 2026');
    assert.equal(D.formatDateTime('2026-11-15T18:05'), '15 Nov 2026, 18:05');
    assert.equal(D.format('range', { start: '2026-11-01', end: '2026-11-15' }), '1 Nov 2026 – 15 Nov 2026');
});

test('ISO weeks follow ISO 8601 at year edges', () => {
    assert.deepEqual(D.isoWeek(D.make(2026, 0, 1)), { year: 2026, week: 1 });
    assert.deepEqual(D.isoWeek(D.make(2027, 0, 1)), { year: 2026, week: 53 });
    assert.deepEqual(D.isoWeek(D.make(2024, 11, 30)), { year: 2025, week: 1 });
    assert.equal(D.weeksInYear(2026), 53);
    assert.equal(D.iso(D.weekMonday(2026, 47)), '2026-11-16');
    assert.equal(D.weekString(D.isoWeek(D.make(2026, 10, 18))), '2026-W47');
});

test('min and max bound values and focus', () => {
    assert.equal(D.within('2026-11-15', '2026-11-01', '2026-11-30'), true);
    assert.equal(D.within('2026-10-31', '2026-11-01', ''), false);
    assert.equal(D.within('2026-12-01', '', '2026-11-30'), false);
    assert.equal(D.within('18:00', '09:00', '17:30'), false);
    assert.equal(D.within('2026-W47', '2026-W01', '2026-W52'), true);
    assert.equal(D.within('', '', ''), false);
    assert.equal(D.iso(D.clampDate(D.make(2026, 9, 1), '2026-11-01', '2026-11-30')), '2026-11-01');
    assert.equal(D.iso(D.clampDate(D.make(2027, 0, 1), '2026-11-01', '2026-11-30')), '2026-11-30');
});

test('keyboard moves follow the APG date picker', () => {
    const at = (key, shift = false, weekStart = 1) => D.iso(D.dayKey(ref, key, shift, weekStart));
    assert.equal(at('ArrowLeft'), '2026-11-14');
    assert.equal(at('ArrowRight'), '2026-11-16');
    assert.equal(at('ArrowUp'), '2026-11-08');
    assert.equal(at('ArrowDown'), '2026-11-22');
    assert.equal(at('PageUp'), '2026-10-15');
    assert.equal(at('PageDown'), '2026-12-15');
    assert.equal(at('PageUp', true), '2025-11-15');
    assert.equal(at('PageDown', true), '2027-11-15');
    assert.equal(at('Home'), '2026-11-09'); // Monday start: Sunday is the last day
    assert.equal(at('End'), '2026-11-15');
    assert.equal(at('Home', false, 0), '2026-11-15'); // Sunday start
    assert.equal(at('End', false, 0), '2026-11-21');
    assert.equal(D.dayKey(ref, 'a'), null);
    assert.equal(D.iso(D.addMonths(D.make(2026, 0, 31), 1)), '2026-02-28');

    const month = (key) => D.isoMonth(D.monthKey(D.make(2026, 10, 1), key));
    assert.equal(month('ArrowUp'), '2026-08');
    assert.equal(month('PageDown'), '2027-11');
    assert.equal(month('Home'), '2026-10');
    assert.equal(month('End'), '2026-12');

    const week = (key, shift) => D.weekString(D.isoWeek(D.weekKey(D.make(2026, 10, 16), key, shift)));
    assert.equal(week('ArrowDown'), '2026-W48');
    assert.equal(week('ArrowUp'), '2026-W46');
    assert.equal(week('PageDown'), '2026-W51');
    assert.equal(week('PageUp', true), '2025-W47');
});

test('the month grid has six rows from the week start', () => {
    const rows = D.monthRows(ref, 1);
    assert.equal(rows.length, 6);
    assert.equal(D.iso(rows[0][0]), '2026-10-26');
    assert.equal(D.iso(D.monthRows(ref, 0)[0][0]), '2026-11-01');
    assert.equal(D.localeWeekStart('en-NG'), 1);
});
