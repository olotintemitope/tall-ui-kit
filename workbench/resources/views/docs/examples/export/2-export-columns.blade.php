{{-- Column picker on its own: Export dispatches export-columns on window --}}
<div x-data="{ picked: '' }" x-on:export-columns.window="if ($event.detail.name === 'payouts-columns') picked = $event.detail.format.toUpperCase() + ': ' + $event.detail.columns.join(', ')" class="space-y-2">
    <x-ui.button variant="outline" size="sm" x-on:click="$dispatch('open-dialog', 'payouts-columns')">Choose columns</x-ui.button>
    <p class="type-meta wrap-anywhere" x-show="picked" x-text="picked" aria-live="polite"></p>
    <x-ui.export-columns
        name="payouts-columns"
        :columns="[
            ['key' => 'reference', 'heading' => 'Reference', 'required' => true, 'checked' => true],
            ['key' => 'curator', 'heading' => 'Curator', 'description' => 'Name and email', 'checked' => true],
            ['key' => 'amount', 'heading' => 'Amount', 'checked' => true],
            ['key' => 'bank', 'heading' => 'Bank account', 'description' => 'Masked to the last four digits', 'checked' => false],
            ['key' => 'requested_at', 'heading' => 'Requested', 'checked' => true],
            ['key' => 'status', 'heading' => 'Status', 'checked' => false],
        ]"
    />
</div>
