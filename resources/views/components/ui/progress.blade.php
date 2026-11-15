{{--
    Progress of a known task (an upload, a campaign's delivered streams, a profile
    checklist): a native <progress> (the accessible element: its label, value, max and
    aria-valuetext) drawn as a flat track with a fill. Without `value` it is
    indeterminate: the element has no value (assistive tech says "busy") and a 30%
    segment slides across (transform only, no stripes); reduced motion holds it still and
    pulses. Not the page-level x-ui.progress-bar (2px, automatic, Livewire requests).

    Look: a `surface-muted` track, 2px radius; the fill in the tone's colour, each at 3:1
    or more on the track and on white (KitContrast "Progress"): default chart-1, success
    emerald-700, warning amber-800, danger red-700. `buffer` draws a second, lighter fill
    (`line-strong`) behind the value, for uploads and streams that load ahead. When the
    value changes (a Livewire morph keeps the element), the fill scales to it with
    transform: scaleX over --motion-md ease-motion-out; instant under reduced motion.

    Props: label (required: names the bar), value (null = indeterminate), max (100),
    size (sm 4px, the default | md 8px | lg 12px), tone (default | success | warning |
    danger), labelPosition (top: label left, value right, above the bar | end: the value
    after the bar on one line | hidden: both kept for assistive tech only), format
    (percent | count: "3 of 5" | bytes: "2.1 MB of 5 MB"), buffer (a value on the same
    scale), valueText (replaces the printed value, e.g. "1,240 of 2,000 streams"),
    hideLabel (the older name for labelPosition="hidden"), id.
--}}
@props([
    'label', 'value' => null, 'max' => 100, 'valueText' => null, 'hideLabel' => false, 'id' => null,
    'size' => 'sm', 'tone' => 'default', 'labelPosition' => 'top', 'format' => 'percent', 'buffer' => null,
])

@php
    $id = $id ?? 'progress-'.\Illuminate\Support\Str::slug($label).'-'.substr(md5((string) $value.$max), 0, 4);
    $determinate = $value !== null;
    $max = (float) $max > 0 ? $max : 100;
    $ratio = $determinate ? min(1, max(0, $value / $max)) : 0;
    $percent = $determinate ? (int) round($ratio * 100) : null;
    $bytes = function (float $n): string {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($n >= 1000 && $i < count($units) - 1) {
            $n /= 1000;
            $i++;
        }

        return ($i === 0 ? number_format($n) : rtrim(rtrim(number_format($n, 1), '0'), '.')).' '.$units[$i];
    };
    $text = $valueText ?? ($determinate ? match ($format) {
        'count' => number_format((float) $value).' of '.number_format((float) $max),
        'bytes' => $bytes((float) $value).' of '.$bytes((float) $max),
        default => $percent.'%',
    } : null);
    $ariaText = $valueText ?? ($determinate && $format !== 'percent' ? $text : null);
    $bufferRatio = $buffer !== null ? min(1, max($ratio, $buffer / $max)) : null;
    $position = $hideLabel ? 'hidden' : (in_array($labelPosition, ['top', 'end', 'hidden'], true) ? $labelPosition : 'top');
    $height = ['sm' => 'h-1', 'md' => 'h-2', 'lg' => 'h-3'][$size] ?? 'h-1';
    $tone = in_array($tone, ['default', 'success', 'warning', 'danger'], true) ? $tone : 'default';
@endphp

<div {{ $attributes->class(['flex min-w-0 flex-col gap-1.5', 'ui-progress-end' => $position === 'end']) }} data-progress="{{ $determinate ? 'determinate' : 'indeterminate' }}">
    <div @class(['flex items-baseline justify-between gap-3', 'sr-only' => $position === 'hidden'])>
        <label for="{{ $id }}" class="type-field-label">{{ $label }}</label>
        @if ($text && $position !== 'end')
            <span class="type-numeric text-ink-body" aria-hidden="true">{{ $text }}</span>
        @endif
    </div>
    <div class="flex min-w-0 items-center gap-3">
        <div @class(['ui-progress-track relative min-w-0 flex-1 overflow-hidden rounded-sm', $height]) data-tone="{{ $tone }}">
            <progress
                id="{{ $id }}"
                class="ui-progress"
                max="{{ $max }}"
                @if ($determinate) value="{{ $value }}" @endif
                @if ($ariaText) aria-valuetext="{{ $ariaText }}" @endif
            >{{ $text ?? 'Working' }}</progress>
            @if ($determinate)
                @if ($bufferRatio !== null)
                    <span class="ui-progress-buffer" style="transform: scaleX({{ round($bufferRatio, 4) }})" aria-hidden="true" data-progress-buffer></span>
                @endif
                <span class="ui-progress-fill" style="transform: scaleX({{ round($ratio, 4) }})" aria-hidden="true" data-progress-fill></span>
            @else
                <span class="ui-progress-segment" aria-hidden="true"></span>
            @endif
        </div>
        @if ($text && $position === 'end')
            <span class="type-numeric shrink-0 whitespace-nowrap text-ink-body" aria-hidden="true">{{ $text }}</span>
        @endif
    </div>
</div>
