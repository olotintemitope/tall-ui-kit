{{-- Settings page: tinted page, sections with a text column and a panel --}}
<x-ui.form-page>
    <x-ui.form x-on:submit.prevent="$toast({ variant: 'success', title: 'Settings saved.' })">
        <x-ui.form-section title="Business" description="Shown on invoices and receipts.">
            <x-ui.panel>
                <x-ui.field-row>
                    <x-ui.field label="Legal name" name="patternLegalName" required>
                        <x-ui.input value="Eko Studio Ltd" width="full" />
                    </x-ui.field>
                    <x-ui.field label="RC number" name="patternRc" hint="As on your CAC certificate.">
                        <x-ui.input value="RC 1840221" width="full" />
                    </x-ui.field>
                </x-ui.field-row>
                <x-ui.field label="Country" name="patternCountry">
                    <x-ui.select selected="ng" :options="['ng' => 'Nigeria', 'gh' => 'Ghana', 'ke' => 'Kenya']" />
                </x-ui.field>
                <x-ui.switch label="Email receipts to customers" description="Sent when a payment succeeds." checked />
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
