{{-- Vertical, completed steps clickable --}}
<x-ui.stepper label="Verification" orientation="vertical" :current="3" clickable :steps="[
    ['label' => 'Upload your ID', 'description' => 'A passport or national ID card.', 'href' => '#id'],
    ['label' => 'Take a selfie', 'description' => 'We compare it with the photo on your ID.', 'href' => '#selfie'],
    ['label' => 'Wait for review', 'description' => 'Usually under an hour.'],
]" />
