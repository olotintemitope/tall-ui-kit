{{--
    Colour picker: the native swatch (opens the system picker; 28px, 4px radius, 1px
    `control` edge) and a hex field inside one control border. Typing a hex (#RGB or
    #RRGGBB, with or without #) updates the swatch; picking updates the field; an
    invalid hex marks the field aria-invalid until fixed. Bind wire:model / x-model on
    the component (an upper-case #RRGGBB string).
    Props: value (#212322), size, width (sm), label (accessible name of the hex field
    outside x-ui.field), swatchLabel ("Pick a colour").
--}}
@props(['value' => '#212322', 'size' => 'default', 'width' => null, 'label' => null, 'swatchLabel' => 'Pick a colour', 'invalid' => null])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'width' => null])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'color-'.uniqid();
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
@endphp

<div
    x-data="uiColor(@js(strtoupper($value)))"
    x-modelable="value"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    {{ $attributes->only('class')->class([
        'flex items-center gap-2 overflow-hidden rounded-md border bg-surface pl-1 transition-[border-color,box-shadow] duration-100',
        FormControl::width($width, 'sm'),
        FormControl::height($size),
        'border-danger focus-within:ring-2 focus-within:ring-danger/20' => $isInvalid,
        'border-control hover:border-control-hover focus-within:border-focus focus-within:ring-2 focus-within:ring-primary/20' => ! $isInvalid,
        'has-disabled:bg-surface-subtle has-disabled:cursor-not-allowed has-disabled:border-dashed has-disabled:hover:border-control',
    ]) }}
    data-control-box
    data-color
>
    <input type="color" class="ui-color-swatch size-7 shrink-0 rounded-sm focus-ring" x-bind:value="hex.toLowerCase()" x-on:input="picked($event.target.value)" aria-label="{{ $swatchLabel }}" data-ui="color" />
    <input
        type="text"
        id="{{ $controlId }}"
        x-bind:value="hex"
        x-on:change="typed($event.target.value)"
        x-on:keydown.enter.prevent="typed($event.target.value)"
        x-bind:aria-invalid="(invalid || @js($isInvalid)) ? 'true' : null"
        @if ($isInvalid) aria-invalid="true" @endif
        @if ($label) aria-label="{{ $label }}" @endif
        @if ($d = FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message)) aria-describedby="{{ $d }}" @endif
        spellcheck="false"
        autocapitalize="characters"
        autocomplete="off"
        maxlength="7"
        data-ui="input"
        class="min-w-0 flex-1 bg-transparent pr-3 font-mono text-meta text-ink uppercase outline-none disabled:cursor-not-allowed disabled:text-ink-muted"
    />
    <span class="sr-only" aria-live="polite" x-text="invalid ? 'Not a colour: use # and six hex digits.' : ''"></span>
</div>
