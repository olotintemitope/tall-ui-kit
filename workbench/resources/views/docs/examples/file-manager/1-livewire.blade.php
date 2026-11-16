{{-- A Livewire page: data in, actions out (check every id on the server) --}}
<x-ui.code-block label="resources/views/livewire/drive.blade.php" copy>{{ implode("\n", [
    '<'.'x-ui.file-manager',
    '    label="My files"',
    '    :items="$items"',
    '    :breadcrumbs="$trail"',
    '    :folder="$folder"',
    '    :folders="$folderTree"',
    '    :sort="$sort" :direction="$direction" :total="$total"',
    '    open-action="open" rename-action="rename" move-action="move"',
    '    delete-action="delete" create-folder-action="createFolder"',
    '    download-action="download" sort-action="sortBy" load-more-action="loadMore"',
    '    upload="uploads" upload-target="uploadFolder"',
    '/>',
]) }}</x-ui.code-block>
