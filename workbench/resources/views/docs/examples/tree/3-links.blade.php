{{-- Rows as links (nothing selectable) --}}
<div class="max-w-sm">
    <x-ui.tree
        label="Help topics"
        id="tree-help"
        :expanded="['payouts']"
        :nodes="[
            ['id' => 'payouts', 'label' => 'Payouts', 'children' => [
                ['id' => 'payout-schedule', 'label' => 'Payout schedule', 'href' => '#payout-schedule'],
                ['id' => 'failed-payouts', 'label' => 'Failed payouts', 'href' => '#failed-payouts'],
            ]],
            ['id' => 'invoicing', 'label' => 'Invoicing', 'children' => [
                ['id' => 'vat', 'label' => 'VAT on invoices', 'href' => '#vat'],
            ]],
        ]"
    />
</div>
