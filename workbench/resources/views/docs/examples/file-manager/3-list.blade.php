{{-- List view, and the loading, error and empty states --}}
<div class="space-y-8">
    <x-ui.file-manager id="docs-fm-list" label="Invoices" view="list" :persist="false" now="2026-11-16 12:00:00"
        :breadcrumbs="[['id' => null, 'label' => 'Invoices']]"
        :items="[
            ['id' => 'p', 'name' => 'Paid', 'folder' => true, 'count' => 18, 'modified' => '2026-11-05 10:00:00'],
            ['id' => 'i1', 'name' => 'INV-1042.pdf', 'size' => 182000, 'modified' => '2026-11-12 10:00:00'],
            ['id' => 'i2', 'name' => 'INV-1043 for a very long customer name that wraps onto a second line.pdf', 'size' => 176000, 'modified' => '2026-11-12 10:00:00'],
        ]" />
    <x-ui.file-manager id="docs-fm-loading" label="Loading" loading :persist="false" :breadcrumbs="[['id' => null, 'label' => 'Shared with me']]" />
    <x-ui.file-manager id="docs-fm-error" label="Error" error="The folder could not be loaded." :persist="false" :breadcrumbs="[['id' => null, 'label' => 'Archive']]" />
    <x-ui.file-manager id="docs-fm-empty" label="Empty" :items="[]" :persist="false" :breadcrumbs="[['id' => null, 'label' => 'Drafts']]" />
</div>
