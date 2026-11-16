{{-- Workbench /files: ManagerDemo. Opening a file shows it in x-ui.file-preview's dialog. --}}
<div class="min-w-0" data-manager-demo>
    <x-ui.file-manager
        label="My files"
        :items="$items"
        :breadcrumbs="$trail"
        :folder="$folder"
        :folders="$tree"
        :sort="$sort"
        :direction="$direction"
        :total="$total"
        persist="workbench-files-view"
        open-action="openItem"
        rename-action="rename"
        move-action="move"
        delete-action="delete"
        create-folder-action="createFolder"
        download-action="download"
        sort-action="sortBy"
        load-more-action="loadMore"
        upload="uploads"
        upload-target="uploadFolder"
        id="drive"
        now="2026-11-16 12:00:00"
    />
    @if ($previewFile)
        <x-ui.file-preview variant="dialog" name="manager-preview" :file="$previewFile" :files="$previewSet" navigate-action="previewFile" now="2026-11-16 12:00:00" wire:key="manager-preview" />
    @endif
</div>
