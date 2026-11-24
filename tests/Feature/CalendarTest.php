<?php

use Illuminate\Support\Facades\Blade;

/*
| x-ui.calendar (docs/components/data-display.md, "Calendar"): a month table from sm up,
| an agenda below it, today marked, events escaped, "+N more" past `max`.
*/

function calendarHtml(array $props = []): string
{
    return Blade::render('<x-ui.calendar :month="$month" :events="$events" :now="$now" :max="$max" />', $props + [
        'month' => '2026-11',
        'now' => '2026-11-24 09:00',
        'max' => 3,
        'events' => [
            ['key' => 'a', 'date' => '2026-11-24T14:00:00+01:00', 'title' => 'Fire drill', 'tone' => 'warning', 'meta' => 'Hall B'],
            ['key' => 'b', 'date' => '2026-11-24T09:30:00+01:00', 'title' => 'Induction <b>day</b>', 'href' => '/sessions/b'],
            ['key' => 'c', 'date' => '2026-11-03', 'title' => 'All day', 'time' => 'All day'],
            ['key' => 'z', 'date' => '2026-12-02', 'title' => 'Next month'],
        ],
    ]);
}

it('draws the month as a table with a caption and weekday headers, Monday first', function () {
    $html = calendarHtml();

    expect($html)->toContain('data-calendar-grid', '<caption class="sr-only">November 2026</caption>')
        ->and(substr_count($html, '<th scope="col"'))->toBe(7)
        ->and($html)->toMatch('/<th scope="col" abbr="Monday"[^>]*>Mon<\/th>/')
        // 26 Oct (Monday) to 6 Dec (Sunday): six weeks.
        ->and(substr_count($html, '<tr>'))->toBe(7)
        ->and($html)->toContain('data-calendar-day="2026-10-26"', 'data-calendar-day="2026-12-06"');
});

it('marks today and lists its events in time order, escaped, linked when they have href', function () {
    $html = calendarHtml();
    $today = substr($html, strpos($html, 'data-calendar-day="2026-11-24"'));
    $today = substr($today, 0, strpos($today, '</td>'));

    expect($html)->toMatch('/<td[^>]*aria-current="date"[^>]*data-calendar-day="2026-11-24"/')
        ->and($today)->toContain('<a href="/sessions/b"')
        ->toContain('Induction &lt;b&gt;day&lt;/b&gt;')
        ->not->toContain('<b>day</b>')
        ->toContain('Hall B')
        ->toContain('bg-warning-text')
        ->and(strpos($today, '09:30'))->toBeLessThan(strpos($today, '14:00'));
});

it('shows a given time as written and counts what does not fit', function () {
    $events = collect(range(1, 5))->map(fn ($i) => ['key' => "e$i", 'date' => "2026-11-10T0{$i}:00:00+01:00", 'title' => "Event $i"])->all();
    $html = calendarHtml(['events' => [...$events, ['key' => 'c', 'date' => '2026-11-03', 'title' => 'All day', 'time' => 'All day']]]);

    expect($html)->toContain('All day</span> All day')
        ->toContain('data-calendar-more>+2 more</li>')
        ->and(substr_count($html, 'Event 5'))->toBe(1); // in the agenda only
});

it('shows the month as an agenda on phones, and says when it is empty', function () {
    $html = calendarHtml();
    $agenda = substr($html, strpos($html, 'data-calendar-agenda'));

    expect($agenda)->toContain('<time datetime="2026-11-24">Tue 24 Nov</time>', '<time datetime="2026-11-03">')
        ->not->toContain('Next month');

    expect(calendarHtml(['events' => []]))->toContain('data-calendar-empty>Nothing scheduled this month.</p>');
});

it('starts the week on Sunday when asked', function () {
    $html = Blade::render('<x-ui.calendar month="2026-11" :week-start="0" now="2026-11-24" />');

    expect($html)->toMatch('/<th scope="col" abbr="Sunday"[^>]*>Sun<\/th>/')
        ->and($html)->toContain('data-calendar-day="2026-11-01"');
});
