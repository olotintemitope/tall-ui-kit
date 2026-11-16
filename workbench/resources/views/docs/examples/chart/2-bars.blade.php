{{-- Grouped and stacked bars --}}
<div class="grid gap-4 lg:grid-cols-2">
    <x-ui.card.chart
        type="bar"
        title="Invoices"
        period="Last 6 months"
        :labels="['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov']"
        :series="[
            ['name' => 'Sent', 'token' => 'chart-1', 'points' => [120, 134, 141, 128, 156, 162]],
            ['name' => 'Paid', 'token' => 'chart-2', 'points' => [98, 120, 119, 115, 140, 131]],
        ]"
        legend
    />
    <x-ui.card.chart
        type="stacked-bar"
        title="Payments by method"
        period="Last 6 months"
        :labels="['Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov']"
        :series="[
            ['name' => 'Card', 'token' => 'chart-1', 'points' => [60, 64, 70, 66, 75, 79]],
            ['name' => 'Bank transfer', 'token' => 'chart-2', 'points' => [30, 41, 38, 36, 48, 40]],
            ['name' => 'USSD', 'token' => 'chart-3', 'points' => [8, 15, 11, 13, 17, 12]],
        ]"
        legend
    />
</div>
