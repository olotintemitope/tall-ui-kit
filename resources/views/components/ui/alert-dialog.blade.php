{{--
    An alert that needs acknowledging: a title, a message and one button (OK is not
    allowed; use a verb such as "Got it" only when nothing else fits, e.g. "Close").
    role="alertdialog"; focus goes to the button; Escape also acknowledges.
    Props: name, title, description, acknowledgeLabel (Close). Slot: extra body text.
--}}
@props(['name', 'title', 'description' => null, 'acknowledgeLabel' => 'Close'])

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" size="sm" role="alertdialog" :dismissible="false" :close-button="false" id-prefix="alert" data-alert-dialog {{ $attributes }}>
    {{ $slot }}
    <x-slot:footer>
        <x-ui.button size="sm" x-on:click="$dispatch('acknowledged', name); close(true)" data-dialog-acknowledge>{{ $acknowledgeLabel }}</x-ui.button>
    </x-slot:footer>
</x-ui.dialog.base>
