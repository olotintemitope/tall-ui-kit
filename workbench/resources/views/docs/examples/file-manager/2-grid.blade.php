{{-- Grid view (static data: try the keys and the pointer; actions need Livewire) --}}
<x-ui.file-manager
    id="docs-fm-grid"
    label="Campaign assets"
    :persist="false"
    now="2026-11-16 12:00:00"
    :breadcrumbs="[['id' => null, 'label' => 'Campaign assets']]"
    :items="[
        ['id' => 'a', 'name' => 'Artwork', 'folder' => true, 'count' => 4, 'modified' => '2026-11-14 10:00:00'],
        ['id' => 'b', 'name' => 'Press kit and approved biography for every territory', 'folder' => true, 'count' => 12, 'modified' => '2026-11-02 10:00:00'],
        ['id' => 'c', 'name' => 'release-plan-q1-2027.pdf', 'size' => 2400000, 'modified' => '2026-11-15 10:00:00'],
        ['id' => 'd', 'name' => 'Lyrics.txt', 'size' => 2400, 'modified' => '2026-11-10 10:00:00'],
        ['id' => 'e', 'name' => 'Teaser.mp4', 'size' => 48000000, 'modified' => '2026-11-09 10:00:00'],
    ]"
/>
