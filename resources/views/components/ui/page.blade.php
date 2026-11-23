{{--
    A portal page: the tinted level-0 page and one centred content column, so every
    route in an app shares the same width, gutters and rhythm. Start it with an
    x-ui.page-header (usually `boxed` with `breadcrumbs`); sections inside are 16px
    apart, the kit's header-to-content rhythm.

    Props:
      width  full (default): lists, details, dashboards, analytics, workbenches.
             form: create and edit forms (a 48rem column).
    The column is marked data-page-shell="full|form" so layout audits can check that
    content fills it. One root element, so it can be a Livewire component's root.
    Other attributes (wire:poll, x-data, style) go on the column.
--}}
@props(['width' => 'full'])

@php $form = $width === 'form'; @endphp

<div class="min-h-screen bg-page-muted" data-page>
    <main data-page-shell="{{ $form ? 'form' : 'full' }}" {{ $attributes->class([
        'mx-auto flex w-full min-w-0 flex-col gap-4 px-4 pt-4 pb-8 sm:px-6 lg:px-8',
        'max-w-7xl' => ! $form,
        'max-w-3xl' => $form,
    ]) }}>
        {{ $slot }}
    </main>
</div>
