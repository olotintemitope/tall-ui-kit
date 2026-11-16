{{-- States --}}
<div class="flex flex-col gap-3">
    <x-ui.checkbox name="cbAll" label="All invoices on this page" indeterminate />
    <x-ui.checkbox name="cbLocked" label="Require two approvers" description="Set by your workspace owner." checked disabled />
    <x-ui.checkbox name="cbOff" label="Auto-approve payouts under ₦50,000" disabled />
    <x-ui.checkbox name="cbTerms" label="I confirm these bank details are correct" invalid />
</div>
