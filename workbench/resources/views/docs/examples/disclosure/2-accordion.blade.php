{{-- Exclusive accordion: one item open at a time --}}
<div class="max-w-xl space-y-2">
    <x-ui.heading :level="3" size="label">Payout questions</x-ui.heading>
    <x-ui.accordion exclusive name="payout-faq">
        <x-ui.accordion.item summary="When are payouts sent?" open>
            <x-ui.text>Every weekday at 15:00 West Africa Time, for payouts approved before then.</x-ui.text>
        </x-ui.accordion.item>
        <x-ui.accordion.item summary="What is the minimum payout?">
            <x-ui.text>₦5,000. Smaller balances roll over to the next run.</x-ui.text>
        </x-ui.accordion.item>
        <x-ui.accordion.item summary="Can I change my bank account?" meta="Takes 24h">
            <x-ui.text>Yes, in Settings. New accounts are held for 24 hours before the first payout.</x-ui.text>
        </x-ui.accordion.item>
    </x-ui.accordion>
</div>
