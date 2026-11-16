{{-- Rank changes and your row --}}
<x-ui.leaderboard label="Docs leaderboard" value-label="streams" :rows="[
    ['key' => 'a', 'rank' => 1, 'label' => 'Tunde Bakare', 'sub' => 'Afrobeats', 'value' => 48210, 'change' => 2],
    ['key' => 'b', 'rank' => 2, 'label' => 'Amaka Obi', 'sub' => 'Highlife', 'value' => 45990, 'change' => -1],
    ['key' => 'c', 'rank' => 3, 'label' => 'Ngozi Eze', 'value' => 39100, 'change' => 0],
    ['key' => 'd', 'rank' => 4, 'label' => 'Kelechi Nwosu', 'value' => 30440, 'new' => true],
    ['key' => 'e', 'rank' => 5, 'label' => 'Dayo Bello', 'value' => 28800, 'change' => 3, 'current' => true],
]" />
