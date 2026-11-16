{{-- With Livewire uploads --}}
<x-ui.code-block label="resources/views/livewire/expenses/create.blade.php" copy>{{ implode("\n", [
    '<'.'x-ui.field label="Receipts" name="receipts">',
    '    <'.'x-ui.file wire:model="receipts" multiple hint="Up to 5 files, 10 MB each.">',
    '        <'.'x-slot:files>',
    '            <'.'x-ui.upload-progress.list for="receipts" multiple remove-action="removeReceipt" label="Receipts" class="mt-2" />',
    '        </'.'x-slot:files>',
    '    </'.'x-ui.file>',
    '</'.'x-ui.field>',
]) }}</x-ui.code-block>
