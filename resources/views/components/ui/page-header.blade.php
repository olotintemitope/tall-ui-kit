{{--
    Page header: optional eyebrow (label role), the page title (title role, the only
    Space Grotesk text besides figures), optional description (meta) and right-aligned
    actions. 16px below it to the first content. Props: title, eyebrow, description.
    Slot: actions.
--}}
@props(['title', 'eyebrow' => null, 'description' => null])

<header {{ $attributes->class(['flex flex-col gap-3 pb-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <x-ui.section-label class="mb-1">{{ $eyebrow }}</x-ui.section-label>
        @endif
        <h1 class="type-title min-w-0 wrap-anywhere">{{ $title }}</h1>
        @if ($description)
            <p class="type-meta mt-1">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
