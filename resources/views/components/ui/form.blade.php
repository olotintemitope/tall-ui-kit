{{--
    <form> wrapper for Livewire forms. Tracks `dirty` (any input or change; reset on
    Discard via $dispatch('form-reset') or after a successful submit) for a sticky
    x-ui.form-actions, and after a submit that comes back with errors it scrolls to and
    focuses the error summary (more than 3 errors) or the first invalid control. Pass
    wire:submit and other attributes as usual.
    Props: guard (true: an unsaved-changes guard. While dirty, closing or reloading the
    tab asks the browser's own question, and a wire:navigate link opens the kit confirm
    "Leave without saving?"; resources/js/forms.js). The form mirrors its state as
    data-dirty for the guard and for tests. dirty (true: unsaved from the start and kept
    after a successful submit, for flows whose values live only in the page, e.g. a
    wizard between steps).
--}}
@props(['guard' => false, 'dirty' => false])

<form
    novalidate
    x-data="uiForm"
    x-on:input="dirty = true"
    x-on:change="dirty = true"
    x-on:submit="submitted = true"
    x-on:form-reset="dirty = false"
    x-bind:data-dirty="dirty ? 'true' : 'false'"
    data-dirty="{{ $dirty ? 'true' : 'false' }}"
    @if ($dirty) x-init="dirty = true" data-keep-dirty @endif
    @if ($guard) data-unsaved-guard @endif
    {{ $attributes->class(['flex flex-col gap-6']) }}
>{{ $slot }}</form>

@if ($guard)
    @once
        <x-ui.confirm
            name="unsaved-changes"
            title="Leave without saving?"
            description="Your changes on this page will be lost."
            confirm-label="Leave page"
            variant="destructive"
        />
    @endonce
@endif
