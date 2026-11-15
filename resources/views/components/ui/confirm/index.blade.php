{{--
    Confirmation: the title is a question ("Pause this campaign?"), one consequence
    line, Cancel and a verb (never Yes or OK). Open with $dispatch('open-dialog', name)
    (or the older open-confirm). With `action` (a Livewire method) the confirm button
    runs it with the dialog busy: it cannot be closed mid-action, it closes on success
    and shows an error inline when the method returns a string or throws. Without
    `action` it dispatches `confirmed` with the name. The server method re-checks
    permissions. The backdrop does not close it.
    Props: name, title, description (the consequence), confirmLabel (a verb), action,
    params (arguments for the action), variant (default | destructive), initialFocus
    (first | cancel). Slot: extra body content (x-ui.confirm.destructive and .reason use it).
--}}
@props(['name', 'title', 'description' => null, 'confirmLabel' => 'Confirm', 'action' => null, 'params' => [], 'variant' => 'default', 'initialFocus' => null, 'canConfirm' => 'true'])

@php
    $run = $action
        ? 'run('.collect([$action, ...$params])->map(fn ($v) => (string) \Illuminate\Support\Js::from($v))->implode(', ').')'
        : '$dispatch(\'confirmed\', name); close(true)';
    $destructive = $variant === 'destructive';
@endphp

<x-ui.dialog.base
    :name="$name"
    :title="$title"
    :description="$description"
    size="sm"
    role="alertdialog"
    :dismissible="false"
    :initial-focus="$initialFocus ?? ($destructive ? 'cancel' : 'first')"
    :aliases="[$name]"
    id-prefix="confirm"
    data-confirm="{{ $variant }}"
    {{ $attributes }}
>
    @if (trim($slot) !== '')
        <div class="flex flex-col gap-4">{{ $slot }}</div>
    @endif

    <x-slot:footer>
        <x-ui.button variant="outline" size="sm" x-on:click="close()" x-bind:aria-disabled="busy ? 'true' : null" data-dialog-cancel>Cancel</x-ui.button>
        <x-ui.dialog.action :variant="$destructive ? 'destructive' : 'default'" x-bind:aria-disabled="busy || ! ({{ $canConfirm }}) ? 'true' : null" x-on:click="if ({{ $canConfirm }}) {{ $run }}" data-confirm-action>{{ $confirmLabel }}</x-ui.dialog.action>
    </x-slot:footer>
</x-ui.dialog.base>
