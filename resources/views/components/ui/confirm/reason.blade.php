{{--
    Confirmation that needs a reason (suspend, pause, reject): a required reason select
    and, when "Other" is chosen, a required text field. The action receives the reason
    (the option value, or the typed text for Other) as its first argument; the server
    validates it again. Errors show on the field and move focus to it.
    Props: name, title, description, confirmLabel (a verb), action, reasons (value =>
    label; "other" is added), variant (default | destructive), otherLabel.
--}}
@props(['name', 'title', 'description' => null, 'confirmLabel' => 'Confirm', 'action' => null, 'reasons' => [], 'variant' => 'default', 'otherLabel' => 'Describe the reason'])

@php
    $selectId = 'confirm-'.$name.'-reason';
    $otherId = 'confirm-'.$name.'-other';
    $errorId = 'confirm-'.$name.'-reason-error';
    $run = $action
        ? 'const chosen = chosenReason(); if (chosen !== null) run('.\Illuminate\Support\Js::from($action).', chosen)'
        : 'const chosen = chosenReason(); if (chosen !== null) { $dispatch(\'confirmed\', { name, reason: chosen }); close(true) }';
@endphp

<x-ui.dialog.base
    :name="$name"
    :title="$title"
    :description="$description"
    size="sm"
    role="alertdialog"
    :dismissible="false"
    :aliases="[$name]"
    id-prefix="confirm"
    data-confirm="reason"
    {{ $attributes }}
>
    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1.5">
            <label for="{{ $selectId }}" id="{{ $selectId }}-label" class="type-field-label">Reason <span class="text-ink-muted">*</span></label>
            <x-ui.select
                :id="$selectId"
                width="full"
                placeholder="Choose a reason"
                :options="$reasons + ['other' => 'Other']"
                x-model="reason"
                x-on:change="reasonError = ''"
                aria-required="true"
                x-bind:aria-invalid="reasonError && reason === '' ? 'true' : null"
                x-bind:aria-describedby="reasonError && reason === '' ? '{{ $errorId }}' : null"
                data-reason-select
            />
            <p id="{{ $errorId }}" x-show="reasonError && reason === ''" x-cloak class="type-caption text-danger-text!" x-text="reasonError"></p>
        </div>

        <div x-show="reason === 'other'" x-cloak class="flex flex-col gap-1.5">
            <label for="{{ $otherId }}" class="type-field-label">{{ $otherLabel }} <span class="text-ink-muted">*</span></label>
            <textarea
                id="{{ $otherId }}"
                x-model="reasonOther"
                x-on:input="reasonError = ''"
                rows="3"
                maxlength="500"
                aria-required="true"
                x-bind:aria-invalid="reasonError && reason === 'other' ? 'true' : null"
                x-bind:aria-describedby="reasonError && reason === 'other' ? @js($errorId.'-other') : null"
                data-ui="textarea"
                data-reason-other
                class="w-full rounded-md border border-control bg-surface px-3 py-2 type-body text-ink focus-visible:border-focus focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/20 aria-invalid:border-danger"
            ></textarea>
            <p id="{{ $errorId }}-other" x-show="reasonError && reason === 'other'" x-cloak class="type-caption text-danger-text!" x-text="reasonError"></p>
        </div>
    </div>

    <x-slot:footer>
        <x-ui.button variant="outline" size="sm" x-on:click="close()" data-dialog-cancel>Cancel</x-ui.button>
        <x-ui.dialog.action :variant="$variant === 'destructive' ? 'destructive' : 'default'" x-on:click="{{ $run }}" data-confirm-action>{{ $confirmLabel }}</x-ui.dialog.action>
    </x-slot:footer>
</x-ui.dialog.base>
