{{-- Rows as data: drag the grip, or Space then the arrow keys --}}
<div x-data="{ order: '' }" x-on:ui-sort="order = $event.detail.order.join(', ')" class="max-w-xl space-y-2">
    <x-ui.sortable
        label="Payment methods, in checkout order"
        id="sortable-methods"
        :items="[
            ['key' => 'card', 'label' => 'Card'],
            ['key' => 'transfer', 'label' => 'Bank transfer'],
            ['key' => 'mobile', 'label' => 'Mobile money', 'description' => 'MTN MoMo, Airtel Money and OPay, settled within the hour.'],
            ['key' => 'ussd', 'label' => 'USSD'],
        ]"
    />
    <p class="type-meta" x-show="order">New order: <span class="type-numeric text-ink-body" x-text="order"></span></p>
</div>
