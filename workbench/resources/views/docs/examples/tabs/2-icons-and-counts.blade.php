{{-- Icons, counts and a count that needs attention --}}
<x-ui.tabs label="Inbox" active="open" size="sm" :tabs="[
    'open' => ['label' => 'Open', 'icon' => 'inbox', 'count' => 24],
    'late' => ['label' => 'Late', 'icon' => 'clock', 'count' => 5, 'badge' => 'danger'],
    'closed' => ['label' => 'Closed', 'icon' => 'circle-check', 'count' => 1208],
]" />
