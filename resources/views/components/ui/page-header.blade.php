{{--
    Page header: optional eyebrow (label role), the page title (title role, the only
    Space Grotesk text besides figures), optional description (meta) and right-aligned
    actions (they wrap onto a second line rather than push a narrow page sideways). 16px
    below it to the first content. Props: title, eyebrow, description, level (1: the
    heading level; 2 for a header inside a page that already has its h1).
    Slot: actions.
--}}
@props(['title', 'eyebrow' => null, 'description' => null, 'level' => 1])

@php $level = max(1, min(6, (int) $level)); @endphp

<header {{ $attributes->class(['flex flex-col gap-3 pb-4 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <x-ui.section-label class="mb-1">{{ $eyebrow }}</x-ui.section-label>
        @endif
        <h{{ $level }} class="type-title min-w-0 wrap-anywhere">{{ $title }}</h{{ $level }}>
        @if ($description)
            <p class="type-meta mt-1">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex min-w-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</header>
