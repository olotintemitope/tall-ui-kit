{{-- Workbench /workflow: UploadDemo. The upload list replaces x-ui.file's own list through its `files` slot. --}}
<div class="min-w-0 space-y-3" data-upload-demo>
    <x-ui.field label="Attachments" name="files">
        <x-ui.file wire:model="files" multiple hint="Up to 5 files, 10 MB each.">
            <x-slot:files>
                <x-ui.upload-progress.list for="files" multiple remove-action="removeFile" label="Attachments" class="mt-2" />
            </x-slot:files>
        </x-ui.file>
    </x-ui.field>
</div>
