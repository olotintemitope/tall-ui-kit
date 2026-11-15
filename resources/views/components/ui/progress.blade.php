{{--
    Progress of a known task (an upload, a campaign's delivered streams, a profile
    checklist): a native <progress> on a 4px `surface-muted` track with a `chart-1` fill
    (15.8:1 on white, 13.6:1 on the track), a label in the field-label role and the
    percent in numeric on the right. Without `value` it is indeterminate: the element
    has no value (assistive tech says "busy") and a 30% segment slides across (transform
    only, no stripes); reduced motion holds it still and pulses. Not the page-level
    x-ui.progress-bar (2px, automatic, Livewire requests).
    Props: label (required: names the bar), value (null = indeterminate), max (100),
    valueText (replaces the percent, e.g. "1,240 of 2,000 streams"), hideLabel, id.
--}}
@props(['label', 'value' => null, 'max' => 100, 'valueText' => null, 'hideLabel' => false, 'id' => null])

@php
    $id = $id ?? 'progress-'.\Illuminate\Support\Str::slug($label).'-'.substr(md5((string) $value.$max), 0, 4);
    $determinate = $value !== null;
    $percent = $determinate && $max > 0 ? (int) round(min(1, max(0, $value / $max)) * 100) : null;
    $text = $valueText ?? ($determinate ? $percent.'%' : null);
@endphp

<div {{ $attributes->class(['flex min-w-0 flex-col gap-1.5']) }} data-progress="{{ $determinate ? 'determinate' : 'indeterminate' }}">
    <div @class(['flex items-baseline justify-between gap-3', 'sr-only' => $hideLabel])>
        <label for="{{ $id }}" class="type-field-label">{{ $label }}</label>
        @if ($text)
            <span class="type-numeric text-ink-body" aria-hidden="true">{{ $text }}</span>
        @endif
    </div>
    <div class="relative h-1 overflow-hidden rounded-sm">
        <progress
            id="{{ $id }}"
            class="ui-progress"
            max="{{ $max }}"
            @if ($determinate) value="{{ $value }}" @endif
            @if ($valueText) aria-valuetext="{{ $valueText }}" @endif
        >{{ $text ?? 'Working' }}</progress>
        @unless ($determinate)
            <span class="ui-progress-segment" aria-hidden="true"></span>
        @endunless
    </div>
</div>
