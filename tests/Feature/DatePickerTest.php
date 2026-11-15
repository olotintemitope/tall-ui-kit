<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use TallUiKit\Support\DateInput;

// The custom date and time pickers (x-ui.date-picker and the components on it). The
// browser side (parsing, the keyboard map, min and max, ISO output) is covered by
// tests/js/date-core.test.mjs, run here under `node --test`.

afterEach(fn () => Carbon::setTestNow());

/** The config a picker hands to uiDatePicker(), decoded. */
function pickerConfig(string $html): array
{
    preg_match('/x-data="uiDatePicker\(JSON.parse\(\'(.*?)\'\)\)"/', html_entity_decode($html, ENT_QUOTES), $m);

    return json_decode(json_decode('"'.$m[1].'"'), true);
}

it('runs the picker script tests: lenient parsing, min and max, the keyboard map and ISO output', function () {
    $node = Process::run('node --version');
    if (! $node->successful()) {
        $this->markTestSkipped('Node is not installed.');
    }

    $result = Process::path(kit_path())->run('node --test tests/js/date-core.test.mjs');

    expect($result->exitCode())->toBe(0, $result->output().$result->errorOutput())
        ->and($result->output())->toContain('fail 0');
});

it('renders a text field, a calendar button and a popup anchored below, with no native picker', function () {
    $html = Blade::render('<x-ui.field label="Launch" name="launchDate" hint="Weekdays only." required><x-ui.date wire:model.live="launchDate" min="2026-01-01" value="2026-11-15" /></x-ui.field>');

    expect($html)
        ->toContain('<div x-data="uiDatePicker()" data-value="2026-11-15" x-modelable="value" data-mode="date" data-min="2026-01-01" class="relative min-w-0 max-w-full w-40" wire:model.live="launchDate" data-ui-date>')
        ->toContain('x-modelable="value"')->toContain('data-value="2026-11-15"')
        ->toMatch('/<input type="text"[^>]*id="field-launchdate" value="15 Nov 2026" placeholder="DD\/MM\/YYYY"/')
        ->toContain('aria-required="true"')->toContain('aria-describedby="field-launchdate-hint"')
        ->toContain('aria-label="Choose date" aria-haspopup="dialog" aria-controls="field-launchdate-popup"')
        ->not->toContain('type="hidden"')
        ->toContain('x-anchor.bottom-start.noflip.offset.6.fixed="$refs.box"')
        ->toContain('role="dialog" aria-modal="true" aria-label="Choose date"')
        ->not->toContain('type="date"')
        ->and(substr_count($html, 'wire:model.live="launchDate"'))->toBe(1)
        // Small, because an island re-renders it: no utility soup, no inline SVG, no x-on attributes.
        ->and(strlen(Blade::render('<x-ui.date wire:model="d" min="2026-01-01" max="2026-12-31" />')))->toBeLessThan(1100);
    expect(file_get_contents(kit_path('resources/js/datepicker.js')))->toContain('this.$revealBelow(this.$refs.popup, this.$refs.box)');
});

it('keeps the value out of x-data, so a morph never restarts an open picker', function () {
    $a = Blade::render('<x-ui.date value="2026-11-15" />');
    $b = Blade::render('<x-ui.date value="2026-11-16" />');

    preg_match('/x-data="([^"]*)"/', $a, $first);
    preg_match('/x-data="([^"]*)"/', $b, $second);

    expect($first[1])->toBe($second[1])->not->toContain('2026-11');
});

it('posts ISO values in every mode and prints the same label as the script', function (string $component, string $value, string $iso, string $label) {
    $html = Blade::render("<x-ui.{$component} name=\"v\" value=\"{$value}\" />");

    expect($html)->toContain('<input type="hidden" name="v" value="'.$iso.'"')
        ->toMatch('/<input type="text"[^>]*value="'.preg_quote($label, '/').'"/');
})->with([
    'date' => ['date', '2026-11-05', '2026-11-05', '5 Nov 2026'],
    'date from a datetime string' => ['date', '2026-11-05 14:30:00', '2026-11-05', '5 Nov 2026'],
    'time' => ['time-input', '18:05:00', '18:05', '18:05'],
    'month' => ['month', '2026-11', '2026-11', 'November 2026'],
    'week' => ['week', '2026-W07', '2026-W07', 'Week 7, 2026'],
    'datetime' => ['datetime', '2026-11-21T18:00', '2026-11-21T18:00', '21 Nov 2026, 18:00'],
    'not a date' => ['date', '2026-02-31', '', ''],
]);

