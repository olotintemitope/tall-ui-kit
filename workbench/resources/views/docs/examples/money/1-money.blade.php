{{-- Money: naira, dollars, signs and the lean form --}}
<x-ui.kv-list>
    <x-ui.kv label="Balance"><x-ui.money :value="2450000" /></x-ui.kv>
    <x-ui.kv label="USD wallet"><x-ui.money :value="1840.5" currency="USD" /></x-ui.kv>
    <x-ui.kv label="Refund"><x-ui.money :value="-35000" /></x-ui.kv>
    <x-ui.kv label="Top-up" sub="With an explicit sign"><x-ui.money :value="50000" sign="+" /></x-ui.kv>
    <x-ui.kv label="In a dense row" sub="lean: one span, no Alpine"><x-ui.money :value="125000" lean /></x-ui.kv>
</x-ui.kv-list>
