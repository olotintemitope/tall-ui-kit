{{-- Label, hint, required and error --}}
<div class="flex max-w-md flex-col gap-4">
    <x-ui.field label="Business email" name="fieldEmail" hint="Receipts and payout notices go here." required>
        <x-ui.input type="email" value="finance@lagosnights.ng" width="full" />
    </x-ui.field>
    <x-ui.field label="Payout threshold" name="fieldThreshold" error="Enter at least ₦5,000.">
        <x-ui.input value="2500" width="sm">
            <x-slot:prefix>₦</x-slot:prefix>
        </x-ui.input>
    </x-ui.field>
</div>
