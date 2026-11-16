{{-- A strip of metrics --}}
<x-ui.stat-strip :columns="4">
    <x-ui.stat label="Revenue" :value="15250000" money sub="Last 30 days" />
    <x-ui.stat label="Invoices paid" :value="1204" sub="Of 1,312 sent" />
    <x-ui.stat label="Refunds" :value="12" />
    <x-ui.stat label="Payout queue" :value="null" sub="Syncing" />
</x-ui.stat-strip>
