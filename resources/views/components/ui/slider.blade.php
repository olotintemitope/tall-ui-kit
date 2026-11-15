{{--
    A custom slider, one thumb or two (docs/components/workflow.md, "Slider"), on the
    WAI-ARIA APG slider and multi-thumb slider patterns: each thumb is role="slider" with
    aria-valuemin/max/now, aria-valuetext (the formatted value: "₦25,000") and its own
    name; with two thumbs each one's range stops at the other's value. x-ui.range (two
    native inputs) stays as it is; see the docs for which to use.

    Keyboard (on a thumb): Right/Up +step, Left/Down -step, Page Up/Down ±`pageStep`
    (a tenth of the range by default), Home/End to the lowest/highest the thumb may
    take. Pointer (mouse, pen, touch): press anywhere on the track to move the nearest
    thumb there, then drag; the track has touch-action: none, so a horizontal finger
    drag never scrolls the page. The value commits on release and on each key press,
    so wire:model.live sends one request per drag, not one per pixel; the printed value
    follows the drag live.

    Look: a 4px `control` track (3.65:1), the chosen part in ink, 20px white thumbs with
    a 1px `control` edge (ink on hover and while dragged), the 2px focus ring on the
    focused thumb. The value prints in an <output> beside the label (numeric role);
    `bounds` prints min and max under the track (caption). Disabled: a dashed track
    and thumbs in `surface-subtle`, no opacity.

    Binding: wire:model / x-model on the component: a number, or [low, high] with
    `dual`. Pass `value` too for the first paint (the binding arrives after Alpine
    starts). Plain forms: `name` posts name (single) or name[] (two values).
    Lazy: the behaviour (resources/js/slider-core.js) loads the first time a slider is
    on the page; until then the server-rendered thumbs show the initial value.

    Props: label (visible; names the group), min (0), max (100), step (1), pageStep,
    dual, value, format (number | money | percent), currency (NGN), decimals, unit
    ("saves", after the number), bounds (false), disabled, hideLabel, name, minLabel
    ("Minimum"), maxLabel ("Maximum"), id.
--}}
@props([
    'label', 'min' => 0, 'max' => 100, 'step' => 1, 'pageStep' => null, 'dual' => false, 'value' => null,
    'format' => 'number', 'currency' => 'NGN', 'decimals' => null, 'unit' => null, 'bounds' => false,
    'disabled' => false, 'hideLabel' => false, 'name' => null, 'minLabel' => 'Minimum', 'maxLabel' => 'Maximum', 'id' => null,
])

@php
    use TallUiKit\Support\Format;

    $min = (float) $min;
    $max = (float) $max > $min ? (float) $max : $min + 1;
    $step = (float) $step > 0 ? (float) $step : 1;
    $pageStep = $pageStep !== null ? (float) $pageStep : max($step, round(($max - $min) / 10 / $step) * $step);
    $snap = fn ($v): float => max($min, min($max, $min + round(((float) $v - $min) / $step) * $step));
    $values = $dual
        ? (is_array($value) && count($value) === 2 ? [$snap($value[0]), $snap($value[1])] : [$min, $max])
        : [$snap(is_array($value) ? ($value[0] ?? $min) : ($value ?? $min))];
    if ($dual && $values[0] > $values[1]) {
        $values = [$values[1], $values[0]];
    }
    $fmt = function (float $v) use ($format, $currency, $decimals, $unit): string {
        $text = match ($format) {
            'money' => Format::money($v, $currency),
            // Trim trailing zeros of the decimals only: 70 stays 70%, 12.50 becomes 12.5%.
            'percent' => (str_contains($n = number_format($v, $decimals ?? 0), '.') ? rtrim(rtrim($n, '0'), '.') : $n).'%',
            default => number_format($v, $decimals ?? (floor($v) == $v ? 0 : 2)),
        };

        return $unit ? $text.' '.$unit : $text;
    };
    $pct = fn (float $v): float => round(($v - $min) / ($max - $min) * 100, 3);
    $id ??= 'slider-'.\Illuminate\Support\Str::slug($label).'-'.substr(md5(json_encode([$min, $max, $step, $dual])), 0, 4);
    $lower = $minLabel.' '.\Illuminate\Support\Str::lower($label);
    $upper = $maxLabel.' '.\Illuminate\Support\Str::lower($label);
    $text = $dual ? $fmt($values[0]).' to '.$fmt($values[1]) : $fmt($values[0]);
    $options = ['min' => $min, 'max' => $max, 'step' => $step, 'pageStep' => $pageStep, 'dual' => (bool) $dual, 'format' => $format, 'currency' => $currency, 'decimals' => $decimals, 'unit' => $unit];
@endphp

<div
    x-data="uiSlider(@js($options))"
    data-value="{{ json_encode($dual ? $values : $values[0]) }}"
    x-modelable="value"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    role="group"
    aria-labelledby="{{ $id }}-label"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model'])->class(['flex min-w-0 flex-col gap-1.5']) }}
    data-slider="{{ $dual ? 'dual' : 'single' }}"
    @if ($disabled) data-disabled @endif
>
    <div @class(['flex flex-wrap items-baseline justify-between gap-x-2', 'sr-only' => $hideLabel])>
        <span id="{{ $id }}-label" class="type-field-label">{{ $label }}</span>
        <output class="type-numeric text-ink-body" aria-hidden="true" data-slider-output>{{ $text }}</output>
    </div>
    <div class="ui-slider" data-slider-track>
        <div class="ui-slider-rail" aria-hidden="true"></div>
        <div class="ui-slider-fill" aria-hidden="true" style="left: {{ $dual ? $pct($values[0]) : 0 }}%; right: {{ 100 - $pct($dual ? $values[1] : $values[0]) }}%" data-slider-fill></div>
        @foreach ($values as $i => $v)
            <div
                role="slider"
                @if ($disabled) aria-disabled="true" tabindex="-1" @else tabindex="0" @endif
                @if ($dual) aria-label="{{ $i === 0 ? $lower : $upper }}" @else aria-labelledby="{{ $id }}-label" @endif
                aria-orientation="horizontal"
                aria-valuemin="{{ $dual && $i === 1 ? $values[0] + 0 : $min + 0 }}"
                aria-valuemax="{{ $dual && $i === 0 ? $values[1] + 0 : $max + 0 }}"
                aria-valuenow="{{ $v + 0 }}"
                aria-valuetext="{{ $fmt($v) }}"
                class="ui-slider-thumb focus-ring"
                style="left: {{ $pct($v) }}%"
                data-slider-thumb="{{ $i }}"
            ></div>
        @endforeach
    </div>
    @if ($bounds)
        <div class="flex justify-between gap-2" aria-hidden="true">
            <span class="type-caption">{{ $fmt($min) }}</span>
            <span class="type-caption">{{ $fmt($max) }}</span>
        </div>
    @endif
    @if ($name && $attributes->whereStartsWith(['wire:model', 'x-model'])->isEmpty())
        @foreach ($values as $i => $v)
            <input type="hidden" name="{{ $dual ? $name.'[]' : $name }}" value="{{ $v + 0 }}" data-slider-input="{{ $i }}">
        @endforeach
    @endif
</div>
