{{--
    Loading state for an x-ui.chart with a title: the header row (28px), the summary
    figure (26px) and a plot block of the chart's height. x-ui.chart uses the same
    blocks for its `loading` state. Props: height (default 160 | sm 120), title (true
    keeps the header and summary rows), label.
--}}
@props(['height' => 'default', 'title' => true, 'label' => 'Loading chart'])

<div {{ $attributes }} role="status" data-skeleton="chart">
    <span class="sr-only">{{ $label }}</span>
    @if ($title)
        <div class="flex h-7 items-center" aria-hidden="true"><x-ui.skeleton class="h-2.5 w-24" /></div>
        <x-ui.skeleton.line role="figure" width="w-28" class="mt-1" />
    @endif
    <x-ui.skeleton @class([$height === 'sm' ? 'h-30' : 'h-40', 'w-full', 'mt-3' => $title]) aria-hidden="true" data-chart-skeleton />
</div>
