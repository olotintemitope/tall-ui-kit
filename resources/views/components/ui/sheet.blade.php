{{--
    A side sheet on the native <dialog>: slides in from the right (200ms, ease-motion-out;
    instant under reduced motion), 480px (`md`) or 640px (`lg`), full width on phones.
    Same events, focus and busy rules as x-ui.dialog; the backdrop closes it.
    Props: name, title, description, size (md | lg), dismissible (true).
    Slots: default (scrolling body), footer.
--}}
@props(['name', 'title', 'description' => null, 'size' => 'md', 'dismissible' => true])

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" kind="sheet" :sheet-size="$size" :dismissible="$dismissible" id-prefix="sheet" {{ $attributes }}>
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog.base>
