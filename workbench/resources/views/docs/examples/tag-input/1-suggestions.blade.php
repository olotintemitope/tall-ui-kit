{{-- Suggestions and a maximum --}}
<x-ui.field label="Genres" name="tagGenres" hint="Up to 5. Press Enter or a comma to add.">
    <x-ui.tag-input :value="['Afrobeats', 'Amapiano']" :max="5" :max-length="24" :suggestions="['Afrobeats', 'Amapiano', 'Alté', 'Fuji', 'Gospel', 'Highlife', 'Hip-hop', 'Juju', 'R&B']" />
</x-ui.field>
