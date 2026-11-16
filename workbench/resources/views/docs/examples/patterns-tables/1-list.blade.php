{{-- A small list: toolbar, table with status pills, empty and loading states nearby --}}
<div class="flex flex-col gap-3">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <x-ui.search placeholder="Search invoices" width="sm" size="sm" />
        <x-ui.button variant="outline" size="sm">Export</x-ui.button>
    </div>
    <x-ui.table caption="Invoices">
        <x-slot:head>
            <x-ui.table.th>Reference</x-ui.table.th>
            <x-ui.table.th>Customer</x-ui.table.th>
            <x-ui.table.th>Status</x-ui.table.th>
            <x-ui.table.th numeric>Amount</x-ui.table.th>
        </x-slot:head>
        <x-ui.table.row>
            <x-ui.table.td mono>INV-2041</x-ui.table.td>
            <x-ui.table.td strong>Chiamaka Eze</x-ui.table.td>
            <x-ui.table.td><x-ui.badge variant="success">Paid</x-ui.badge></x-ui.table.td>
            <x-ui.table.td numeric>₦125,000.00</x-ui.table.td>
        </x-ui.table.row>
        <x-ui.table.row>
            <x-ui.table.td mono>INV-2040</x-ui.table.td>
            <x-ui.table.td strong>Tunde Bakare Enterprises</x-ui.table.td>
            <x-ui.table.td><x-ui.badge variant="warning">Overdue</x-ui.badge></x-ui.table.td>
            <x-ui.table.td numeric>₦48,500.00</x-ui.table.td>
        </x-ui.table.row>
    </x-ui.table>
    <x-ui.empty-state title="No refunds yet" description="Refunds you issue show here." />
</div>
