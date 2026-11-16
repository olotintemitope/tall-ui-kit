{{-- Types, prefix and suffix --}}
<div class="flex max-w-md flex-col gap-4">
    <x-ui.field label="Customer email" name="inputEmail">
        <x-ui.input type="email" placeholder="name@company.ng" width="full" />
    </x-ui.field>
    <x-ui.field label="Website" name="inputWebsite">
        <x-ui.input type="url" value="https://lagosnights.ng" width="full" />
    </x-ui.field>
    <x-ui.field label="Invoice amount" name="inputAmount">
        <x-ui.input value="185,000.00" width="sm">
            <x-slot:prefix>₦</x-slot:prefix>
        </x-ui.input>
    </x-ui.field>
    <x-ui.field label="Retry window" name="inputRetry">
        <x-ui.input value="48" width="xs">
            <x-slot:suffix>hours</x-slot:suffix>
        </x-ui.input>
    </x-ui.field>
</div>
