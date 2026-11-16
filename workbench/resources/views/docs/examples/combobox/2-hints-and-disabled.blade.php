{{-- Hints and unavailable options --}}
<x-ui.field label="Genre" name="comboGenre">
    <x-ui.combobox placeholder="Choose a genre" :options="[
        ['value' => 'afrobeats', 'label' => 'Afrobeats', 'hint' => '1,204 playlists'],
        ['value' => 'amapiano', 'label' => 'Amapiano', 'hint' => '862 playlists'],
        ['value' => 'highlife', 'label' => 'Highlife', 'hint' => '311 playlists'],
        ['value' => 'fuji', 'label' => 'Fuji', 'hint' => 'No curators yet', 'disabled' => true],
    ]" />
</x-ui.field>
