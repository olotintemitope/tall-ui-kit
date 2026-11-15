{{--
    On/off switch row: a <button role="switch"> with aria-checked on the left, label
    (body-strong) and description (meta) on the right; the whole row toggles it.
    Bind with wire:model or x-model (x-modelable). Props: label, description, checked
    (initial state without a binding), name (submits a hidden 1/0 input in plain forms),
    size (default|sm). Inside x-ui.field it takes the field's id and describedby wiring.
--}}
@props(['label' => null, 'description' => null, 'checked' => false, 'size' => 'default', 'invalid' => null])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null])

@php
    use TallUiKit\Support\FormControl;

    $binding = $attributes->filter(fn ($value, $key) => str_starts_with($key, 'wire:model') || $key === 'x-model' || str_starts_with($key, 'x-model.'));
    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'switch-'.uniqid();
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $descriptionId = $description ? $controlId.'-description' : null;
    $small = $size === 'sm';
    $describedBy = FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message, $descriptionId);
    $hasText = $label || $description;
@endphp

<div x-data="{ on: @js((bool) $checked) }" x-modelable="on" {{ $binding }} {{ $attributes->only('class')->class(['flex flex-col']) }}>
    <{{ $hasText ? 'label' : 'span' }} @if ($hasText) for="{{ $controlId }}" @endif class="group flex cursor-pointer items-start gap-3 has-disabled:cursor-not-allowed">
        <span class="flex h-5 shrink-0 items-center">
            <button
                type="button"
                role="switch"
                id="{{ $controlId }}"
                x-bind:aria-checked="on ? 'true' : 'false'"
                aria-checked="{{ $checked ? 'true' : 'false' }}"
                x-on:click="on = ! on; $nextTick(() => { $el.dispatchEvent(new Event('input', { bubbles: true })); $el.dispatchEvent(new Event('change', { bubbles: true })) })"
                data-ui="switch"
                @if ($isInvalid) aria-invalid="true" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($label && $description) aria-labelledby="{{ $controlId }}-label" @endif
                {{ $attributes->except(array_merge(array_keys($binding->getAttributes()), ['class', 'id', 'name']))->class([
                    'relative inline-flex shrink-0 items-center rounded-full border transition-colors duration-150 focus-ring',
                    'disabled:cursor-not-allowed disabled:border-dashed disabled:border-control! disabled:bg-surface-subtle!',
                    $small ? 'h-4 w-7' : 'h-5 w-9',
                ]) }}
                x-bind:class="on ? 'bg-secondary border-secondary' : 'bg-surface-muted {{ $isInvalid ? 'border-danger' : 'border-control group-hover:not-disabled:border-control-hover' }}'"
            >
                <span
                    @class(['pointer-events-none block rounded-full shadow-thumb transition-transform duration-150 motion-reduce:transition-none forced-colors:bg-[CanvasText]', $small ? 'size-3' : 'size-4'])
                    x-bind:class="on ? 'bg-on-secondary {{ $small ? 'translate-x-[13px]' : 'translate-x-[17px]' }}' : 'bg-surface translate-x-px'"
                ></span>
            </button>
        </span>

        @if ($hasText)
            <span class="flex min-w-0 flex-col group-has-disabled:[&>span]:text-ink-muted">
                @if ($label)
                    <span id="{{ $controlId }}-label" class="type-body-strong select-none">{{ $label }}</span>
                @endif
                @if ($description)
                    <span id="{{ $descriptionId }}" class="type-meta">{{ $description }}</span>
                @endif
            </span>
        @endif
    </{{ $hasText ? 'label' : 'span' }}>

    @if ($fieldName && ! $binding->getAttributes())
        <input type="hidden" name="{{ $fieldName }}" x-bind:value="on ? 1 : 0" value="{{ $checked ? 1 : 0 }}">
    @endif
</div>
