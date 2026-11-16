{{-- Livewire uploads: the list follows wire:model on the file input --}}
<x-ui.code-block label="resources/views/livewire/campaigns/files.blade.php" copy>{{ '<'.'x-ui.field label="Attachments" name="files">
    <'.'x-ui.file wire:model="files" multiple>
        <'.'x-slot:files>
            <'.'x-ui.upload-progress.list for="files" multiple remove-action="removeFile" />
        </'.'x-slot:files>
    </'.'x-ui.file>
</'.'x-ui.field>' }}</x-ui.code-block>
