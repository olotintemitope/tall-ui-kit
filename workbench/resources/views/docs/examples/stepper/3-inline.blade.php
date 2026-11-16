{{-- Inline: the wizard header, "Step 3 of 5" on phones --}}
<x-ui.stepper label="Campaign steps" variant="inline" :current="3" :steps="['Song', 'Targets', 'Details', 'Review', 'Pay']" />
