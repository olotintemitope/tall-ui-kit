{{-- Checkable: parents show mixed when some children are checked --}}
<div x-data="{ files: ['q1-report'] }" class="max-w-sm space-y-2">
    <x-ui.tree
        label="Files to share"
        id="tree-files-share"
        checkable
        x-model="files"
        :value="['q1-report']"
        :expanded="['reports']"
        :nodes="[
            ['id' => 'reports', 'label' => 'Reports', 'children' => [
                ['id' => 'q1-report', 'label' => 'Q1 report.xlsx'],
                ['id' => 'q2-report', 'label' => 'Q2 report.xlsx'],
                ['id' => 'q3-report', 'label' => 'Q3 report for Adebayo-Williams Logistics, revised after audit.xlsx'],
            ]],
            ['id' => 'logo', 'label' => 'Logo.svg'],
        ]"
    />
    <p class="type-meta wrap-anywhere">Checked: <span class="type-numeric text-ink-body" x-text="files.length ? files.join(', ') : 'none'"></span></p>
</div>
