{{--
    An ISO week field: a month calendar with week numbers; a week (its row) is chosen.
    The value is 2026-W47; the field shows "Week 47, 2026". Weeks start on Monday.
    Props and wiring: x-ui.date-picker (min, max as 2026-W01, size, width, invalid).
--}}
{{-- Multi-word props are taken here and passed on by name: a kebab-case attribute does not survive the {{ $attributes }} spread. --}}
@props(['disabledDates' => null, 'weekStart' => null, 'minuteStep' => null, 'defaultTime' => '09:00', 'startName' => null, 'endName' => null])

<x-ui.date-picker mode="week" :disabled-dates="$disabledDates" :week-start="$weekStart" :minute-step="$minuteStep" :default-time="$defaultTime" :start-name="$startName" :end-name="$endName" {{ $attributes }} />
