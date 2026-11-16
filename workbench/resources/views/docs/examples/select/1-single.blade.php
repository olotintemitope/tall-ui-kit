{{-- Single choice --}}
<div class="flex flex-col gap-4">
    <x-ui.field label="Invoice status" name="selectStatus">
        <x-ui.select selected="overdue" :options="['draft' => 'Draft', 'sent' => 'Sent', 'overdue' => 'Overdue', 'paid' => 'Paid']" />
    </x-ui.field>
    <x-ui.field label="Currency" name="selectCurrency" hint="Customers pay in this currency.">
        <x-ui.select placeholder="Choose a currency" :options="['NGN' => 'Naira (NGN)', 'USD' => 'US dollar (USD)', 'GBP' => 'Pound sterling (GBP)']" />
    </x-ui.field>
</div>
