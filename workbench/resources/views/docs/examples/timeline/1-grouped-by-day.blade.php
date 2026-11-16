{{-- Data mode, grouped by day, with Show more --}}
<x-ui.timeline
    label="Payout activity"
    group-by-day
    :limit="4"
    now="2026-11-15 14:00:00"
    :items="[
        ['key' => 'p1', 'time' => '2026-11-15 11:20:00', 'actor' => 'Amaka Obi', 'text' => 'approved the payout.', 'status' => ['Approved', 'success']],
        ['key' => 'p2', 'time' => '2026-11-15 10:05:00', 'actor' => 'Tunde Bakare', 'text' => 'left a note.', 'body' => 'Bank name matches the BVN record. Fine to release.'],
        ['key' => 'p3', 'time' => '2026-11-15 09:05:00', 'actor' => 'Chidi Eze', 'text' => 'attached the statement.', 'attachment' => ['name' => 'statement-october-2026-first-bank.pdf', 'href' => '#statement', 'meta' => '240 KB']],
        ['key' => 'p4', 'time' => '2026-11-14 17:30:00', 'actor' => 'Payments system', 'text' => 'held the payout for review.', 'status' => ['On hold', 'warning']],
        ['key' => 'p5', 'time' => '2026-11-14 16:02:00', 'actor' => 'Dayo Bello', 'text' => 'requested a payout of ₦1,250,000.'],
        ['key' => 'p6', 'time' => '2026-11-12 10:00:00', 'actor' => 'Efe Okoro', 'text' => 'updated the bank details.'],
    ]"
/>
