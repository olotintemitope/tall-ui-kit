{{-- Workbench /files: UploadFieldsDemo. --}}
<div class="grid min-w-0 grid-cols-1 gap-8 lg:grid-cols-2" data-upload-fields-demo>
    <div class="min-w-0 space-y-3">
        <x-ui.field label="Supporting documents" name="documents">
            <x-ui.file wire:model="documents" multiple accept=".pdf,image/*" max-size="5MB" :max-files="4" max-total-size="10MB" reorder reorder-action="reorderDocuments" remove-action="removeDocument" paste />
        </x-ui.field>
        <x-ui.attachment-list label="On the server, in order" :items="$documentRows" empty="Nothing uploaded yet." />
    </div>
    <div class="min-w-0 space-y-6">
        <x-ui.field label="Profile photo" name="photo">
            <x-ui.file wire:model="photo" accept="image/*" max-size="2MB" capture="user" />
        </x-ui.field>
        <x-ui.field label="Master recording" name="large">
            <x-ui.file wire:model="large" multiple chunked="{{ url('workbench/uploads') }}" :chunk-size="262144" max-size="200MB" hint="WAV or ZIP, up to 200 MB. Uploads in pieces and resumes after a dropped connection.">
                <x-slot:files>
                    <x-ui.upload-progress.list for="large" multiple label="Large uploads" class="mt-2" />
                </x-slot:files>
            </x-ui.file>
        </x-ui.field>
        <x-ui.attachment-list label="Finished large uploads" :items="$largeRows" remove-action="removeLarge" empty="No large uploads yet." />
    </div>
</div>
