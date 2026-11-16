{{-- Workbench /files: StatesDemo. --}}
<div class="grid min-w-0 grid-cols-1 gap-8 lg:grid-cols-3" data-states-demo>
    <x-ui.file-manager id="fm-loading" label="Loading example" :breadcrumbs="[['id' => null, 'label' => 'Loading example']]" loading :persist="false" />
    <x-ui.file-manager id="fm-error" label="Error example" :breadcrumbs="[['id' => null, 'label' => 'Error example']]" :error="$failed ? 'The folder could not be loaded. Check your connection.' : null" retry-action="retry" :items="$failed ? [] : [['id' => 'e1', 'name' => 'Recovered.pdf', 'size' => 1200, 'modified' => '2026-11-16 09:00:00']]" :persist="false" now="2026-11-16 12:00:00" />
    <x-ui.file-manager id="fm-empty" label="Empty example" :breadcrumbs="[['id' => null, 'label' => 'Empty example']]" :items="[]" :persist="false" />
</div>
