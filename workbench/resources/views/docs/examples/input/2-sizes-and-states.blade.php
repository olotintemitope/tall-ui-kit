{{-- Small, disabled and invalid --}}
<div class="flex max-w-md flex-col gap-4">
    <x-ui.field label="Reference (small)" name="inputSmall">
        <x-ui.input size="sm" value="INV-2048" />
    </x-ui.field>
    <x-ui.field label="Account number" name="inputLocked" hint="Contact support to change it.">
        <x-ui.input value="0123456789" disabled />
    </x-ui.field>
    <x-ui.field label="Tax ID" name="inputTaxId" error="Enter the 10-digit TIN from your certificate.">
        <x-ui.input value="1234-56" />
    </x-ui.field>
</div>
