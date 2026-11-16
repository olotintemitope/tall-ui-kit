{{-- Slot rows with richer content, unbordered --}}
<div class="max-w-xl">
    <x-ui.sortable label="Approval steps" id="sortable-approvals" :bordered="false">
        <x-ui.sortable.item key="finance" label="Finance review" help="sortable-approvals-help">
            <p class="type-meta">Funmi Lawal checks the amount against the invoice.</p>
        </x-ui.sortable.item>
        <x-ui.sortable.item key="compliance" label="Compliance" help="sortable-approvals-help">
            <p class="type-meta">Bank details must match the BVN record.</p>
        </x-ui.sortable.item>
        <x-ui.sortable.item key="release" label="Release" help="sortable-approvals-help">
            <p class="type-meta">Gbenga Musa releases the batch at 15:00.</p>
        </x-ui.sortable.item>
    </x-ui.sortable>
</div>
