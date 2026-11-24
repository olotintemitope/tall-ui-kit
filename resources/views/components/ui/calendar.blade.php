{{--
    A month of events (docs/components/data-display.md, "Calendar"). From `sm` up, a
    real table: a caption naming the month, one column per weekday (short name shown,
    full name in `abbr`), one row per week, days outside the month muted, today marked
    with aria-current="date" and a 1px ink ring on its number. Each day lists its
    events (time, then title; a link when it has `href`), up to `max`, then "+N more".
    Below `sm` the grid does not fit seven readable columns, so the same events show as
    an agenda: one heading per day that has events, its events beneath. No JavaScript.

    An event's `tone` (neutral | info | success | warning | danger) draws a small dot
    before its title; the title still says what it is, so nothing relies on colour.

    Props: month (Y-m, or any date in the month; default this month), events (list of
    [key, date (Y-m-d or a date-time), title, time (shown as given; default the
    date-time's H:i when it has one), href, tone, meta (a second line, e.g. a venue)]),
    weekStart (1 Monday | 0 Sunday, default 1), max (events shown per day, 3), more
    ("+:count more"), empty ("Nothing scheduled this month."), label (the table's name,
    default the month), headingLevel (agenda day headings, 3), tz (Africa/Lagos), now.
--}}
@props([
    'month' => null, 'events' => [], 'weekStart' => 1, 'max' => 3, 'more' => '+:count more',
    'empty' => 'Nothing scheduled this month.', 'label' => null, 'headingLevel' => 3,
    'tz' => 'Africa/Lagos', 'now' => null,
])

@php
    use Illuminate\Support\Carbon;

    $today = ($now ? Carbon::parse($now) : Carbon::now())->setTimezone($tz)->startOfDay();
    $first = ($month ? Carbon::parse(strlen((string) $month) === 7 ? $month.'-01' : $month, $tz) : $today->copy())->startOfMonth();
    $start = $first->copy()->startOfWeek((int) $weekStart);
    $end = $first->copy()->endOfMonth()->endOfWeek(((int) $weekStart + 6) % 7);
    $tag = 'h'.max(2, min(6, (int) $headingLevel));
    $tones = ['info' => 'bg-info-text', 'success' => 'bg-success-text', 'warning' => 'bg-warning-text', 'danger' => 'bg-danger-text', 'neutral' => 'bg-ink-muted'];

    // Events by day (Y-m-d), each with its display time, in time order.
    $byDay = collect($events)
        ->filter(fn ($e) => is_array($e) && filled($e['date'] ?? null))
        ->map(function (array $e) use ($tz) {
            $raw = (string) $e['date'];
            $at = Carbon::parse($raw, $tz);
            $hasTime = strlen($raw) > 10;
            if ($hasTime) {
                $at = Carbon::parse($raw)->setTimezone($tz);
            }

            return $e + ['_day' => $at->toDateString(), '_sort' => $at->timestamp, '_time' => $e['time'] ?? ($hasTime ? $at->format('H:i') : null)];
        })
        ->sortBy('_sort')
        ->groupBy('_day');

    $monthEvents = $byDay->filter(fn ($list, $day) => substr($day, 0, 7) === $first->format('Y-m'));
    $caption = $label ?? $first->translatedFormat('F Y');

    // One event: tone dot, time, title (a link with href), optional meta line. Escaped.
    $renderEvent = function (array $event) use ($tones): string {
        $dot = isset($tones[$event['tone'] ?? '']) ? '<span class="mt-1.5 size-1.5 shrink-0 rounded-full '.$tones[$event['tone']].'" aria-hidden="true"></span>' : '';
        $time = filled($event['_time'] ?? null) ? '<span class="font-semibold tabular-nums">'.e($event['_time']).'</span> ' : '';
        $title = e((string) ($event['title'] ?? ''));
        $title = filled($event['href'] ?? null) ? '<a href="'.e($event['href']).'" class="rounded-sm text-ink hover:underline focus-ring">'.$title.'</a>' : $title;
        $meta = filled($event['meta'] ?? null) ? '<span class="block text-ink-muted wrap-anywhere">'.e((string) $event['meta']).'</span>' : '';

        return '<div class="flex min-w-0 items-start gap-1.5 type-caption text-ink"'.(filled($event['key'] ?? null) ? ' data-key="'.e($event['key']).'"' : '').'>'.$dot.'<span class="min-w-0 wrap-anywhere">'.$time.$title.$meta.'</span></div>';
    };
    $weekdays = collect(range(0, 6))->map(fn ($i) => $start->copy()->addDays($i));
@endphp

<div {{ $attributes->class(['ui-calendar min-w-0']) }} data-calendar data-month="{{ $first->format('Y-m') }}">
    {{-- From sm up: the month grid. --}}
    <table class="hidden w-full table-fixed border-collapse sm:table" data-calendar-grid>
        <caption class="sr-only">{{ $caption }}</caption>
        <thead>
            <tr>
                @foreach ($weekdays as $weekday)
                    <th scope="col" abbr="{{ $weekday->translatedFormat('l') }}" class="border-b border-line px-2 py-2 text-center type-caption font-medium">{{ $weekday->translatedFormat('D') }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @for ($week = $start->copy(); $week->lte($end); $week->addWeek())
                <tr>
                    @for ($d = 0; $d < 7; $d++)
                        @php
                            $day = $week->copy()->addDays($d);
                            $key = $day->toDateString();
                            $inMonth = $day->month === $first->month;
                            $isToday = $day->isSameDay($today);
                            $list = $byDay->get($key, collect());
                        @endphp
                        <td @class(['h-24 border-b border-r border-line p-1.5 align-top first:border-l', 'bg-surface-subtle' => ! $inMonth]) @if ($isToday) aria-current="date" @endif data-calendar-day="{{ $key }}">
                            <time datetime="{{ $key }}" @class(['inline-flex size-6 items-center justify-center rounded-full type-meta tabular-nums', 'font-semibold text-ink' => $inMonth, 'text-ink-muted' => ! $inMonth, 'ring-1 ring-ink' => $isToday])>{{ $day->day }}</time>
                            @if ($list->isNotEmpty())
                                <ul class="mt-1 flex min-w-0 flex-col gap-1">
                                    @foreach ($list->take((int) $max) as $event)
                                        <li class="min-w-0" data-calendar-event>
                                            {!! $renderEvent($event) !!}
                                        </li>
                                    @endforeach
                                    @if ($list->count() > (int) $max)
                                        <li class="type-caption" data-calendar-more>{{ str_replace(':count', (string) ($list->count() - (int) $max), $more) }}</li>
                                    @endif
                                </ul>
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>

    {{-- Below sm: the month's events as an agenda. --}}
    <div class="sm:hidden" data-calendar-agenda>
        @forelse ($monthEvents as $day => $list)
            @php $date = Carbon::parse($day, $tz); @endphp
            <section class="border-b border-line py-3 first:pt-0 last:border-b-0" @if ($date->isSameDay($today)) aria-current="date" @endif>
                <{{ $tag }} class="type-body-strong"><time datetime="{{ $day }}">{{ $date->translatedFormat('D j M') }}</time></{{ $tag }}>
                <ul class="mt-2 flex flex-col gap-2">
                    @foreach ($list as $event)
                        <li class="min-w-0" data-calendar-event>
                            {!! $renderEvent($event) !!}
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            <p class="type-meta" data-calendar-empty>{{ $empty }}</p>
        @endforelse
    </div>
</div>
