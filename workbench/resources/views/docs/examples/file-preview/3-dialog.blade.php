{{-- In a dialog or sheet, stepping through a set with Previous / Next (and the arrow keys) --}}
<x-ui.code-block label="resources/views/livewire/drive.blade.php" copy>{{ implode("\n", [
    '<'.'x-ui.file-preview variant="dialog" name="preview"',
    '    :file="$previewFile" :files="$siblings" navigate-action="preview" />',
    '',
    '{{-- open it from the manager\'s openAction: $this->dispatch(\'open-dialog\', name: \'preview\') --}}',
]) }}</x-ui.code-block>
