{{--
    A date and time field: the calendar plus hour and minute columns in one popup below
    the field. The value is Y-m-d\TH:i, as datetime-local posted; w-56 by default.
    Props and wiring: x-ui.date-picker (min, max, step or minuteStep, hours 12 | 24,
    defaultTime, disabledDates, size, width, invalid).
--}}
{{-- Multi-word props are taken here and passed on by name: a kebab-case attribute does not survive the {{ $attributes }} spread. --}}
@props(['disabledDates' => null, 'weekStart' => null, 'minuteStep' => null, 'defaultTime' => '09:00', 'startName' => null, 'endName' => null])

<x-ui.date-picker mode="datetime" :disabled-dates="$disabledDates" :week-start="$weekStart" :minute-step="$minuteStep" :default-time="$defaultTime" :start-name="$startName" :end-name="$endName" {{ $attributes }} />
