{{--
    Circular progress (docs/components/workflow.md, "Progress ring"): a small SVG ring for
    a task's progress where a bar does not fit (a file tile, a table cell, a button). It
    is a loading indicator, never a KPI or a chart: figures go in x-ui.stat.

    role="progressbar" with aria-valuemin, aria-valuemax and aria-valuenow (left out when
    indeterminate, so assistive tech says "busy"), aria-valuetext ("62%" or `valueText`)
    and a name from `label`. Look: a `surface-muted` ring with the arc in the tone's
    colour (the x-ui.progress tones, 3:1 or more on the ring and on white), round caps,
    starting at 12 o'clock. Sizes 24, 40 and 64px (strokes 3, 4 and 6px); `showValue`
    prints the percent in the middle at 40 and 64 (numeric role). Indeterminate: a
    quarter arc turns (transform only); under reduced motion it holds still and pulses
    (opacity).

    Props: label (required), value (null = indeterminate), max (100), size (24 | 40 |
    64), tone (default | success | warning | danger), showValue (false), valueText.
--}}
@props(['label', 'value' => null, 'max' => 100, 'size' => 40, 'tone' => 'default', 'showValue' => false, 'valueText' => null])

@php
    $size = in_array((int) $size, [24, 40, 64], true) ? (int) $size : 40;
    $stroke = [24 => 3, 40 => 4, 64 => 6][$size];
    $r = ($size - $stroke) / 2;
    $c = 2 * M_PI * $r;
    $determinate = $value !== null;
    $max = (float) $max > 0 ? $max : 100;
    $ratio = $determinate ? min(1, max(0, $value / $max)) : 0.25;
    $percent = (int) round($ratio * 100);
    $text = $valueText ?? ($determinate ? $percent.'%' : null);
    $tone = in_array($tone, ['default', 'success', 'warning', 'danger'], true) ? $tone : 'default';
@endphp

<span
    {{ $attributes->class(['ui-ring relative inline-flex shrink-0 items-center justify-center align-middle']) }}
    role="progressbar"
    aria-label="{{ $label }}"
    aria-valuemin="0"
    aria-valuemax="{{ $max + 0 }}"
    @if ($determinate) aria-valuenow="{{ $value + 0 }}" aria-valuetext="{{ $text }}" @endif
    data-tone="{{ $tone }}"
    data-ring="{{ $determinate ? 'determinate' : 'indeterminate' }}"
    style="width: {{ $size }}px; height: {{ $size }}px"
>
    <svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 {{ $size }} {{ $size }}" fill="none" aria-hidden="true" @class(['ui-ring-spin' => ! $determinate])>
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" stroke-width="{{ $stroke }}" class="ui-ring-track" />
        <circle cx="{{ $size / 2 }}" cy="{{ $size / 2 }}" r="{{ $r }}" stroke-width="{{ $stroke }}" stroke-linecap="round" class="ui-ring-arc"
            stroke-dasharray="{{ round($c, 3) }}" stroke-dashoffset="{{ round($c * (1 - $ratio), 3) }}" transform="rotate(-90 {{ $size / 2 }} {{ $size / 2 }})" />
    </svg>
    @if ($showValue && $determinate && $size >= 40)
        <span @class(['absolute inset-0 flex items-center justify-center type-numeric text-ink', 'text-[11px]!' => $size === 40]) aria-hidden="true">{{ $percent }}%</span>
    @endif
</span>
