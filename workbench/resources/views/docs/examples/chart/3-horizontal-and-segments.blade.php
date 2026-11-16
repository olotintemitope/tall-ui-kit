{{-- Ranked bars and a segment bar --}}
<div class="grid gap-6 lg:grid-cols-2">
    <x-ui.chart
        type="bar-horizontal"
        title="Revenue by state"
        money
        :labels="['Lagos', 'Abuja (FCT)', 'Rivers', 'Oyo', 'Kano']"
        :series="[['name' => 'Revenue', 'token' => 'chart-2', 'points' => [6420000, 3180000, 1250000, 940000, 610000]]]"
    />
    <x-ui.segment-bar
        label="Invoices by status"
        :segments="[
            ['label' => 'Paid', 'value' => 812, 'color' => 'chart-1'],
            ['label' => 'Open', 'value' => 264, 'color' => 'chart-2'],
            ['label' => 'Overdue', 'value' => 58, 'color' => 'chart-accent'],
        ]"
    />
</div>
