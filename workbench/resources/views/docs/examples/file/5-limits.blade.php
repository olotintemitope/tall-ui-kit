{{-- Limits with per-file errors, reorder, paste and the generated hint (new in v0.9; all optional) --}}
<x-ui.field label="Supporting documents" name="docs_limits">
    <x-ui.file multiple accept=".pdf,image/*" max-size="5MB" :max-files="4" max-total-size="10MB" reorder paste />
</x-ui.field>
