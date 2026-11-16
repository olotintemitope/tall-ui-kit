{{-- A group of checkboxes in a fieldset --}}
<x-ui.fieldset legend="Payment methods" description="Customers see these at checkout.">
    <x-ui.checkbox name="methods[]" value="card" label="Card" checked />
    <x-ui.checkbox name="methods[]" value="transfer" label="Bank transfer" description="Settles in up to 24 hours." checked />
    <x-ui.checkbox name="methods[]" value="ussd" label="USSD" />
</x-ui.fieldset>
