{{-- Sticky header inside the table's own scroll box --}}
<x-ui.table caption="Settlement log" sticky max-height="max-h-64">
    <x-slot:head>
        <tr>
            <x-ui.table.th>Batch</x-ui.table.th>
            <x-ui.table.th>Settled</x-ui.table.th>
            <x-ui.table.th numeric>Payments</x-ui.table.th>
            <x-ui.table.th numeric>Amount</x-ui.table.th>
        </tr>
    </x-slot:head>
    @foreach (range(1, 12) as $day)
        <x-ui.table.row>
            <x-ui.table.td mono>STL-{{ 4800 + $day }}</x-ui.table.td>
            <x-ui.table.td muted>{{ $day }} Nov 2026</x-ui.table.td>
            <x-ui.table.td numeric>{{ 120 + $day * 7 }}</x-ui.table.td>
            <x-ui.table.td numeric><x-ui.money :value="2400000 + $day * 135000" lean /></x-ui.table.td>
        </x-ui.table.row>
    @endforeach
</x-ui.table>
