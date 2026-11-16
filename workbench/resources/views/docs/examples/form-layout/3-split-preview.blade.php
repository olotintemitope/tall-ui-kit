{{-- Split with a live preview --}}
<x-ui.form-page>
    <x-ui.form-layout.split variant="preview" preview-label="Link preview" x-data="{ title: 'Midnight in Lagos', artist: 'Tems Adebayo' }">
        <x-ui.panel>
            <x-ui.field label="Release title" name="splitTitle">
                <x-ui.input x-model="title" width="full" />
            </x-ui.field>
            <x-ui.field label="Artist" name="splitArtist">
                <x-ui.input x-model="artist" width="full" />
            </x-ui.field>
        </x-ui.panel>
        <x-slot:aside>
            <x-ui.panel>
                <p class="type-heading wrap-anywhere" x-text="title"></p>
                <p class="type-meta wrap-anywhere" x-text="artist"></p>
            </x-ui.panel>
        </x-slot:aside>
    </x-ui.form-layout.split>
</x-ui.form-page>
