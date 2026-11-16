{{-- Horizontal, with an error step --}}
<x-ui.stepper label="Set up payouts" :current="4" :steps="[
    ['label' => 'Account', 'description' => 'Email and password.'],
    ['label' => 'Business details', 'description' => 'Registered name and address.'],
    ['label' => 'Bank account', 'description' => 'The account name did not match.', 'state' => 'error'],
    ['label' => 'Review'],
    ['label' => 'Go live'],
]" />
