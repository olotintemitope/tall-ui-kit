{{-- Bars: sizes, tones, formats and indeterminate --}}
<div class="grid gap-6 md:grid-cols-2">
    <x-ui.progress label="Profile complete" :value="60" />
    <x-ui.progress label="Delivered streams" :value="1240" :max="2000" format="count" size="md" label-position="end" tone="success" />
    <x-ui.progress label="Video upload" :value="2100000" :max="5000000" :buffer="3400000" format="bytes" size="lg" />
    <x-ui.progress label="Storage used" :value="92" tone="warning" size="md" />
    <x-ui.progress label="Import failed at row 412" :value="35" tone="danger" label-position="end" />
    <x-ui.progress label="Checking the file" />
</div>
