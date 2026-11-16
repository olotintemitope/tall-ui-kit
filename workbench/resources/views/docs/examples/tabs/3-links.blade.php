{{-- Link tabs between sibling pages --}}
<x-ui.tabs label="Campaign pages" active="submissions" :tabs="[
    'overview' => ['label' => 'Overview', 'href' => '#overview'],
    'submissions' => ['label' => 'Submissions', 'href' => '#submissions', 'count' => 42],
    'payouts' => ['label' => 'Payouts', 'href' => '#payouts'],
    'settings' => ['label' => 'Settings', 'href' => '#settings'],
]" />
