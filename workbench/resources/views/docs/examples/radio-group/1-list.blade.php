{{-- A list with descriptions --}}
<x-ui.radio-group name="rgSchedule" label="Payout schedule" selected="weekly" :options="[
    ['value' => 'daily', 'label' => 'Daily', 'description' => 'Every working day at 16:00.'],
    ['value' => 'weekly', 'label' => 'Weekly', 'description' => 'Every Friday.'],
    ['value' => 'manual', 'label' => 'Manual', 'description' => 'Only when you request one.'],
]" />
