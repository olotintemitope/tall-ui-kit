{{-- Text and code: the app reads its own file and passes the text (capped by maxText) --}}
<x-ui.file-preview now="2026-11-16 12:00:00" :file="[
    'key' => 'notes', 'name' => 'Meeting notes.md', 'url' => '#notes', 'type' => 'text/markdown',
    'size' => 3200, 'modified' => '2026-11-13 10:00:00', 'text' => \Workbench\App\Files\DemoFileController::text('Meeting notes.md'),
]" />
