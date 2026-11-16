{{-- Settings section on a tinted page --}}
<x-ui.form-page>
    <x-ui.form x-on:submit.prevent="$toast({ variant: 'success', title: 'Payout settings saved.' })">
        <x-ui.form-section title="Payouts" description="Where and when we pay you.">
            <x-ui.panel>
                <x-ui.field label="Account name" name="settingsAccountName" required>
                    <x-ui.input value="Lagos Nights Records Ltd" width="full" />
                </x-ui.field>
                <x-ui.field label="Payout day" name="settingsPayoutDay" hint="Requests made after 16:00 go out the next payout day.">
                    <x-ui.select selected="fri" :options="['mon' => 'Monday', 'wed' => 'Wednesday', 'fri' => 'Friday']" />
                </x-ui.field>
                <x-slot:footer>
                    <x-ui.form-actions>
                        <x-ui.button variant="ghost" type="button" x-on:click="$dispatch('form-reset')">Discard</x-ui.button>
                        <x-ui.button type="submit">Save changes</x-ui.button>
                    </x-ui.form-actions>
                </x-slot:footer>
            </x-ui.panel>
        </x-ui.form-section>
    </x-ui.form>
</x-ui.form-page>
