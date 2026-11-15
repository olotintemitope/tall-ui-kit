{{-- Workbench /workflow: TreeDemo. --}}
@php
    $files = [
        ['id' => 'finance', 'label' => 'Finance', 'expanded' => true, 'children' => [
            ['id' => 'q1-report', 'label' => 'Q1 report.xlsx'],
            ['id' => 'q2-report', 'label' => 'Q2 report.xlsx'],
            ['id' => 'invoices', 'label' => 'Invoices', 'children' => [
                ['id' => 'inv-1042', 'label' => 'INV-1042.pdf'],
                ['id' => 'inv-1043', 'label' => 'INV-1043 for a very long customer name that wraps onto a second line.pdf'],
            ]],
        ]],
        ['id' => 'brand', 'label' => 'Brand', 'children' => [
            ['id' => 'logo', 'label' => 'Logo.svg'],
            ['id' => 'fonts', 'label' => 'Fonts', 'disabled' => true],
        ]],
        ['id' => 'archive', 'label' => 'Archive', 'hasChildren' => true],
    ];
@endphp
<div class="grid min-w-0 grid-cols-1 gap-6 md:grid-cols-2" data-tree-demo>
    <div class="min-w-0 space-y-2">
        <x-ui.tree label="Files to share" :nodes="$files" checkable lazy="children" wire:model.live="checked" :value="$checked" />
        <p class="type-meta" data-tree-checked>Checked on the server: <span class="type-numeric text-ink-body">{{ $checked ? implode(', ', $checked) : 'none' }}</span></p>
    </div>
    <div class="min-w-0 space-y-2">
        <x-ui.tree label="Folder" :nodes="$files" selectable lazy="children" wire:model.live="picked" :value="$picked" :expanded="['finance']" id="tree-folder" />
        <p class="type-meta" data-tree-picked>Selected on the server: <span class="type-numeric text-ink-body">{{ $picked ?? 'none' }}</span></p>
    </div>
</div>
