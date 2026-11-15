{{--
    Radio buttons in a fieldset, stacked with 12px gaps. Each option is a row whose whole
    area is the click target: the radio, then label (body-strong) and description (meta).
    Props: name (input name and error key), label (legend), hint, options (value => label,
    or a list of ['value', 'label', 'description', 'disabled']), selected (initial value
    without wire:model), variant (list | cards: bordered plan-style choices, 1px `control`
    border (3.65:1); selected gets a 1px `primary-text` border (5.21:1) and a primary/5
    tint, and its radio fills ink (15.9:1)), orientation (vertical|horizontal, list only),
    required, invalid, error (an explicit message, as on x-ui.field; otherwise
    $errors->first(name)). wire:model / x-model are applied to every radio. A checked radio
    is an ink fill with a white dot, never a thick border.
--}}
@props(['name', 'label' => null, 'hint' => null, 'options' => [], 'selected' => null, 'variant' => 'list', 'orientation' => 'vertical', 'required' => false, 'invalid' => null, 'error' => null])

@php
    use TallUiKit\Support\FormControl;

    $groupId = FormControl::id($attributes->get('id'), $name);
    $message = FormControl::error($errors ?? null, $name, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $binding = $attributes->filter(fn ($value, $key) => str_starts_with($key, 'wire:model') || $key === 'x-model' || str_starts_with($key, 'x-model.'));
    $cards = $variant === 'cards';

    $normalised = collect($options)->map(fn ($option, $key) => is_array($option)
        ? ['value' => (string) $option['value'], 'label' => $option['label'], 'description' => $option['description'] ?? null, 'disabled' => $option['disabled'] ?? false]
        : ['value' => (string) $key, 'label' => $option, 'description' => null, 'disabled' => false])->values();
@endphp

<fieldset
    {{ $attributes->except(array_merge(array_keys($binding->getAttributes()), ['id', 'disabled']))->class(['min-w-0']) }}
    id="{{ $groupId }}"
    role="radiogroup"
    @if ($attributes->has('disabled')) disabled @endif
    @if ($isInvalid) aria-invalid="true" data-invalid @endif
    @if ($required) aria-required="true" @endif
    @if (($hint && ! $message) || $message) aria-describedby="{{ FormControl::describedBy($groupId, filled($hint) && ! $message, (bool) $message) }}" @endif
>
    @if ($label)
        <legend class="type-field-label mb-1.5">{{ $label }}@if ($required)<span class="text-ink-muted" aria-hidden="true"> *</span>@endif</legend>
    @endif

    <div @class([
        'flex',
        'flex-col gap-3' => $orientation !== 'horizontal' || $cards,
        'flex-wrap gap-x-6 gap-y-3' => $orientation === 'horizontal' && ! $cards,
        'pt-0.5' => $label && ! $cards,
    ])>
        @foreach ($normalised as $option)
            @php $optionId = $groupId.'-'.\Illuminate\Support\Str::slug($option['value'] === '' ? 'none' : $option['value']); @endphp
            <label for="{{ $optionId }}" @class([
                'group flex cursor-pointer items-start gap-3 has-disabled:cursor-not-allowed',
                'rounded-md border bg-surface p-3 transition-colors duration-100 has-checked:border-primary-text! has-checked:bg-primary/5 has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-focus' => $cards,
                'border-danger' => $cards && $isInvalid,
                'border-control hover:border-control-hover' => $cards && ! $isInvalid,
            ])>
                <span class="relative flex h-5 w-4 shrink-0 items-center justify-center">
                    <input
                        type="radio"
                        id="{{ $optionId }}"
                        name="{{ $name }}"
                        value="{{ $option['value'] }}"
                        data-ui="radio"
                        @checked($selected !== null && (string) $selected === $option['value'])
                        @disabled($option['disabled'])
                        @if ($option['description']) aria-labelledby="{{ $optionId }}-label" aria-describedby="{{ $optionId }}-description" @endif
                        {{ $binding }}
                        @class([
                            'peer size-4 cursor-pointer appearance-none forced-colors:appearance-auto rounded-full border bg-surface transition-colors duration-100',
                            'checked:border-secondary checked:bg-secondary',
                            'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-focus' => ! $cards,
                            'focus-visible:outline-none' => $cards,
                            'disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:checked:border-solid disabled:checked:border-ink-muted disabled:checked:bg-ink-muted',
                            'border-danger' => $isInvalid,
                            'border-control group-hover:border-control-hover' => ! $isInvalid,
                        ])
                    />
                    <span class="pointer-events-none absolute size-1.5 rounded-full bg-on-secondary opacity-0 peer-checked:opacity-100 forced-colors:hidden" aria-hidden="true"></span>
                </span>
                <span class="flex min-w-0 flex-col group-has-disabled:[&>span]:text-ink-muted">
                    <span id="{{ $optionId }}-label" class="type-body-strong select-none">{{ $option['label'] }}</span>
                    @if ($option['description'])
                        <span id="{{ $optionId }}-description" class="type-meta">{{ $option['description'] }}</span>
                    @endif
                </span>
            </label>
        @endforeach
    </div>

    @if ($message)
        <p id="{{ FormControl::errorId($groupId) }}" class="type-caption text-danger-text! mt-1" aria-live="polite">{{ $message }}</p>
    @elseif ($hint)
        <p id="{{ FormControl::hintId($groupId) }}" class="type-caption mt-1">{{ $hint }}</p>
    @endif
</fieldset>
