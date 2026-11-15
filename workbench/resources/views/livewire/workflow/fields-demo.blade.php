{{-- Workbench /workflow: FieldsDemo. --}}
<div class="grid min-w-0 grid-cols-1 gap-6 md:grid-cols-2" data-fields-demo>
    <div class="min-w-0 space-y-2">
        <x-ui.field label="Genres" name="genres" hint="Up to 5. Press Enter or a comma after each, or paste a list.">
            <x-ui.tag-input wire:model.live="genres" :value="$genres" :max="5" :max-length="24" :suggestions="['Afrobeats', 'Amapiano', 'Alté', 'Fuji', 'Gospel', 'Highlife', 'Hip-hop', 'Juju', 'R&B']" />
        </x-ui.field>
        <p class="type-meta" data-genres-server>On the server: <span class="type-numeric text-ink-body">{{ $genres ? implode(', ', $genres) : 'none' }}</span></p>
    </div>
    <div class="min-w-0 space-y-5">
        <x-ui.slider label="Budget" :min="5000" :max="100000" :step="2500" format="money" wire:model.live="budget" :value="$budget" bounds />
        <x-ui.slider label="Followers" dual :min="0" :max="1000000" :step="10000" :page-step="100000" wire:model.live="followers" :value="$followers" />
        <p class="type-meta" data-slider-server>On the server: <span class="type-numeric text-ink-body">{{ number_format($budget) }}; {{ number_format($followers[0]) }} to {{ number_format($followers[1]) }}</span></p>
    </div>
</div>
