{{-- Sparklines in table cells --}}
<x-ui.table caption="Weekly volume by customer">
    <x-slot:head>
        <tr>
            <x-ui.table.th>Customer</x-ui.table.th>
            <x-ui.table.th>Last 8 weeks</x-ui.table.th>
            <x-ui.table.th numeric>This week</x-ui.table.th>
        </tr>
    </x-slot:head>
    <x-ui.table.row>
        <x-ui.table.td strong>Zenith Foods</x-ui.table.td>
        <x-ui.table.td><x-ui.chart.sparkline :points="[12, 14, 13, 17, 16, 19, 22, 24]" label="Zenith Foods, rising" /></x-ui.table.td>
        <x-ui.table.td numeric>24</x-ui.table.td>
    </x-ui.table.row>
    <x-ui.table.row>
        <x-ui.table.td strong>Kano Agro Ltd</x-ui.table.td>
        <x-ui.table.td><x-ui.chart.sparkline :points="[30, 28, 27, 25, 22, 21, 19, 18]" label="Kano Agro Ltd, falling" /></x-ui.table.td>
        <x-ui.table.td numeric>18</x-ui.table.td>
    </x-ui.table.row>
</x-ui.table>
