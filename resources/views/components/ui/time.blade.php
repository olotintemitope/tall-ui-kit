{{--
    A date or time in the page, always as <time datetime="ISO 8601"> so it is
    machine-readable. Modes: absolute (default; "14 Nov 2026", with `withTime`
    "14 Nov 2026, 10:00"), relative ("2h ago", "in 3 days", "Yesterday"; the absolute
    date and time sit in `title` and the datetime attribute) and date-only shorthands
    through `format` (a PHP date format) for tables. Africa/Lagos unless `tz` is given.
    Props: value (Carbon, DateTimeInterface, ISO string or timestamp; null prints an en
    dash), mode (absolute | relative), withTime, format, tz, now (fixes "now" for tests
    and the gallery).
--}}
@props(['value' => null, 'mode' => 'absolute', 'withTime' => false, 'format' => null, 'tz' => 'Africa/Lagos', 'now' => null])

@php
    $at = $value === null || $value === '' ? null : (is_numeric($value) ? \Illuminate\Support\Carbon::createFromTimestamp((int) $value) : \Illuminate\Support\Carbon::parse($value))->setTimezone($tz);
@endphp

@if (! $at)
    <span {{ $attributes->class(['text-ink-muted']) }}>&ndash;</span>
@else
    @php
        $full = $at->format('j M Y, H:i');
        $label = match (true) {
            $format !== null => $at->format($format),
            $mode === 'relative' => \TallUiKit\Support\Format::relative($at, $now ? \Illuminate\Support\Carbon::parse($now)->setTimezone($tz) : null),
            $withTime => $full,
            default => $at->format('j M Y'),
        };
    @endphp
    <time datetime="{{ $at->toIso8601String() }}" @if ($mode === 'relative' || $format !== null) title="{{ $full }}" @endif {{ $attributes }}>{{ $label }}</time>
@endif
