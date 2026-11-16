{{-- Workbench /files: PreviewDemo (Previous / Next run show()). The dialog variant is on the file manager above (open a file). --}}
<div class="min-w-0 space-y-3" data-preview-demo>
    <x-ui.file-preview :file="$file" :files="$files" navigate-action="show" now="2026-11-16 12:00:00" />
</div>
