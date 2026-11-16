{{-- Responsive: a select with the same binding below sm --}}
<x-ui.segmented label="Status" value="pending" responsive :options="[
    'all' => 'All',
    'pending' => 'Pending',
    'approved' => 'Approved',
    'paid' => 'Paid',
    'rejected' => 'Rejected',
]" />
