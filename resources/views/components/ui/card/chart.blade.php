{{--
    Chart card: the x-ui.chart anatomy (header, summary, legend, plot, data table) in a
    card. Takes every x-ui.chart prop and slot and passes them on; other attributes
    (class, data-*) go on the card. Slots: actions, summary.
--}}
@props([
    'type' => 'line', 'series' => [], 'labels' => [], 'height' => 'default', 'money' => false, 'currency' => 'NGN',
    'stacked' => false, 'yFormat' => 'number', 'empty' => 'No data in this period.', 'title' => null, 'period' => null,
    'label' => null, 'total' => null, 'previous' => null, 'deltaPeriod' => null, 'upIsGood' => true, 'legend' => true,
    'toggle' => false, 'loading' => false, 'error' => null,
])

<x-ui.card {{ $attributes->class(['p-4']) }} data-card-chart>
    <x-ui.chart
        :type="$type" :series="$series" :labels="$labels" :height="$height" :money="$money" :currency="$currency"
        :stacked="$stacked" :y-format="$yFormat" :empty="$empty" :title="$title" :period="$period" :label="$label"
        :total="$total" :previous="$previous" :delta-period="$deltaPeriod" :up-is-good="$upIsGood" :legend="$legend"
        :toggle="$toggle" :loading="$loading" :error="$error"
    >
        @isset($actions)
            <x-slot:actions>{{ $actions }}</x-slot:actions>
        @endisset
        @isset($summary)
            <x-slot:summary>{{ $summary }}</x-slot:summary>
        @endisset
    </x-ui.chart>
</x-ui.card>
