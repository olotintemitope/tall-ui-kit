{{--
    Combobox: an editable input (role="combobox", aria-autocomplete="list",
    aria-expanded, aria-controls) with a listbox popup (role="listbox", options with
    aria-selected). Focus stays in the input; the active option is announced through
    aria-activedescendant. Typing filters by substring (type-ahead) and a polite status
    says how many options match. Down/Up move (opening it first), Alt+Down opens, Enter
    picks, Escape closes (a second Escape clears), Tab leaves and keeps the choice. A
    click on the chevron opens and closes it. Bind wire:model / x-model on the
    component (the option value, or null).

    Native alternative: `native` renders <input list> with a <datalist> instead, for a
    free-text field with suggestions where the browser's own popup is acceptable (it
    cannot be styled, and Safari and Firefox differ). Use the Alpine combobox when the
    value must be one of the options or the list needs hints.

    Props: options (value => label, or a list of ['value', 'label', 'hint', 'disabled']),
    value (initial), placeholder, size, width (md), invalid, label (accessible name
    outside x-ui.field), allowCustom (keep typed text that matches no option), empty
    ("No matches"), native.
--}}
@props([
    'options' => [], 'value' => null, 'placeholder' => null, 'size' => 'default', 'width' => null, 'invalid' => null,
    'label' => null, 'allowCustom' => false, 'empty' => 'No matches', 'native' => false,
])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'combobox-'.uniqid();
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $list = collect($options)->map(fn ($option, $key) => is_array($option)
        ? ['value' => (string) $option['value'], 'label' => $option['label'], 'hint' => $option['hint'] ?? null, 'disabled' => (bool) ($option['disabled'] ?? false)]
        : ['value' => (string) $key, 'label' => $option, 'hint' => null, 'disabled' => false])->values();
    $describedBy = FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message);
@endphp

@if ($native)
    <x-ui.input :size="$size" :width="$width" :invalid="$invalid" :id="$controlId" list="{{ $controlId }}-list" :placeholder="$placeholder" autocomplete="off" :aria-label="$label" {{ $attributes }} data-combobox="native" />
    <datalist id="{{ $controlId }}-list">
        @foreach ($list as $option)
            <option value="{{ $option['label'] }}"></option>
        @endforeach
    </datalist>
@else
<div
    x-data="uiCombobox({ options: @js($list), value: @js($value === null ? null : (string) $value), allowCustom: @js((bool) $allowCustom), id: @js($controlId) })"
    x-modelable="value"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    x-on:focusout="blur($event)"
    {{ $attributes->only('class')->class(['relative', FormControl::width($width, 'md')]) }}
    data-combobox
>
    <div @class([
        'flex items-stretch overflow-hidden rounded-md border bg-white transition-[border-color,box-shadow] duration-100',
        $size === 'sm' ? 'h-8' : 'h-9',
        'border-red-600 focus-within:ring-2 focus-within:ring-red-600/20' => $isInvalid,
        'border-control hover:border-control-hover focus-within:border-focus focus-within:ring-2 focus-within:ring-primary/20' => ! $isInvalid,
        'has-disabled:bg-surface-subtle has-disabled:cursor-not-allowed has-disabled:border-dashed',
    ]) data-control-box>
        <input
            x-ref="input"
            type="text"
            id="{{ $controlId }}"
            role="combobox"
            aria-autocomplete="list"
            aria-controls="{{ $controlId }}-listbox"
            x-bind:aria-expanded="open ? 'true' : 'false'"
            aria-expanded="false"
            x-bind:aria-activedescendant="activeId"
            autocomplete="off"
            spellcheck="false"
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($label) aria-label="{{ $label }}" @endif
            @if ($isInvalid) aria-invalid="true" @endif
            @if ($required) aria-required="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            x-model="query"
            x-on:input="input()"
            x-on:click="open || show()"
            x-on:keydown.down.prevent="$event.altKey ? show() : move(1)"
            x-on:keydown.up.prevent="move(-1)"
            x-on:keydown.enter="enter($event)"
            x-on:keydown.escape="escape($event)"
            x-on:keydown.tab="open && active >= 0 && query.trim() !== '' && results[active] && results[active].label.toLowerCase().startsWith(query.trim().toLowerCase()) ? pick(active) : null"
            data-ui="combobox"
            {{ $attributes->except(['class', 'id'])->whereDoesntStartWith(['wire:model', 'x-model']) }}
            class="peer min-w-0 flex-1 bg-transparent pl-3 font-body text-body text-ink placeholder:text-ink-muted outline-none disabled:cursor-not-allowed disabled:text-ink-muted"
        />
        <button type="button" tabindex="-1" aria-hidden="true" x-on:mousedown.prevent x-on:click="open ? close() : (show(), $refs.input.focus())" class="flex w-8 shrink-0 items-center justify-center text-ink-muted hover:text-ink">
            <x-ui.icon name="chevron-down" class="size-4 transition-transform duration-150 motion-reduce:transition-none" x-bind:class="open && 'rotate-180'" />
        </button>
    </div>

    <ul
        id="{{ $controlId }}-listbox"
        role="listbox"
        @if ($label) aria-label="{{ $label }}" @else aria-labelledby="{{ $controlId }}-label" @endif
        x-show="open"
        x-cloak
        x-effect="open && $revealBelow($el, $el.parentElement.querySelector('[role=combobox]'))"
        x-transition:enter="transition ease-motion-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-motion-out duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="absolute inset-x-0 top-full z-40 mt-1.5 max-h-64 overflow-y-auto rounded-lg border border-panel-border bg-white p-1 shadow-popover"
        data-combobox-listbox
    >
        <template x-for="(option, index) in results" :key="option.value">
            <li
                role="option"
                x-bind:id="optionId(index)"
                x-bind:aria-selected="option.value === value ? 'true' : 'false'"
                x-bind:aria-disabled="option.disabled ? 'true' : null"
                x-on:mousedown.prevent
                x-on:click="pick(index)"
                x-on:mousemove="active = index"
                x-bind:class="{ 'bg-surface-subtle': index === active, 'cursor-not-allowed text-ink-muted': option.disabled, 'cursor-pointer': ! option.disabled }"
                class="flex min-h-8 items-center gap-2 rounded-md px-2 py-1"
            >
                <span class="flex size-4 shrink-0 items-center justify-center"><x-ui.icon name="check" class="size-4 text-ink" x-show="option.value === value" /></span>
                <span class="flex min-w-0 flex-1 flex-col">
                    <span class="type-body text-ink wrap-anywhere" x-text="option.label"></span>
                    <span class="type-caption wrap-anywhere" x-show="option.hint" x-text="option.hint"></span>
                </span>
            </li>
        </template>
        <li x-show="results.length === 0" class="type-meta px-2 py-1.5" role="presentation">{{ $empty }}</li>
    </ul>
    <span class="sr-only" aria-live="polite" x-text="status" data-combobox-status></span>
</div>
@endif
