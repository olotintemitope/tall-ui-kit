{{--
    Page header: optional eyebrow (label role), the page title (title role, the only
    Space Grotesk text besides figures), optional description (meta) and right-aligned
    actions. 16px below it to the first content.

    Props:
      title, eyebrow, description
      level        heading level (1; 2 for a header inside a page that already has its h1)
      breadcrumbs  list of ['label' => ..., 'href' => ...] drawn above the header, on the
                   page, 12px above it (the last item is the current page)
      breadcrumbLabel  the breadcrumb nav's name ("Breadcrumb")
      boxed        put the title and actions on a raised, borderless surface (the
                   portal style used with x-ui.page); no bottom padding of its own
      moreLabel    name of the overflow button ("More actions")
    Slots:
      actions  one primary and at most one secondary button
      more     every other action, as x-ui.dropdown.item rows; drawn as an ellipsis
               menu before the actions, so the header never carries a row of buttons

    Actions keep their natural width from sm and the title column takes the squeeze
    (it wraps); below sm the actions wrap under the title instead of pushing a narrow
    page sideways.
--}}
@props([
    'title',
    'eyebrow' => null,
    'description' => null,
    'level' => 1,
    'breadcrumbs' => [],
    'breadcrumbLabel' => 'Breadcrumb',
    'boxed' => false,
    'moreLabel' => 'More actions',
])

@php
    $level = max(1, min(6, (int) $level));
    $hasCrumbs = ! empty($breadcrumbs);
    $headerClass = [
        'flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between',
        'pb-4' => ! $boxed,
        'rounded-lg bg-card p-4 shadow-panel sm:p-5' => $boxed,
    ];
@endphp

@if ($hasCrumbs)
<div {{ $attributes->class(['grid min-w-0 gap-3']) }} data-page-header>
    <x-ui.breadcrumb :items="$breadcrumbs" :label="$breadcrumbLabel" />
    <header @class($headerClass)>
@else
    <header {{ $attributes->class($headerClass) }} data-page-header>
@endif
        <div class="min-w-0 sm:flex-1">
            @if ($eyebrow)
                <x-ui.section-label class="mb-1">{{ $eyebrow }}</x-ui.section-label>
            @endif
            <h{{ $level }} class="type-title min-w-0 wrap-anywhere">{{ $title }}</h{{ $level }}>
            @if ($description)
                <p class="type-meta mt-1">{{ $description }}</p>
            @endif
        </div>

        @if (isset($actions) || isset($more))
            <div class="flex min-w-0 flex-wrap items-center gap-2 sm:shrink-0" data-page-header-actions>
                @isset($more)
                    <x-ui.dropdown placement="bottom-end">
                        <x-slot:trigger>
                            <x-ui.button variant="outline" size="icon" :aria-label="$moreLabel"><x-ui.icon name="ellipsis" /></x-ui.button>
                        </x-slot:trigger>
                        {{ $more }}
                    </x-ui.dropdown>
                @endisset
                {{ $actions ?? '' }}
            </div>
        @endif
    </header>
@if ($hasCrumbs)
</div>
@endif
