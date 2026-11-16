{{-- Money position: x-ui.kv-list --}}
<div class="max-w-md">
    <x-ui.kv-list>
        <x-ui.kv label="Gross" sub="Before fees"><x-ui.money :value="15250000" /></x-ui.kv>
        <x-ui.kv label="Platform fee" sub="10% of gross"><x-ui.money :value="-1525000" /></x-ui.kv>
        <x-ui.kv label="VAT on fee"><x-ui.money :value="-114375" /></x-ui.kv>
        <x-ui.kv label="Net payout"><x-ui.money :value="13610625" /></x-ui.kv>
    </x-ui.kv-list>
</div>
