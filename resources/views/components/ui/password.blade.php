{{--
    Password input with a show/hide toggle (a real button, aria-pressed, fixed name
    "Show password") and an optional text-only strength hint (`strength`: a polite live
    caption under the field, "Weak: add length or mix letters, numbers and symbols.";
    no colour bar, never blocks submit; the server rules decide). Paste and password
    managers work as normal. Inside x-ui.field it takes the field's wiring.
    Props: autocomplete (current-password | new-password), strength (false), size,
    width (full). Attributes (wire:model, x-model) go to the <input>.
--}}
@props(['autocomplete' => 'current-password', 'strength' => false, 'size' => 'default', 'width' => 'full'])
@aware(['name' => null, 'id' => null])

@php
    $hintId = 'password-strength-'.\Illuminate\Support\Str::slug($id ?? $name ?? \TallUiKit\Support\FormControl::model($attributes) ?? 'password');
@endphp

<div x-data="uiPassword({ strength: @js((bool) $strength) })" class="flex w-full flex-col" data-password>
    <x-ui.input
        type="password"
        :width="$width"
        :size="$size"
        :autocomplete="$autocomplete"
        x-bind:type="shown ? 'text' : 'password'"
        autocapitalize="none"
        spellcheck="false"
        x-on:input="rate($event.target.value)"
        :aria-describedby="$strength ? $hintId : null"
        {{ $attributes }}
        class="max-sm:[&_input]:text-base"
    >
        <x-slot:suffix plain>
            <button
                type="button"
                class="mr-0.5 flex size-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-subtle hover:text-ink focus-ring"
                x-on:click="shown = ! shown"
                x-bind:aria-pressed="shown ? 'true' : 'false'"
                aria-pressed="false"
                aria-label="Show password"
                data-password-toggle
            >
                <x-ui.icon name="eye" class="size-4" x-show="! shown" />
                <x-ui.icon name="eye-off" class="size-4" x-show="shown" x-cloak />
            </button>
        </x-slot:suffix>
    </x-ui.input>
    @if ($strength)
        <p id="{{ $hintId }}" class="type-caption mt-1 min-h-4" aria-live="polite" x-text="hint" data-password-strength></p>
    @endif
</div>
