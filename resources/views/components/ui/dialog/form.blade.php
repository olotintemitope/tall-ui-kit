{{--
    A dialog around a form. Enter submits (a real <form>; the footer button submits it
    through form=""). Save runs `action` (a Livewire method) with the dialog busy:
    Escape and × wait, the button shows a spinner. It closes when the method returns
    anything but false or a string, and no field is invalid; a string is shown inline
    as the error. Closing with unsaved edits asks "Discard changes?" (a confirm over
    the form, the one allowed nested modal). The backdrop does not close it.
    Props: name, title, description, size (md), action, submitLabel (Save changes),
    cancelLabel (Cancel), submitVariant (default). Slot: the fields. Bind fields with
    wire:model; reset them on `dialog-discarded` if needed.
--}}
@props(['name', 'title', 'description' => null, 'size' => 'md', 'action' => null, 'submitLabel' => 'Save changes', 'cancelLabel' => 'Cancel', 'submitVariant' => 'default'])

@php $formId = 'dialog-'.$name.'-form'; @endphp

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" :size="$size" :dismissible="false" guard="dirty" {{ $attributes }}>
    <form id="{{ $formId }}" class="flex flex-col gap-4" x-on:submit.prevent="{{ $action ? 'run('.\Illuminate\Support\Js::from($action).')' : '$dispatch(\'dialog-submitted\', name); dirty = false; close(true)' }}" novalidate data-dialog-form>
        {{ $slot }}
    </form>

    <x-slot:footer>
        <x-ui.button variant="outline" size="sm" x-on:click="close()" data-dialog-cancel>{{ $cancelLabel }}</x-ui.button>
        <x-ui.dialog.action :variant="$submitVariant" type="submit" :form="$formId">{{ $submitLabel }}</x-ui.dialog.action>
    </x-slot:footer>

    <x-slot:after>
        <dialog x-ref="discard" wire:ignore.self role="alertdialog" aria-labelledby="dialog-{{ $name }}-discard-title" aria-describedby="dialog-{{ $name }}-discard-description" x-on:cancel.prevent="keepEditing()" class="ui-dialog mx-auto mt-[16vh] h-fit w-[calc(100%-2rem)] max-w-[400px] rounded-lg border border-panel-border bg-popover p-0 shadow-dialog" data-dialog-discard>
            <div class="px-5 pt-5 pb-4">
                <h2 id="dialog-{{ $name }}-discard-title" class="type-heading">Discard changes?</h2>
                <p id="dialog-{{ $name }}-discard-description" class="type-body mt-1.5">Your edits in "{{ $title }}" will be lost.</p>
            </div>
            <div class="flex justify-end gap-2 border-t border-line px-5 py-3" data-dialog-footer>
                <x-ui.button variant="outline" size="sm" x-on:click="keepEditing()" data-dialog-cancel>Keep editing</x-ui.button>
                <x-ui.button variant="destructive" size="sm" x-on:click="discard()">Discard</x-ui.button>
            </div>
        </dialog>
    </x-slot:after>
</x-ui.dialog.base>
