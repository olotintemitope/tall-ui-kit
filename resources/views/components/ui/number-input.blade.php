{{--
    Number stepper: a text field with role="spinbutton" and two divided buttons inside
    the same 1px `control` border (minus, then plus, on the right, each 32px wide and
    the control's full height). The browser's spin buttons never show. Up/Down step,
    Page Up/Down step ten times, Home/End jump to min/max; the buttons step without
    taking focus from a keyboard user and turn aria-disabled at a bound. Typed values
    are clamped and rounded to `step` on blur. Money is grouped with its sign on blur
    ("₦150,000") and plain while editing. Bind with wire:model / x-model on the
    component (a number or null; `.live.debounce` works as on an input).
    Props: min, max, step (1), format (number | money | percent), currency (NGN | USD),
    decimals, size (default h-9 | sm h-8), width (sm), invalid, label (accessible name
    outside x-ui.field), decrementLabel / incrementLabel ("Decrease" / "Increase",
    suffixed with the field's name when given as `noun`), noun.
--}}
@props([
    'min' => null, 'max' => null, 'step' => 1, 'format' => 'number', 'currency' => 'NGN', 'decimals' => null,
    'size' => 'default', 'width' => null, 'invalid' => null, 'label' => null, 'noun' => null, 'value' => null,
])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\FormControl;

    $model = FormControl::model($attributes);
    $fieldName = $name ?? $model;
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'number-'.uniqid();
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $small = $size === 'sm';
    $options = ['min' => $min, 'max' => $max, 'step' => $step, 'format' => $format, 'currency' => $currency, 'decimals' => $decimals, 'value' => $value];
    $button = ['flex shrink-0 items-center justify-center border-l border-control text-ink-muted transition-colors duration-100 focus-ring-inset state-hover:bg-surface-subtle state-hover:text-ink state-active:bg-surface-muted aria-disabled:cursor-not-allowed aria-disabled:text-ink-muted aria-disabled:bg-surface-subtle', $small ? 'w-7' : 'w-8'];
    $suffix = $noun ? ' '.$noun : '';
@endphp

<div
    x-data="uiStepper(@js($options))"
    x-modelable="value"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    {{ $attributes->only('class')->class([
        'flex items-stretch overflow-hidden rounded-md border bg-white transition-[border-color,box-shadow] duration-100',
        FormControl::width($width, 'sm'),
        $small ? 'h-8' : 'h-9',
        'border-red-600 focus-within:ring-2 focus-within:ring-red-600/20' => $isInvalid,
        'border-control hover:border-control-hover focus-within:border-focus focus-within:ring-2 focus-within:ring-primary/20' => ! $isInvalid,
        'has-disabled:bg-surface-subtle has-disabled:cursor-not-allowed has-disabled:border-dashed',
    ]) }}
    data-control-box
    data-number-input
>
    <input
        x-ref="input"
        type="text"
        role="spinbutton"
        inputmode="{{ ($decimals ?? ($format === 'money' && $currency === 'USD' ? 2 : 0)) > 0 || ($step != (int) $step) ? 'decimal' : 'numeric' }}"
        autocomplete="off"
        id="{{ $controlId }}"
        @if ($label) aria-label="{{ $label }}" @endif
        @if ($min !== null) aria-valuemin="{{ $min }}" @endif
        @if ($max !== null) aria-valuemax="{{ $max }}" @endif
        x-bind:aria-valuenow="value"
        x-bind:aria-valuetext="valueText || null"
        @if ($isInvalid) aria-invalid="true" @endif
        @if ($required) aria-required="true" @endif
        @if ($d = FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message)) aria-describedby="{{ $d }}" @endif
        x-bind:value="text"
        x-on:input="typed($event.target.value); text = $event.target.value"
        x-on:focus="focus()"
        x-on:blur="blur()"
        x-on:keydown="key($event)"
        data-ui="number"
        {{ $attributes->except(['class', 'id'])->whereDoesntStartWith(['wire:model', 'x-model']) }}
        @class([
            'min-w-0 flex-1 bg-transparent px-3 font-body text-body text-ink outline-none tabular-nums placeholder:text-ink-muted',
            'disabled:cursor-not-allowed disabled:text-ink-muted',
        ])
    />
    <button type="button" tabindex="-1" @class($button) x-on:click="atMin || stepBy(-1)" x-on:mousedown.prevent x-bind:aria-disabled="atMin ? 'true' : null" aria-label="Decrease{{ $suffix }}" aria-controls="{{ $controlId }}" data-step="down">
        <x-ui.icon name="minus" class="size-4" />
    </button>
    <button type="button" tabindex="-1" @class($button) x-on:click="atMax || stepBy(1)" x-on:mousedown.prevent x-bind:aria-disabled="atMax ? 'true' : null" aria-label="Increase{{ $suffix }}" aria-controls="{{ $controlId }}" data-step="up">
        <x-ui.icon name="plus" class="size-4" />
    </button>
</div>
