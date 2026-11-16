{{-- Export menu: the trigger and its levels (exports run through WithExports in a Livewire component) --}}
<div class="flex flex-wrap items-center justify-end gap-2">
    <x-ui.export-menu
        name="invoices-export"
        :rows="248"
        :total="1312"
        :columns="[
            ['key' => 'reference', 'heading' => 'Reference', 'required' => true, 'checked' => true],
            ['key' => 'customer', 'heading' => 'Customer', 'description' => 'Business name and email', 'checked' => true],
            ['key' => 'issued_at', 'heading' => 'Issued', 'checked' => true],
            ['key' => 'amount', 'heading' => 'Amount', 'checked' => true],
            ['key' => 'tax', 'heading' => 'VAT', 'checked' => false],
            ['key' => 'status', 'heading' => 'Status', 'checked' => true],
        ]"
    />
</div>
