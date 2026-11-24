{{-- A month of training: timed sessions, an all-day item, tones, links and overflow --}}
<x-ui.calendar month="2026-11" now="2026-11-24 09:00" :events="[
    ['key' => 'a', 'date' => '2026-11-03', 'time' => 'All day', 'title' => 'Exam board meeting', 'tone' => 'info'],
    ['key' => 'b', 'date' => '2026-11-10T09:00:00+01:00', 'title' => 'Induction', 'href' => '#induction', 'meta' => 'Hall A'],
    ['key' => 'c', 'date' => '2026-11-10T11:00:00+01:00', 'title' => 'Fire safety', 'tone' => 'warning'],
    ['key' => 'd', 'date' => '2026-11-10T14:00:00+01:00', 'title' => 'Python basics'],
    ['key' => 'e', 'date' => '2026-11-10T16:00:00+01:00', 'title' => 'Office hours'],
    ['key' => 'f', 'date' => '2026-11-24T10:00:00+01:00', 'title' => 'Mock exam', 'tone' => 'danger', 'meta' => 'Centre 2'],
    ['key' => 'g', 'date' => '2026-11-27T13:00:00+01:00', 'title' => 'Certificates issued', 'tone' => 'success'],
]" />
