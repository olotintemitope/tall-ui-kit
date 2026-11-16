{{-- Line chart with a summary, delta and chart/table toggle --}}
<x-ui.card.chart
    type="line"
    title="Revenue"
    period="Last 7 days"
    money
    :labels="['2026-11-09', '2026-11-10', '2026-11-11', '2026-11-12', '2026-11-13', '2026-11-14', '2026-11-15']"
    :series="[['name' => 'Revenue', 'token' => 'chart-accent', 'points' => [182000, 240500, 198000, 305000, 276500, 341000, 298000]]]"
    :previous="1620000"
    delta-period="7d"
    toggle
/>
