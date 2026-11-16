{{-- A form dialog: Enter submits, closing with edits asks first --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'add-bank')">Add bank account</x-ui.button>

<x-ui.dialog.form name="add-bank" title="Add bank account" description="Payouts go to this account from the next cycle." submit-label="Add account">
    <x-ui.field label="Account name" name="bankAccountName" required>
        <x-ui.input width="full" autocomplete="name" />
    </x-ui.field>
    <x-ui.field label="Account number" name="bankAccountNumber" hint="10 digits (NUBAN)." required>
        <x-ui.input inputmode="numeric" maxlength="10" width="sm" />
    </x-ui.field>
</x-ui.dialog.form>
