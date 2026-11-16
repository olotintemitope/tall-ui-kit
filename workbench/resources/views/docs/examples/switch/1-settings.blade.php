{{-- Settings that apply at once --}}
<div class="flex flex-col gap-4">
    <x-ui.switch name="swAlerts" label="Payout alerts" description="An email when money leaves your balance." checked />
    <x-ui.switch name="swDigest" label="Daily digest" description="A summary of new invoices at 09:00." />
    <x-ui.switch name="swSandbox" label="Test mode" description="Only owners can change this." disabled />
</div>
