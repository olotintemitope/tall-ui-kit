{{--
    A date field with the kit's calendar popup (no native picker). The value is Y-m-d,
    as the native input posted, w-40 by default. Typing is parsed leniently (15/11/2026,
    15 Nov 2026, 2026-11-15, today). Props and wiring: x-ui.date-picker (min, max,
    disabledDates, weekStart, size, width, invalid, defer). Siblings: x-ui.datetime,
    x-ui.time-input, x-ui.month, x-ui.week, x-ui.date-range.
--}}
{{-- Multi-word props are taken here and passed on by name: a kebab-case attribute does not survive the {{ $attributes }} spread. --}}
@props(['disabledDates' => null, 'weekStart' => null, 'minuteStep' => null, 'defaultTime' => '09:00', 'startName' => null, 'endName' => null])

<x-ui.date-picker mode="date" :disabled-dates="$disabledDates" :week-start="$weekStart" :minute-step="$minuteStep" :default-time="$defaultTime" :start-name="$startName" :end-name="$endName" {{ $attributes }} />
