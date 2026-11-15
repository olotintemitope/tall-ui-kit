{{--
    A time field: hour and minute columns (listboxes) in a popup below the field, or
    typed (18:05, 1805, 6pm). The value is H:i in 24-hour time; `hours="12"` changes
    the display only. `step` (seconds, as the native input) or `minuteStep` (5) sets
    the minute column. Props and wiring: x-ui.date-picker (min, max, size, width, invalid).
--}}
{{-- Multi-word props are taken here and passed on by name: a kebab-case attribute does not survive the {{ $attributes }} spread. --}}
@props(['disabledDates' => null, 'weekStart' => null, 'minuteStep' => null, 'defaultTime' => '09:00', 'startName' => null, 'endName' => null])

<x-ui.date-picker mode="time" :disabled-dates="$disabledDates" :week-start="$weekStart" :minute-step="$minuteStep" :default-time="$defaultTime" :start-name="$startName" :end-name="$endName" {{ $attributes }} />
