@php
    $payments = [
        ['ref' => 'NB-10234', 'customer' => 'Ada Obi', 'email' => 'ada@example.com', 'amount' => 125000, 'status' => ['Completed', 'success']],
        ['ref' => 'NB-10233', 'customer' => 'Tunde Bakare', 'email' => 'tunde.bakare.a.very.long.address@an-extremely-long-domain.example.com', 'amount' => 48000, 'status' => ['Pending', 'warning']],
        ['ref' => 'NB-10232', 'customer' => 'Kemi Adeyemi', 'email' => 'kemi@example.com', 'amount' => 9500, 'status' => ['Failed', 'danger']],
    ];
    $paginator = new \Illuminate\Pagination\LengthAwarePaginator(range(1, 10), 95, 10, 3, ['path' => request()->url()]);
@endphp
<x-workbench::gallery.layout title="Data display">
    <x-ui.page-header eyebrow="Gallery" title="Data display" description="Flat sections with 1px rules; cards only for peers in a grid." />

    <div class="space-y-8">
        <x-ui.stat-strip :columns="4">
            <x-ui.stat label="Revenue" :value="15250000" money sub="Last 30 days" />
            <x-ui.stat label="Payments" :value="1204" sub="Up from 1,096" />
            <x-ui.stat label="Refunds" :value="12" />
            <x-ui.stat label="Payout queue" :value="null" />
        </x-ui.stat-strip>

        <section aria-labelledby="table" class="space-y-3">
            <x-ui.section-label as="h2" id="table">Table</x-ui.section-label>
            <x-ui.table caption="Latest payments">
                <x-slot:head>
                    <tr>
                        <x-ui.table.th>Reference</x-ui.table.th>
                        <x-ui.table.th>Customer</x-ui.table.th>
                        <x-ui.table.th numeric>Amount</x-ui.table.th>
                        <x-ui.table.th>Status</x-ui.table.th>
                    </tr>
                </x-slot:head>
                @foreach ($payments as $p)
                    <x-ui.table.row wire:key="p-{{ $p['ref'] }}">
                        <x-ui.table.td mono>{{ $p['ref'] }}</x-ui.table.td>
                        <x-ui.table.td strong>{{ $p['customer'] }}<span class="block type-meta wrap-anywhere">{{ $p['email'] }}</span></x-ui.table.td>
                        <x-ui.table.td numeric><x-ui.money :value="$p['amount']" /></x-ui.table.td>
                        <x-ui.table.td><x-ui.badge :variant="$p['status'][1]">{{ $p['status'][0] }}</x-ui.badge></x-ui.table.td>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table>
            <x-ui.pagination :paginator="$paginator" />
        </section>

        <section aria-labelledby="cards" class="space-y-3">
            <x-ui.section-label as="h2" id="cards">Cards</x-ui.section-label>
            <div class="grid gap-4 md:grid-cols-3">
                <x-ui.card.summary label="Revenue" :value="15250000" money :previous="13900000" period="30d" :sparkline="[4, 6, 5, 8, 7, 9, 12]" sparkline-label="Revenue trend" />
                <x-ui.card.summary label="New artists" :value="86" :previous="94" period="30d" sub="From 14 countries" />
                <x-ui.card.link href="#cards" title="Payout settings" meta="Bank, schedule and threshold" />
            </div>
            <div class="grid gap-4 md:grid-cols-2">
                <x-ui.card.list title="Top campaigns" as="ol" view-all="#cards">
                    <x-ui.ranked-item label="Danfo Diaries" sub="Playlist" :rank="1" :value="420" :share="0.42" />
                    <x-ui.ranked-item label="Lagos After Dark" sub="Social" :rank="2" :value="310" :share="0.31" />
                    <x-ui.ranked-item label="Highlife Sunday" sub="Blog" :rank="3" :value="150" :share="0.15" />
                </x-ui.card.list>
                <x-ui.card.detail title="Payment NB-10234" status="Completed" status-variant="success" :items="[
                    ['label' => 'Customer', 'value' => 'Ada Obi'],
                    ['label' => 'Reference', 'value' => 'NB-10234', 'numeric' => true],
                    ['label' => 'Method', 'value' => 'Card'],
                    ['label' => 'Paid', 'value' => '14 Nov 2026, 09:30'],
                ]" />
            </div>
            <x-ui.card.profile name="Kemi Adeyemi" meta="Curator, Lagos" verified :stats="[['label' => 'Playlists', 'value' => '12'], ['label' => 'Followers', 'value' => '48.2k']]" />
        </section>

        <section aria-labelledby="lists" class="grid gap-8 md:grid-cols-2">
            <div class="space-y-3">
                <x-ui.section-label as="h2" id="lists">Key values and breakdowns</x-ui.section-label>
                <x-ui.kv-list>
                    <x-ui.kv label="Gross" sub="Before fees"><x-ui.money :value="15250000" /></x-ui.kv>
                    <x-ui.kv label="Fees"><x-ui.money :value="1525000" /></x-ui.kv>
                    <x-ui.kv label="Net"><x-ui.money :value="13725000" /></x-ui.kv>
                </x-ui.kv-list>
                <x-ui.segment-bar label="Payments by platform" :segments="[['label' => 'Playlist', 'value' => 620, 'color' => 'chart-1'], ['label' => 'Social', 'value' => 410, 'color' => 'chart-2'], ['label' => 'Blog', 'value' => 174, 'color' => 'chart-3']]" />
            </div>
            <div class="space-y-3">
                <x-ui.section-label as="h2">Empty, loading and error</x-ui.section-label>
                <x-ui.empty-state title="No payouts yet." description="Payouts appear here once a campaign is paid for." action-label="Read about payouts" action-href="#lists" />
                <x-ui.skeleton.table :rows="3" :columns="3" label="Loading payouts" />
                <x-ui.alert tone="danger">Payouts could not load. Try again.</x-ui.alert>
            </div>
        </section>
    </div>
</x-workbench::gallery.layout>
