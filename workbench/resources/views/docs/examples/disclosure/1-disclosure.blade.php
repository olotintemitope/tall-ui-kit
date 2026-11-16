{{-- A single disclosure --}}
<div class="max-w-xl space-y-2">
    <x-ui.disclosure summary="Why was this payout held?" meta="2 checks">
        <x-ui.list>
            <li>The bank account was added less than 24 hours ago.</li>
            <li>The amount is over the ₦1,000,000 review threshold.</li>
        </x-ui.list>
    </x-ui.disclosure>
    <x-ui.disclosure summary="Fee breakdown" open>
        <x-ui.text role="meta">Platform fee 10%, VAT 7.5% on the fee. Transfer fees are on us.</x-ui.text>
    </x-ui.disclosure>
</div>
