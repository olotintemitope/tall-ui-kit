{{--
    Range slider on native <input type="range">, single or dual (two inputs over one
    track), so arrows, Page Up/Down and Home/End work and each thumb has its own name.
    Look (app.css .ui-range): a 4px `control` track (3.65:1), the chosen part ink, a 24px
    white thumb with a 1px `control` edge; focus is the 2px ring on the thumb. The
    current value is printed in an <output> (numeric role) beside the label, and each
    thumb carries the same text as aria-valuetext.

    Props: label (visible; the group name), min (0), max (100), step (1), steps (a list
    of values: the thumbs move over indexes, so uneven scales snap to real values),
    dual (two thumbs, value [low, high]), format (number | money | percent), currency,
    unit ("saves"), openEnded (the top step reads "500,000+"), value (initial, without
    a binding), minModel / maxModel (Livewire properties for a dual range; sent once on
    release, null at an open end), disabled, hideLabel (keep the label for assistive
    tech only), id. Bind a single or dual value with wire:model / x-model on the
    component instead of minModel/maxModel.
--}}
@props([
    'label', 'min' => 0, 'max' => 100, 'step' => 1, 'steps' => null, 'dual' => false,
    'format' => 'number', 'currency' => 'NGN', 'unit' => null, 'openEnded' => false, 'value' => null,
    'minModel' => null, 'maxModel' => null, 'disabled' => false, 'hideLabel' => false, 'id' => null,
])

@php
    $steps = $steps === null ? null : array_values($steps);
    $dual = $dual || $minModel !== null;
    $id = $id ?? 'range-'.\Illuminate\Support\Str::slug($label).'-'.substr(md5(json_encode([$min, $max, $steps, $minModel, $value])), 0, 4);
    $first = $steps ? 0 : $min;
    $last = $steps ? count($steps) - 1 : $max;
    $inputStep = $steps ? 1 : $step;
    $options = compact('min', 'max', 'step', 'steps', 'dual', 'format', 'currency', 'unit', 'openEnded', 'minModel', 'maxModel', 'value');
    $lower = 'Minimum '.\Illuminate\Support\Str::lower($label);
    $upper = 'Maximum '.\Illuminate\Support\Str::lower($label);
    $fmt = fn ($v) => $v === null ? '' : ($format === 'money' ? \TallUiKit\Support\Format::money($v, $currency) : number_format((float) $v, floor((float) $v) == (float) $v ? 0 : 2));
    $initialText = $dual ? $fmt($steps[0] ?? $min).' to '.$fmt($steps[$last] ?? $max) : $fmt(is_array($value) ? null : ($value ?? ($steps[$last] ?? $max)));
@endphp

<div
    x-data="uiRange(@js($options))"
    @if (! $minModel) x-modelable="value" {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }} @endif
    role="group"
    aria-labelledby="{{ $id }}-label"
    {{ $attributes->whereDoesntStartWith(['wire:model', 'x-model'])->class(['flex min-w-0 flex-col gap-1.5']) }}
    data-range="{{ $dual ? 'dual' : 'single' }}"
>
    <div @class(['flex items-baseline justify-between gap-2', 'sr-only' => $hideLabel])>
        <span id="{{ $id }}-label" class="type-field-label">{{ $label }}</span>
        <output for="{{ $dual ? $id.'-lo '.$id.'-hi' : $id }}" class="type-numeric text-ink-body" aria-hidden="true" x-text="output" data-range-output>{{ $initialText }}</output>
    </div>
    <div @class(['relative h-6', 'ui-range-dual' => $dual])>
        <div class="pointer-events-none absolute inset-x-0 top-1/2 h-1 -translate-y-1/2 rounded-sm bg-control" aria-hidden="true"></div>
        <div class="pointer-events-none absolute top-1/2 h-1 -translate-y-1/2 rounded-sm bg-secondary" aria-hidden="true" x-bind:style="fill" style="left: 0; right: 0" data-range-fill></div>
        @if ($dual)
            <input type="range" class="ui-range" id="{{ $id }}-lo" min="{{ $first }}" max="{{ $last }}" step="{{ $inputStep }}" value="{{ $first }}"
                aria-label="{{ $lower }}" x-model.number="lo" x-on:input="moveLo()" @if ($minModel) x-on:change="push()" @endif
                x-bind:aria-valuetext="text(lo)" @disabled($disabled) data-ui="range">
            <input type="range" class="ui-range" id="{{ $id }}-hi" min="{{ $first }}" max="{{ $last }}" step="{{ $inputStep }}" value="{{ $last }}"
                aria-label="{{ $upper }}" x-model.number="hi" x-on:input="moveHi()" @if ($minModel) x-on:change="push()" @endif
                x-bind:aria-valuetext="text(hi)" @disabled($disabled) data-ui="range">
        @else
            <input type="range" class="ui-range relative" id="{{ $id }}" min="{{ $first }}" max="{{ $last }}" step="{{ $inputStep }}" value="{{ $last }}"
                aria-labelledby="{{ $id }}-label" x-model.number="hi" x-on:input="moveHi()"
                x-bind:aria-valuetext="text(hi)" @disabled($disabled) data-ui="range">
        @endif
    </div>
</div>
