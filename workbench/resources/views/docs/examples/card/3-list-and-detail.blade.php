{{-- List and detail cards --}}
<div class="grid gap-4 md:grid-cols-2">
    <x-ui.card.list title="Recent invoices" view-all="#invoices">
        <x-ui.card.row title="INV-2041, Zenith Foods" meta="Due 21 Nov 2026"><x-ui.money :value="1250000" /></x-ui.card.row>
        <x-ui.card.row title="INV-2040, Bolanle Ogunleye Studios" meta="Paid 12 Nov 2026"><x-ui.money :value="480000" /></x-ui.card.row>
        <x-ui.card.row title="INV-2039, Kano Agro Ltd" meta="Overdue by 4 days"><x-ui.money :value="92500" /></x-ui.card.row>
    </x-ui.card.list>
    <x-ui.card.detail title="Payout PO-7F21C9" status="Pending" status-variant="warning">
        <x-slot:actions><x-ui.button variant="outline" size="sm">Approve</x-ui.button></x-slot:actions>
        <x-ui.card.detail-item label="Curator">Emeka Nwosu</x-ui.card.detail-item>
        <x-ui.card.detail-item label="Amount" numeric><x-ui.money :value="325000" /></x-ui.card.detail-item>
        <x-ui.card.detail-item label="Method">Bank transfer</x-ui.card.detail-item>
        <x-ui.card.detail-item label="Requested">14 Nov 2026, 10:42</x-ui.card.detail-item>
    </x-ui.card.detail>
</div>
