{{-- A range with presets --}}
<div class="flex flex-wrap gap-4">
    <x-ui.field label="Report period" name="dpPeriod">
        <x-ui.date-range start="2026-11-01" end="2026-11-15" />
    </x-ui.field>
    <x-ui.field label="Campaign dates" name="dpCampaign">
        <x-ui.date-range :presets="false" min="2026-11-16" />
    </x-ui.field>
</div>
