{{-- Inline rows for dense settings --}}
<x-ui.form-page>
    <x-ui.panel>
        <x-ui.form-layout.inline>
            <x-ui.field inline label="Default currency" name="inlineCurrency" hint="Used for new invoices.">
                <x-ui.select selected="NGN" :options="['NGN' => 'Naira (NGN)', 'USD' => 'US dollar (USD)']" />
            </x-ui.field>
            <x-ui.field inline label="Payment terms" name="inlineTerms" hint="Days until an invoice is due.">
                <x-ui.number-input :min="0" :max="90" :value="14" noun="days" />
            </x-ui.field>
            <x-ui.field inline label="Late reminders" name="inlineReminders">
                <x-ui.switch description="Email the customer 3 days after the due date." checked />
            </x-ui.field>
        </x-ui.form-layout.inline>
    </x-ui.panel>
</x-ui.form-page>
