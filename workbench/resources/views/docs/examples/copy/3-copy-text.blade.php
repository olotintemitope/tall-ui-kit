{{-- Copy text inline, as in a table cell --}}
<x-ui.table caption="Recent payouts">
    <x-slot:head>
        <tr>
            <x-ui.table.th>Reference</x-ui.table.th>
            <x-ui.table.th>Curator</x-ui.table.th>
        </tr>
    </x-slot:head>
    <x-ui.table.row>
        <x-ui.table.td><x-ui.copy-text label="Copy reference PO-7F21C9" mono>PO-7F21C9</x-ui.copy-text></x-ui.table.td>
        <x-ui.table.td strong>Emeka Nwosu</x-ui.table.td>
    </x-ui.table.row>
    <x-ui.table.row>
        <x-ui.table.td><x-ui.copy-text label="Copy reference PO-7F21CA" mono>PO-7F21CA</x-ui.copy-text></x-ui.table.td>
        <x-ui.table.td strong>Halima Bello</x-ui.table.td>
    </x-ui.table.row>
</x-ui.table>
