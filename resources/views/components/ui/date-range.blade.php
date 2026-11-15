{{--
    A date range: one field ("1 Nov 2026 – 15 Nov 2026"), a calendar where the first
    click sets the start and the second the end (the days between preview on hover), and
    preset buttons above it (Last 7 days, Last 30 days, This month, Custom by default;
    `presets` sets them, false hides them). Two values, both Y-m-d:
        <x-ui.date-range wire:model.start.live="from" wire:model.end.live="to" />
    or one array property ['start' => , 'end' => ] with wire:model. Plain forms post
    name[start] and name[end] (or startName, endName). w-64 by default. Props and
    wiring: x-ui.date-picker (min, max, disabledDates, weekStart, start, end, size, width).
--}}
{{-- Multi-word props are taken here and passed on by name: a kebab-case attribute does not survive the {{ $attributes }} spread. --}}
@props(['disabledDates' => null, 'weekStart' => null, 'minuteStep' => null, 'defaultTime' => '09:00', 'startName' => null, 'endName' => null])

<x-ui.date-picker mode="range" :disabled-dates="$disabledDates" :week-start="$weekStart" :minute-step="$minuteStep" :default-time="$defaultTime" :start-name="$startName" :end-name="$endName" {{ $attributes }} />
