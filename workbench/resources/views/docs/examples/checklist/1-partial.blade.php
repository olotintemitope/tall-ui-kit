{{-- Partial: the next step is highlighted --}}
<x-ui.checklist
    title="Set up payouts"
    description="A few steps before you can withdraw."
    name="docs-payout-setup"
    persist="none"
    collapsible
    :heading-level="4"
    :items="[
        ['key' => 'email', 'label' => 'Verify your email address', 'done' => true],
        ['key' => 'bvn', 'label' => 'Confirm your BVN', 'hint' => 'We never store the full number.', 'done' => false, 'href' => '#bvn', 'actionLabel' => 'Confirm'],
        ['key' => 'bank', 'label' => 'Add a bank account', 'done' => false, 'href' => '#bank', 'actionLabel' => 'Add account'],
        ['key' => 'logo', 'label' => 'Upload a logo', 'optional' => true, 'done' => false, 'href' => '#logo', 'actionLabel' => 'Upload'],
    ]"
/>
