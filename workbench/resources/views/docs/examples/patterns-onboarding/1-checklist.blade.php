{{-- Onboarding: a checklist at the top of the home page --}}
<x-ui.checklist
    title="Get ready to take payments"
    description="Four steps, about ten minutes."
    name="docs-pattern-onboarding"
    persist="none"
    collapsible
    dismissible
    :heading-level="3"
    :items="[
        ['key' => 'profile', 'label' => 'Add your business details', 'done' => true],
        ['key' => 'kyc', 'label' => 'Verify your identity', 'hint' => 'A BVN and a photo ID.', 'href' => '#verify', 'actionLabel' => 'Verify'],
        ['key' => 'bank', 'label' => 'Add a payout account', 'href' => '#bank', 'actionLabel' => 'Add account'],
        ['key' => 'team', 'label' => 'Invite your team', 'optional' => true, 'href' => '#team', 'actionLabel' => 'Invite'],
    ]"
/>
