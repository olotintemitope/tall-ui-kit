{{-- A record's details in a side sheet --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'transaction-sheet')">View transaction</x-ui.button>

<x-ui.sheet name="transaction-sheet" title="Transaction TXN-9F3C21AB" description="Paid 14 Nov 2026, 09:30.">
    <x-ui.dl>
        <x-ui.dl.item label="Customer">Kemi Adeyemi</x-ui.dl.item>
        <x-ui.dl.item label="Amount"><x-ui.money :value="125000" /></x-ui.dl.item>
        <x-ui.dl.item label="Method">Card ending 4421</x-ui.dl.item>
        <x-ui.dl.item label="Status"><x-ui.badge variant="success">Completed</x-ui.badge></x-ui.dl.item>
    </x-ui.dl>
    <x-slot:footer>
        <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('close-dialog', 'transaction-sheet')">Close</x-ui.button>
        <x-ui.button size="sm">Issue refund</x-ui.button>
    </x-slot:footer>
</x-ui.sheet>
