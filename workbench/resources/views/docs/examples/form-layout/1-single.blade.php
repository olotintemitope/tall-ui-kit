{{-- Single column --}}
<x-ui.form-page>
    <x-ui.form x-on:submit.prevent="$toast({ variant: 'success', title: 'Payout requested.' })">
        <x-ui.form-layout.single title="Request a payout" description="Available balance: ₦1,250,000.">
            <x-ui.field label="Amount" name="singleAmount" hint="At least ₦5,000." required>
                <x-ui.number-input format="money" :min="5000" :step="1000" :value="250000" />
            </x-ui.field>
            <x-ui.field label="Pay to" name="singleAccount">
                <x-ui.select selected="gtb" :options="['gtb' => 'GTBank, 0123456789', 'zenith' => 'Zenith Bank, 2087654321']" />
            </x-ui.field>
            <x-slot:footer>
                <x-ui.button variant="ghost" type="button">Cancel</x-ui.button>
                <x-ui.button type="submit">Request payout</x-ui.button>
            </x-slot:footer>
        </x-ui.form-layout.single>
    </x-ui.form>
</x-ui.form-page>
