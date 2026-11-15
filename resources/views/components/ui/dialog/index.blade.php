{{--
    Modal dialog on the native <dialog> (showModal). Open with $dispatch('open-dialog',
    'name') or $this->dispatch('open-dialog', name: 'name'); close with close-dialog,
    Escape, × or (simple dialogs only) the backdrop. Focus goes to the first field or
    button and returns to the trigger on close (the page heading if it is gone).
    Header and footer stay put while the body scrolls; the page does not scroll behind.
    Props: name, title, description, size or width (sm 400 | md 560 | lg 720 | xl 960),
    dismissible (true). Slots: default (body), footer (actions, right-aligned), header.
    Variants: x-ui.dialog.form, x-ui.dialog.steps, x-ui.dialog.fullscreen, x-ui.sheet.
--}}
@props(['name', 'title', 'description' => null, 'width' => null, 'size' => 'md', 'dismissible' => true])

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" :size="$width ?? $size" :dismissible="$dismissible" {{ $attributes }}>
    @isset($header)
        <x-slot:header>{{ $header }}</x-slot:header>
    @endisset
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog.base>
