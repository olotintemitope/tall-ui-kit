{{-- Ring, where a bar does not fit --}}
<div class="flex flex-wrap items-center gap-6">
    <x-ui.progress-ring label="Syncing payouts" :value="25" :size="24" />
    <x-ui.progress-ring label="Uploading cover art" :value="62" :size="40" show-value />
    <x-ui.progress-ring label="Course progress" :value="80" :size="64" show-value tone="success" />
    <x-ui.progress-ring label="Loading the report" :size="40" />
</div>
