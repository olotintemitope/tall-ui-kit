{{-- Rendered directly, for previews only --}}
<div class="flex max-w-sm flex-col gap-2">
    <x-ui.toast variant="success" title="Payout sent." description="₦1,250,000 to Kemi Adeyemi." :action="['label' => 'View', 'url' => '#payouts']" />
    <x-ui.toast variant="error" title="Transfer failed." description="The bank did not accept the account number." />
    <x-ui.toast variant="loading" title="Exporting statements" :dismissible="false" />
</div>
