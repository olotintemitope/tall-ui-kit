{{--
    A short multi-step dialog: "Step 1 of 2" under the title, Back and Continue in the
    footer, the last step's button runs `action` (or dispatches `dialog-submitted`).
    Put each step in an element with data-dialog-step="1", "2" ... in the slot; only
    the current one shows, and focus moves to its first field. Closing with edits asks
    "Discard changes?" like x-ui.dialog.form.
    Props: name, title, description, steps (2), size (md), action, submitLabel (Finish).
--}}
@props(['name', 'title', 'description' => null, 'steps' => 2, 'size' => 'md', 'action' => null, 'submitLabel' => 'Finish'])

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" :size="$size" :dismissible="false" :steps="$steps" {{ $attributes }}>
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
</x-ui.dialog.base>
