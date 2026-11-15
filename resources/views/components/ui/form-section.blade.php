{{--
    Settings section: title (heading) and description (meta) on the tinted page in a
    280px left column, fields in an elevated x-ui.panel on the right (capped at 640px),
    top-aligned. Stacks below lg. Consecutive sections sit 24px apart.
    Props: title, description. Slots: default (fields), footer (panel action bar).
--}}
@props(['title', 'description' => null])

<section {{ $attributes->class(['grid grid-cols-1 items-start gap-x-8 gap-y-3 lg:grid-cols-[280px_minmax(0,1fr)]']) }}>
    <div class="lg:pt-1">
        <h2 class="type-heading">{{ $title }}</h2>
        @if ($description)
            <p class="type-meta mt-1 max-w-sm">{{ $description }}</p>
        @endif
    </div>

    <x-ui.panel class="w-full max-w-[640px]">
        {{ $slot }}

        @isset($footer)
            <x-slot:footer>{{ $footer }}</x-slot:footer>
        @endisset
    </x-ui.panel>
</section>
