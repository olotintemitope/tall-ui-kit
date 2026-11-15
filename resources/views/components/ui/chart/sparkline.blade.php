{{--
    Sparkline: a 24px tall trend line for table cells and stat strips, never decoration.
    A 1.5px chart-2 line with a 3px dot on the last point; no axes, no tooltip. Inline
    SVG drawn at its real pixel size, so the stroke and dot are not stretched.
    Use x-ui.chart type="sparkline" or this component directly.
    Props: points (list of numbers), label (accessible name, e.g. "Revenue, last 14 days"),
    width (px, default 80). The accessible name adds the first and last values.
--}}
@props(['points' => [], 'label' => 'Trend', 'width' => 80])

@php
    $values = array_values(array_map(fn ($v): float => is_numeric($v) ? (float) $v : 0.0, $points));
    $w = max(24, (int) $width);
    $h = 24;
    $pad = 2; // keeps the end dot and the stroke inside the box
    $count = count($values);
    // Anchored at zero for counts and amounts, so a small wobble does not fill the box.
    $min = $count ? min(0, min($values)) : 0;
    $max = $count ? max($values) : 0;
    $range = $max - $min;

    $coords = collect($values)->map(function (float $v, int $i) use ($count, $w, $h, $pad, $min, $range): array {
        $x = $count > 1 ? $pad + $i * ($w - 2 * $pad) / ($count - 1) : $w / 2;
        // A flat series sits on the middle line.
        $y = $range > 0 ? $h - $pad - ($v - $min) / $range * ($h - 2 * $pad) : $h / 2;

        return [round($x, 2), round($y, 2)];
    });

    $path = $coords->map(fn (array $c): string => $c[0].','.$c[1])->implode(' ');
    $last = $coords->last();
    $summary = $count
        ? $label.', from '.\TallUiKit\Support\Format::number($values[0]).' to '.\TallUiKit\Support\Format::number($values[$count - 1])
        : $label.', no data';
@endphp

<svg
    width="{{ $w }}"
    height="{{ $h }}"
    viewBox="0 0 {{ $w }} {{ $h }}"
    role="img"
    aria-label="{{ $summary }}"
    {{ $attributes->class(['inline-block shrink-0 align-middle forced-color-adjust-none']) }}
    data-ui-chart="sparkline"
>
    @if ($count > 1)
        <polyline points="{{ $path }}" fill="none" class="stroke-chart-2" stroke-width="1.5" stroke-linejoin="round" stroke-linecap="round" />
    @endif
    @if ($last)
        <circle cx="{{ $last[0] }}" cy="{{ $last[1] }}" r="1.5" class="fill-chart-2" />
    @endif
</svg>
