{{-- Sticky actions: edit a field to show them --}}
<x-ui.form-page>
    <x-ui.form x-on:submit.prevent="$toast({ variant: 'success', title: 'Notifications saved.' }); dirty = false">
        <x-ui.form-section title="Notifications" description="Where we send payout and invoice notices.">
            <x-ui.panel>
                <x-ui.field label="Send notices to" name="notifyEmail">
                    <x-ui.input type="email" value="finance@lagosnights.ng" width="full" />
                </x-ui.field>
                <x-ui.field label="Digest" name="notifyDigest">
                    <x-ui.select selected="daily" :options="['off' => 'Off', 'daily' => 'Daily at 09:00', 'weekly' => 'Mondays at 09:00']" />
                </x-ui.field>
            </x-ui.panel>
        </x-ui.form-section>
        <x-ui.form-actions sticky>
            <x-ui.button variant="ghost" type="button" x-on:click="$dispatch('form-reset')">Discard</x-ui.button>
            <x-ui.button type="submit">Save changes</x-ui.button>
        </x-ui.form-actions>
    </x-ui.form>
</x-ui.form-page>
