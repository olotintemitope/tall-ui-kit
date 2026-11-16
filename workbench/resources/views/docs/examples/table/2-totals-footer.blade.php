{{-- Row headers and a totals footer --}}
<x-ui.table caption="Payout breakdown, November 2026" caption-visible>
    <x-slot:head>
        <tr>
            <x-ui.table.th>Item</x-ui.table.th>
            <x-ui.table.th numeric>Count</x-ui.table.th>
            <x-ui.table.th numeric>Amount</x-ui.table.th>
        </tr>
    </x-slot:head>
    <x-ui.table.row>
        <x-ui.table.th row>Campaign earnings</x-ui.table.th>
        <x-ui.table.td numeric>42</x-ui.table.td>
        <x-ui.table.td numeric><x-ui.money :value="1680000" lean /></x-ui.table.td>
    </x-ui.table.row>
    <x-ui.table.row>
        <x-ui.table.th row>Platform fee</x-ui.table.th>
        <x-ui.table.td numeric>42</x-ui.table.td>
        <x-ui.table.td numeric><x-ui.money :value="-168000" lean /></x-ui.table.td>
    </x-ui.table.row>
    <x-slot:foot>
        <tr>
            <x-ui.table.th row>Net payout</x-ui.table.th>
            <x-ui.table.td numeric></x-ui.table.td>
            <x-ui.table.td numeric><x-ui.money :value="1512000" lean /></x-ui.table.td>
        </tr>
    </x-slot:foot>
</x-ui.table>
