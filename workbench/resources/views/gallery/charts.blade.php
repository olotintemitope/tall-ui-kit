@php
    $days = collect(range(29, 0))->map(fn ($d) => now()->subDays($d)->toDateString())->all();
    $wave = fn (int $base, int $amp, int $phase) => collect(range(0, 29))->map(fn ($i) => $base + (int) round($amp * sin(($i + $phase) / 4)) + ($i % 5) * 3)->all();
@endphp
<x-workbench::gallery.layout title="Charts">
    <x-ui.page-header eyebrow="Gallery" title="Charts" description="Server-rendered SVG and HTML. Line, bar, stacked bar, ranked bars and sparklines; nothing else." />

    <h2 class="sr-only">Chart types</h2>
    <div class="grid gap-6 lg:grid-cols-2">
        <x-ui.card.chart type="line" title="Revenue" period="Last 30 days" money :labels="$days" :series="[['name' => 'Revenue', 'token' => 'chart-accent', 'points' => $wave(120000, 40000, 0)]]" :previous="2900000" delta-period="30d" toggle />
        <x-ui.card.chart type="line" title="Sign-ups" period="Last 30 days" :labels="$days" :series="[
            ['name' => 'Artists', 'token' => 'chart-1', 'points' => $wave(40, 12, 1)],
            ['name' => 'Curators', 'token' => 'chart-2', 'points' => $wave(25, 8, 3)],
            ['name' => 'Influencers', 'token' => 'chart-3', 'points' => $wave(12, 5, 6)],
        ]" />
        <x-ui.card.chart type="bar" title="Payments" period="Last 14 days" :labels="array_slice($days, -14)" :series="[['name' => 'Payments', 'token' => 'chart-1', 'points' => array_slice($wave(30, 10, 2), -14)]]" />
        <x-ui.card.chart type="stacked-bar" title="Payments by platform" period="Last 14 days" :labels="array_slice($days, -14)" :series="[
            ['name' => 'Playlist', 'token' => 'chart-1', 'points' => array_slice($wave(15, 5, 0), -14)],
            ['name' => 'Social', 'token' => 'chart-2', 'points' => array_slice($wave(10, 4, 2), -14)],
            ['name' => 'Blog', 'token' => 'chart-3', 'points' => array_slice($wave(5, 2, 4), -14)],
        ]" />
        <x-ui.card.chart type="bar-horizontal" title="Top genres" :labels="['Afrobeats', 'Amapiano', 'Highlife', 'Gospel', 'Hip-hop']" :series="[['name' => 'Campaigns', 'token' => 'chart-2', 'points' => [420, 310, 150, 90, 60]]]" />
        <x-ui.card title="Sparklines in a table">
            <x-ui.table flush caption="Weekly trend by campaign">
                <x-slot:head><tr><x-ui.table.th>Campaign</x-ui.table.th><x-ui.table.th>Trend</x-ui.table.th></tr></x-slot:head>
                <x-ui.table.row><x-ui.table.td strong>Danfo Diaries</x-ui.table.td><x-ui.table.td><x-ui.chart.sparkline :points="[3, 5, 4, 7, 6, 9, 11]" label="Danfo Diaries, rising" /></x-ui.table.td></x-ui.table.row>
                <x-ui.table.row><x-ui.table.td strong>Lagos After Dark</x-ui.table.td><x-ui.table.td><x-ui.chart.sparkline :points="[9, 8, 8, 6, 5, 5, 4]" label="Lagos After Dark, falling" /></x-ui.table.td></x-ui.table.row>
            </x-ui.table>
        </x-ui.card>
    </div>
</x-workbench::gallery.layout>
