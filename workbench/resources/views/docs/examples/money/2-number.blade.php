{{-- Numbers: plain, compact and percent --}}
<x-ui.kv-list>
    <x-ui.kv label="Invoices sent"><x-ui.number :value="1204" /></x-ui.kv>
    <x-ui.kv label="Followers"><x-ui.number :value="48210" format="compact" /></x-ui.kv>
    <x-ui.kv label="Collection rate"><x-ui.number :value="0.924" format="percent" :precision="1" /></x-ui.kv>
</x-ui.kv-list>
