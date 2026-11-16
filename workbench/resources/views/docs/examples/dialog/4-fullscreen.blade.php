{{-- Full screen on phones, centred from sm --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'payout-terms')">Read payout terms</x-ui.button>

<x-ui.dialog.fullscreen name="payout-terms" title="Payout terms" size="lg">
    <div class="flex flex-col gap-3">
        <x-ui.text>Payouts are sent every weekday at 3pm to the bank account on file.</x-ui.text>
        <x-ui.text>Requests made after 3pm, at weekends or on public holidays go out the next working day.</x-ui.text>
        <x-ui.text>A transfer that the bank returns is credited back to your balance within two working days.</x-ui.text>
    </div>
    <x-slot:footer>
        <x-ui.button size="sm" x-on:click="$dispatch('close-dialog', 'payout-terms')">Close</x-ui.button>
    </x-slot:footer>
</x-ui.dialog.fullscreen>
