{{-- Title, actions and footer --}}
<x-ui.card title="Payout account" description="Where approved payouts are sent.">
    <x-slot:badge><x-ui.badge variant="success">Verified</x-ui.badge></x-slot:badge>
    <x-slot:actions>
        <x-ui.button variant="outline" size="sm">Change account</x-ui.button>
    </x-slot:actions>
    <x-ui.dl layout="inline" :items="[
        ['label' => 'Bank', 'value' => 'First Bank of Nigeria'],
        ['label' => 'Account', 'value' => '30•• ••• 4821', 'numeric' => true],
        ['label' => 'Name', 'value' => 'Chiamaka Okafor'],
    ]" />
    <x-slot:footer>
        <p class="type-meta">Changes take effect from the next payout run.</p>
    </x-slot:footer>
</x-ui.card>
