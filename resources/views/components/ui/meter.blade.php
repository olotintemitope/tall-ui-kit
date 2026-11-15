{{--
    A measurement within a known range (a curator's acceptance rate, storage used, a
    profile's completeness): a native <meter>. The browser colours the 4px bar from
    `low`, `high` and `optimum`: the optimum region emerald-700, the next amber-700, the
    far one red-700 (each 4.3:1 or more on the track and on white). The value and its
    word ("Good", "Fair", "Poor", from the same regions) are printed beside the label,
    so colour is never the only cue.
    Props: label, value, min (0), max (100), low, high, optimum, format (percent |
    number), valueText (replaces the printed value), words ([good, fair, poor]; false
    hides them), id.
--}}
@props([
    'label', 'value', 'min' => 0, 'max' => 100, 'low' => null, 'high' => null, 'optimum' => null,
    'format' => 'percent', 'valueText' => null, 'words' => ['Good', 'Fair', 'Poor'], 'id' => null,
])

@php
    $id = $id ?? 'meter-'.\Illuminate\Support\Str::slug($label);
    $low ??= $min;
    $high ??= $max;
    $optimum ??= $max;
    $region = fn ($v) => $v < $low ? 'low' : ($v > $high ? 'high' : 'mid');
    $distance = match (true) {
        $region($value) === $region($optimum) => 0,
        ($region($value) === 'mid') || ($region($optimum) === 'mid') => 1,
        default => 2,
    };
    $word = is_array($words) ? ($words[$distance] ?? null) : null;
    $shown = $valueText ?? ($format === 'percent' ? round(($value - $min) / max(1e-9, $max - $min) * 100).'%' : number_format((float) $value));
@endphp

<div {{ $attributes->class(['flex min-w-0 flex-col gap-1.5']) }} data-meter data-meter-level="{{ ['optimum', 'suboptimum', 'even-less-good'][$distance] }}">
    <div class="flex items-baseline justify-between gap-3">
        <label for="{{ $id }}" class="type-field-label">{{ $label }}</label>
        <span class="type-numeric text-ink-body">{{ $shown }}@if ($word)<span class="type-meta"> · {{ $word }}</span>@endif</span>
    </div>
    <meter
        id="{{ $id }}"
        class="ui-meter"
        min="{{ $min }}" max="{{ $max }}" value="{{ $value }}"
        low="{{ $low }}" high="{{ $high }}" optimum="{{ $optimum }}"
        aria-valuetext="{{ trim($shown.($word ? ', '.$word : '')) }}"
    >{{ $shown }}</meter>
</div>
