{{-- Very large files: chunked uploads through the kit's endpoint (see "Chunked uploads") --}}
<x-ui.code-block label="routes/web.php and the Livewire view" copy>{{ implode("\n", [
    'TallUiKit\Uploads\ChunkedUploads::routes(\'ui/uploads\', [\'web\', \'auth\', \'throttle:600,1\']);',
    '',
    '<'.'x-ui.file wire:model="videoIds" multiple chunked="/ui/uploads" max-size="2GB">',
    '    <'.'x-slot:files><'.'x-ui.upload-progress.list for="videoIds" multiple /></'.'x-slot:files>',
    '</'.'x-ui.file>',
    '',
    '// In the component: each id is checked against the current user',
    '$upload = TallUiKit\Uploads\ChunkedUploads::find($id);',
    '$path = $upload?->store(\'videos\', \'s3\');',
]) }}</x-ui.code-block>
