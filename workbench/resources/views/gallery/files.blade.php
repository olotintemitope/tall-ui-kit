{{--
    Workbench /files: the file components on sample data. The manager, preview and upload
    fields are small Livewire children (workbench/app/Livewire/Files) on a session drive,
    so each round trip carries one demo only. File bodies come from DemoFileController.
--}}
@php
    $rows = [
        ['key' => 'r1', 'name' => 'Quarterly report.pdf', 'size' => 2400000, 'href' => route('files-demo.file', ['id' => 'report', 'download' => 1])],
        ['key' => 'r2', 'name' => 'statement-october-2026-first-bank-business-account-ending-4821-with-every-transaction.csv', 'size' => 128000, 'status' => 'uploading', 'progress' => 45],
        ['key' => 'r3', 'name' => 'Stems for the remix.zip', 'size' => 48000000, 'status' => 'failed', 'error' => 'The connection dropped at 62%.'],
        ['key' => 'r4', 'name' => 'invoice-crack.exe', 'size' => 512000, 'status' => 'infected', 'error' => 'Malware was found. The file was quarantined.'],
        ['key' => 'r5', 'name' => 'Contract draft.docx', 'size' => 86000, 'status' => 'blocked', 'error' => 'Word files with macros are blocked by your workspace.'],
        ['key' => 'r6', 'name' => '<img src=x onerror=alert(1)>.png', 'size' => 2048, 'status' => 'done'],
    ];
    $cards = [
        ['key' => 'c1', 'name' => 'Album cover.png', 'size' => 840000, 'thumb' => route('files-demo.file', 'cover'), 'alt' => 'Album cover: a circle on a muted field', 'href' => route('files-demo.file', ['id' => 'cover', 'download' => 1])],
        ['key' => 'c2', 'name' => 'Jingle demo.wav', 'size' => 88244, 'href' => route('files-demo.file', ['id' => 'jingle', 'download' => 1])],
        ['key' => 'c3', 'name' => 'Contracts and signed agreements for the 2026 Lagos and Abuja distribution partnerships.pdf', 'size' => 98000, 'status' => 'uploading', 'progress' => 80],
        ['key' => 'c4', 'name' => 'Old exports.zip', 'size' => 31000000, 'status' => 'failed', 'error' => 'Over the 25 MB limit.'],
    ];
    $chips = [
        ['key' => 'k1', 'name' => 'Brief.pdf', 'meta' => '240 KB', 'href' => '#chips'],
        ['key' => 'k2', 'name' => 'mood-board-final-final-v3-approved-by-everyone.jpg', 'meta' => '1.2 MB', 'href' => '#chips'],
        ['key' => 'k3', 'name' => 'Lyrics.txt', 'meta' => '2 KB', 'href' => '#chips'],
    ];
    $static = [
        ['id' => 's1', 'name' => 'Invoices', 'folder' => true, 'count' => 3, 'modified' => '2026-11-12 10:00:00'],
        ['id' => 's2', 'name' => 'Quarterly report.pdf', 'size' => 2400000, 'modified' => '2026-11-15 10:00:00'],
    ];
@endphp
<x-workbench::gallery.layout title="Files">
    <x-ui.page-header eyebrow="UI kit" title="Files" description="File manager, preview, cards, attachment lists and the upgraded file field. Names wrap, never cut." />

    <div class="space-y-10" data-files-gallery>
        <section aria-labelledby="manager" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="manager">File manager</x-ui.section-label>
            <x-ui.text role="meta">Select with a click, Ctrl or Cmd, Shift or Space; F2 renames, Delete deletes, Enter opens. Drag files from the desktop onto the manager or a folder.</x-ui.text>
            @livewire(\Workbench\App\Livewire\Files\ManagerDemo::class)
        </section>

        <section aria-labelledby="manager-states" class="min-w-0 space-y-6">
            <x-ui.section-label as="h2" id="manager-states">File manager states</x-ui.section-label>
            @livewire(\Workbench\App\Livewire\Files\StatesDemo::class)
            <x-ui.file-manager id="fm-list" label="List view example" :breadcrumbs="[['id' => null, 'label' => 'List view example']]" :items="$static" view="list" :persist="false" now="2026-11-16 12:00:00" />
        </section>

        <section aria-labelledby="preview" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="preview">File preview</x-ui.section-label>
            @livewire(\Workbench\App\Livewire\Files\PreviewDemo::class)
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.button variant="outline" size="sm" x-data x-on:click="$dispatch('open-dialog', 'preview-sheet')">Open in a sheet</x-ui.button>
                <x-ui.text role="meta">The sheet variant, with a text file.</x-ui.text>
            </div>
            <x-ui.file-preview variant="sheet" name="preview-sheet" :file="['key' => 'sheet-notes', 'name' => 'Meeting notes.md', 'url' => route('files-demo.file', 'notes'), 'type' => 'text/markdown', 'size' => 3200, 'modified' => '2026-11-13 10:00:00', 'text' => \Workbench\App\Files\DemoFileController::text('Meeting notes.md')]" now="2026-11-16 12:00:00" />
        </section>

        <section aria-labelledby="cards" class="min-w-0 space-y-6">
            <x-ui.section-label as="h2" id="cards">File cards and attachment lists</x-ui.section-label>
            <x-ui.attachment-list label="Rows with every status" :items="$rows" :actions="['download', 'retry', 'remove', 'cancel']" />
            <x-ui.attachment-list label="Cards" variant="card" :columns="4" :items="$cards" :actions="['download', 'retry', 'remove']" />
            <div id="chips" class="min-w-0 space-y-2">
                <x-ui.text role="meta">Compact links (chat, timelines, tickets):</x-ui.text>
                <x-ui.attachment-list label="Message attachments" variant="compact" :items="$chips" />
            </div>
            <x-ui.attachment-list label="No attachments" :items="[]" empty="No attachments on this ticket." />
        </section>

        <section aria-labelledby="fields" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="fields">File field: limits, reorder, paste, camera, chunked</x-ui.section-label>
            @livewire(\Workbench\App\Livewire\Files\UploadFieldsDemo::class)
        </section>
    </div>
</x-workbench::gallery.layout>
