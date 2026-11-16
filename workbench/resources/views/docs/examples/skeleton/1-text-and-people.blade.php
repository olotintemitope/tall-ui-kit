{{-- Text, a person and custom lines --}}
<div class="grid gap-6 sm:grid-cols-2">
    <x-ui.skeleton.text :lines="3" label="Loading the campaign brief" />
    <div class="flex flex-col gap-4">
        <x-ui.skeleton.avatar label="Loading the curator" />
        <div role="status" class="flex flex-col">
            <span class="sr-only">Loading the payout summary</span>
            <x-ui.skeleton.line role="label" width="w-24" />
            <x-ui.skeleton.line role="figure" width="w-40" />
        </div>
    </div>
</div>
