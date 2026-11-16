{{-- In a list card, and empty --}}
<div class="grid gap-4 md:grid-cols-2">
    <x-ui.card.list title="Top curators" description="By accepted placements, last 30 days" as="ol" view-all="#curators">
        <x-ui.ranked-item :rank="1" label="Yetunde Balogun" sub="Ibadan" :value="48" />
        <x-ui.ranked-item :rank="2" label="Emeka Nwosu" sub="Enugu" :value="41" />
        <x-ui.ranked-item :rank="3" label="Halima Bello" sub="Kaduna" :value="33" />
    </x-ui.card.list>
    <x-ui.ranked-list empty="No refunds in this period." />
</div>