it('normalises bound values, Carbon included, into the native formats', function () {
    $at = Carbon::create(2026, 11, 15, 18, 5);

    expect(DateInput::normalize('date', $at))->toBe('2026-11-15')
        ->and(DateInput::normalize('time', $at))->toBe('18:05')
        ->and(DateInput::normalize('month', $at))->toBe('2026-11')
        ->and(DateInput::normalize('week', Carbon::create(2027, 1, 1)))->toBe('2026-W53')
        ->and(DateInput::normalize('datetime', '2026-11-15 18:05:00'))->toBe('2026-11-15T18:05')
        ->and(DateInput::normalize('week', '2027-W53'))->toBe('')
        ->and(DateInput::normalize('time', '24:00'))->toBe('')
        ->and(DateInput::normalize('date', null))->toBe('')
        ->and(DateInput::display('time', '18:05', 12))->toBe('6:05 pm')
        ->and(DateInput::displayRange('2026-11-01', '2026-11-15'))->toBe('1 Nov 2026 – 15 Nov 2026');
});

it('hands min, max, disabled dates and the week start to the script, normalised', function () {
    $html = Blade::render('<x-ui.date min="2026-01-01 00:00:00" max="not a date" :disabled-dates="[\'weekends\', \'2026-12-25\']" week-start="0" />');
    $config = pickerConfig($html);

    expect($config)->toBe(['disabledDates' => ['weekends', '2026-12-25'], 'weekStart' => 0]);
    // min and max ride on data attributes; one that is not a date is dropped.
    expect($html)->toContain('data-min="2026-01-01"')->not->toContain('data-max');
    expect(Blade::render('<x-ui.month min="2025-01" max="2027-12" />'))->toContain('data-min="2025-01" data-max="2027-12" class="relative min-w-0 max-w-full w-40" data-ui-date>')->toContain('x-data="uiDatePicker()"');
    expect($html)->toContain('x-data="uiDatePicker(JSON.parse(&#039;');

    $time = Blade::render('<x-ui.time-input step="900" hours="12" />');
    expect(pickerConfig($time))->toMatchArray(['minuteStep' => 15, 'hours' => 12]);
    expect($time)->toContain('data-mode="time"')->toContain('placeholder="HH:MM am"')
        ->toContain('aria-label="Choose time"');
});

it('binds a range to two properties, or posts name[start] and name[end], with presets from today', function () {
    Carbon::setTestNow('2026-11-15 10:00:00');

    $html = Blade::render('<x-ui.date-range name="period" wire:model.start.live="from" wire:model.end="to" start="2026-11-01" end="2026-11-15" />');

    expect($html)->not->toContain('wire:model')->not->toContain('x-modelable')->not->toContain('type="hidden"')
        ->toMatch('/<input type="text"[^>]*value="1 Nov 2026 – 15 Nov 2026"/')
        ->toContain('aria-label="Choose dates"');
    expect(Blade::render('<x-ui.date-range name="period" start="2026-11-01" end="2026-11-15" />'))
        ->toContain('<input type="hidden" name="period[start]" value="2026-11-01"')
        ->toContain('<input type="hidden" name="period[end]" value="2026-11-15"');
    expect(pickerConfig($html)['bind'])->toBe(['start' => ['name' => 'from', 'live' => true], 'end' => ['name' => 'to', 'live' => false]]);

    expect(DateInput::presets(true))->toBe([
        ['label' => 'Last 7 days', 'start' => '2026-11-09', 'end' => '2026-11-15'],
        ['label' => 'Last 30 days', 'start' => '2026-10-17', 'end' => '2026-11-15'],
        ['label' => 'This month', 'start' => '2026-11-01', 'end' => '2026-11-15'],
        ['label' => 'Custom', 'start' => '', 'end' => ''],
    ])->and(DateInput::presets(['lastMonth', 'thisYear' => 'Year to date', ['label' => 'Launch week', 'start' => '2026-11-16', 'end' => '2026-11-22'], 'nonsense']))->toBe([
        ['label' => 'Last month', 'start' => '2026-10-01', 'end' => '2026-10-31'],
        ['label' => 'Year to date', 'start' => '2026-01-01', 'end' => '2026-11-15'],
        ['label' => 'Launch week', 'start' => '2026-11-16', 'end' => '2026-11-22'],
    ])->and(DateInput::presets(false))->toBe([]);
});

it('marks invalid and disabled pickers', function () {
    $html = Blade::render('<x-ui.field label="Start" name="startDate" error="Choose a later date."><x-ui.date wire:model="startDate" disabled /></x-ui.field>');

    expect($html)->toContain('aria-invalid="true"')->toContain('aria-describedby="field-startdate-error"')->toContain('ui-date-box ui-date-invalid')
        ->toMatch('/<input type="text"[^>]*disabled/')->toMatch('/<button type="button"[^>]*disabled/');
});

it('has no native date or time input in any view', function () {
    $offenders = collect(File::allFiles(kit_path('resources/views')))
        ->filter(fn ($file) => str_ends_with($file->getFilename(), '.php'))
        ->filter(fn ($file) => preg_match('/type=(["\'])(date|time|month|week|datetime-local)\1/i', preg_replace('/\{\{--.*?--\}\}/s', '', $file->getContents())))
        ->map(fn ($file) => $file->getRelativePathname())
        ->values()
        ->all();

    expect($offenders)->toBe([], 'Use x-ui.date, x-ui.datetime, x-ui.time-input, x-ui.month, x-ui.week or x-ui.date-range.');
});
