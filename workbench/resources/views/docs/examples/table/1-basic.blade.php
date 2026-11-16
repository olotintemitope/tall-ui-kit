{{-- Basic table: cell roles and a status column --}}
<x-ui.table caption="Latest invoices">
    <x-slot:head>
        <tr>
            <x-ui.table.th>Reference</x-ui.table.th>
            <x-ui.table.th>Customer</x-ui.table.th>
            <x-ui.table.th>Issued</x-ui.table.th>
            <x-ui.table.th numeric>Amount</x-ui.table.th>
            <x-ui.table.th>Status</x-ui.table.th>
        </tr>
    </x-slot:head>
    <x-ui.table.row>
        <x-ui.table.td mono>INV-2041</x-ui.table.td>
        <x-ui.table.td strong>Zenith Foods</x-ui.table.td>
        <x-ui.table.td muted>14 Nov 2026</x-ui.table.td>
        <x-ui.table.td numeric><x-ui.money :value="1250000" lean /></x-ui.table.td>
        <x-ui.table.td><x-ui.badge variant="warning">Open</x-ui.badge></x-ui.table.td>
    </x-ui.table.row>
    <x-ui.table.row>
        <x-ui.table.td mono>INV-2040</x-ui.table.td>
        <x-ui.table.td wrap><span class="type-body-strong text-ink">Oluwaseun Adebayo-Williams</span><span class="block type-meta wrap-break-word">accounts@adebayo-williams-logistics.example.com</span></x-ui.table.td>
        <x-ui.table.td muted>12 Nov 2026</x-ui.table.td>
        <x-ui.table.td numeric><x-ui.money :value="480000" lean /></x-ui.table.td>
        <x-ui.table.td><x-ui.badge variant="success">Paid</x-ui.badge></x-ui.table.td>
    </x-ui.table.row>
</x-ui.table>
