{{-- Show a toast from Alpine with $toast --}}
<div x-data class="flex flex-wrap items-center gap-2">
    <x-ui.button variant="outline" x-on:click="$toast({ variant: 'success', title: 'Payout sent.', description: 'It reaches the bank within a working day.' })">Success</x-ui.button>
    <x-ui.button variant="outline" x-on:click="$toast({ variant: 'error', title: 'Transfer failed.', description: 'Check the account number and try again.' })">Error</x-ui.button>
    <x-ui.button variant="outline" x-on:click="$toast({ variant: 'warning', title: 'Two campaigns end this week.' })">Warning</x-ui.button>
    <x-ui.button variant="outline" x-on:click="$toast({ variant: 'info', title: 'Statements update every night.' })">Info</x-ui.button>
    <x-ui.button variant="outline" x-on:click="$toast('Reference copied.')">Neutral</x-ui.button>
</div>
