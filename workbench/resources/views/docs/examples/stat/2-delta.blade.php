{{-- Delta: change against the prior period, coloured by whether it is good --}}
<x-ui.kv-list>
    <x-ui.kv label="Active customers" sub="2,418 now"><x-ui.delta :current="2418" :previous="2190" period="30d" /></x-ui.kv>
    <x-ui.kv label="Chargebacks" sub="9 now"><x-ui.delta :current="9" :previous="6" period="30d" :up-is-good="false" /></x-ui.kv>
    <x-ui.kv label="Average payout" sub="₦84,500 now"><x-ui.delta :current="84500" :previous="84500" period="30d" /></x-ui.kv>
    <x-ui.kv label="Refund requests" sub="4 now, none before"><x-ui.delta :current="4" :previous="0" period="30d" :up-is-good="false" /></x-ui.kv>
</x-ui.kv-list>
