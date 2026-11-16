{{-- Single selection, bound with x-model --}}
<div x-data="{ folder: 'invoices' }" class="max-w-sm space-y-2">
    <x-ui.tree
        label="Folder"
        id="tree-folder-pick"
        selectable
        x-model="folder"
        value="invoices"
        :expanded="['finance']"
        :nodes="[
            ['id' => 'finance', 'label' => 'Finance', 'children' => [
                ['id' => 'invoices', 'label' => 'Invoices'],
                ['id' => 'receipts', 'label' => 'Receipts'],
                ['id' => 'payroll', 'label' => 'Payroll', 'disabled' => true],
            ]],
            ['id' => 'contracts', 'label' => 'Contracts', 'children' => [
                ['id' => 'suppliers', 'label' => 'Suppliers'],
                ['id' => 'curators', 'label' => 'Curators'],
            ]],
        ]"
    />
    <p class="type-meta">Selected: <span class="type-numeric text-ink-body" x-text="folder || 'none'"></span></p>
</div>
