{{--
    A short multi-step dialog: "Step 1 of 2" under the title, Back and Continue in the
    footer, the last step's button runs `action` (or dispatches `dialog-submitted`).
    Put each step in an element with data-dialog-step="1", "2" ... in the slot; only
    the current one shows, and focus moves to its first field. Closing with edits asks
    "Discard changes?" like x-ui.dialog.form.
    Props: name, title, description, steps (2), size (md), action, submitLabel (Finish).
--}}
@props(['name', 'title', 'description' => null, 'steps' => 2, 'size' => 'md', 'action' => null, 'submitLabel' => 'Finish'])

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" :size="$size" :dismissible="false" :steps="$steps" guard="dirty" {{ $attributes }}>
    <x-slot:header>
        <p class="type-caption mt-1" aria-live="polite" data-dialog-progress>Step <span x-text="step">1</span> of {{ $steps }}</p>
    </x-slot:header>

    <div x-effect="$el.querySelectorAll('[data-dialog-step]').forEach((el) => el.hidden = Number(el.dataset.dialogStep) !== step)">
        {{ $slot }}
    </div>

    <x-slot:footer>
        <x-ui.button variant="ghost" size="sm" class="mr-auto" x-show="step > 1" x-on:click="back()" data-dialog-back>Back</x-ui.button>
        <x-ui.button variant="outline" size="sm" x-on:click="close()" data-dialog-cancel>Cancel</x-ui.button>
        <x-ui.button size="sm" x-show="step < steps" x-on:click="next()" data-dialog-continue>Continue</x-ui.button>
        <span x-show="step === steps" x-cloak class="contents">
            <x-ui.dialog.action x-on:click="{{ $action ? 'run('.\Illuminate\Support\Js::from($action).')' : '$dispatch(\'dialog-submitted\', name); close(true)' }}">{{ $submitLabel }}</x-ui.dialog.action>
        </span>
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
