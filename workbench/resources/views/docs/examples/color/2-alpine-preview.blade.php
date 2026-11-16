{{-- Bound with x-model --}}
<div x-data="{ accent: '#B4232C' }" class="flex flex-wrap items-end gap-4">
    <x-ui.color x-model="accent" label="Link colour" swatch-label="Pick the link colour" />
    <p class="type-meta">Value: <span class="type-numeric text-ink" x-text="accent">#B4232C</span></p>
</div>
