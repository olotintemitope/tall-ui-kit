{{-- A dashboard section: flat, a stat strip and one chart, no card grid --}}
<div class="flex flex-col gap-4">
    <x-ui.page-header title="Overview" description="Last 7 days." :level="3" />
    <x-ui.stat-strip :columns="3">
        <x-ui.stat label="Revenue" :value="1841000" money />
        <x-ui.stat label="Payments" :value="312" />
        <x-ui.stat label="Failed" :value="9" sub="2.8% of attempts" />
    </x-ui.stat-strip>
    <x-ui.chart
        type="bar"
        label="Payments per day"
        :labels="['2026-11-09', '2026-11-10', '2026-11-11', '2026-11-12', '2026-11-13', '2026-11-14', '2026-11-15']"
        :series="[['name' => 'Payments', 'token' => 'chart-1', 'points' => [38, 52, 41, 47, 60, 39, 35]]]"
    />
</div>
