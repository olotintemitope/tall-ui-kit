{{-- Multiple --}}
<x-ui.field label="Notify on" name="selectEvents" hint="Pick any number.">
    <x-ui.select multiple placeholder="No events" :selected="['payout.sent', 'invoice.overdue']" :options="[
        'payout.sent' => 'Payout sent',
        'payout.failed' => 'Payout failed',
        'invoice.paid' => 'Invoice paid',
        'invoice.overdue' => 'Invoice overdue',
    ]" />
</x-ui.field>
