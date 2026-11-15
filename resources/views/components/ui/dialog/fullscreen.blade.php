{{--
    A dialog that fills the screen on phones (below 640px: no radius, edge to edge,
    slides up 16px) and is a normal centred dialog from `sm`. For long forms or
    pickers that need the whole phone screen. Props as x-ui.dialog.
--}}
@props(['name', 'title', 'description' => null, 'size' => 'lg', 'dismissible' => false])

<x-ui.dialog.base :name="$name" :title="$title" :description="$description" :size="$size" kind="fullscreen" :dismissible="$dismissible" {{ $attributes }}>
    {{ $slot }}
    @isset($footer)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @endisset
</x-ui.dialog.base>
