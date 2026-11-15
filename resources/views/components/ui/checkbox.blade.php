{{--
    Checkbox row: the box on the left, a label (body-strong) and optional description
    (meta) on the right; the whole row is the click target. Props: label, description,
    invalid, indeterminate (sets the native mixed state on load: an ink box with a white
    dash, announced as "mixed"; for a live value bind
    x-effect="$el.indeterminate = someCondition" on the component). Attributes (wire:model, x-model, value, checked, disabled, name) go to the
    <input>; errors are looked up by name or wire:model.
--}}
@props(['label' => null, 'description' => null, 'invalid' => null, 'indeterminate' => false])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $attributes->get('name') ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id'), $fieldName.($attributes->has('value') ? '-'.$attributes->get('value') : '')) ?? 'checkbox-'.uniqid();
    $message = FormControl::error($errors ?? null, $fieldName);
    $isInvalid = $invalid ?? (bool) $message;
    $descriptionId = $description ? $controlId.'-description' : null;
@endphp

<div {{ $attributes->only('class')->class(['flex flex-col']) }} @if ($message) data-invalid @endif>
    <label for="{{ $controlId }}" class="group flex cursor-pointer items-start gap-3 has-disabled:cursor-not-allowed">
        <span class="relative flex h-5 w-4 shrink-0 items-center justify-center">
            <input {{ $attributes->except(['class', 'id'])->merge([
                'type' => 'checkbox',
                'id' => $controlId,
                'aria-invalid' => $isInvalid ? 'true' : null,
                'aria-describedby' => FormControl::describedBy($controlId, false, (bool) $message, $descriptionId),
                'data-ui' => 'checkbox',
                'x-init' => $indeterminate ? '$el.indeterminate = true' : null,
                'data-indeterminate' => $indeterminate ? 'true' : null,
            ])->class([
                'peer size-4 cursor-pointer appearance-none forced-colors:appearance-auto rounded-sm border bg-white transition-colors duration-100',
                'checked:border-secondary checked:bg-secondary indeterminate:border-secondary indeterminate:bg-secondary',
                'disabled:indeterminate:border-solid disabled:indeterminate:border-ink-muted disabled:indeterminate:bg-ink-muted',
                'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus',
                'disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:checked:border-solid disabled:checked:border-ink-muted disabled:checked:bg-ink-muted',
                'border-red-600' => $isInvalid,
                'border-control group-hover:border-control-hover' => ! $isInvalid,
            ]) }} />
            <x-ui.icon name="check" :stroke-width="3" class="pointer-events-none absolute size-3 text-white opacity-0 peer-checked:opacity-100 peer-indeterminate:opacity-0! forced-colors:hidden" />
            <x-ui.icon name="minus" :stroke-width="3" class="pointer-events-none absolute size-3 text-white opacity-0 peer-indeterminate:opacity-100 forced-colors:hidden" data-checkbox-dash />
        </span>

        @if ($label || $description)
            <span class="flex min-w-0 flex-col group-has-disabled:[&>span]:text-ink-muted">
                @if ($label)
                    <span class="type-body-strong select-none">{{ $label }}</span>
                @endif
                @if ($description)
                    <span id="{{ $descriptionId }}" class="type-meta">{{ $description }}</span>
                @endif
            </span>
        @endif
    </label>

    @if ($message)
        <p id="{{ FormControl::errorId($controlId) }}" class="type-caption text-red-700! mt-1 pl-7" aria-live="polite">{{ $message }}</p>
    @endif
</div>
