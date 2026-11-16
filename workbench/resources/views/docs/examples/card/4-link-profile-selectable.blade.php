{{-- Link, profile and selectable cards --}}
<div class="space-y-4">
    <div class="grid gap-4 md:grid-cols-2">
        <x-ui.card.link href="#billing" title="Billing settings" meta="Plan, tax ID and invoice email" />
        <x-ui.card.profile name="Yetunde Balogun" meta="Curator, Ibadan" verified :stats="['Playlists' => '18', 'Followers' => '62.4k', 'Accepted' => '91%']">
            <x-slot:actions><x-ui.button variant="outline" size="sm">Message</x-ui.button></x-slot:actions>
        </x-ui.card.profile>
    </div>
    <fieldset class="space-y-2">
        <legend class="type-field-label mb-2">Campaign package</legend>
        <x-ui.card.selectable name="package" value="starter" title="Starter" meta="5 curators, one week" :price="45000" checked />
        <x-ui.card.selectable name="package" value="growth" title="Growth" :meta="['15 curators, two weeks', 'Weekly report']" :price="120000" :badges="[['Beta', 'info']]" />
        <x-ui.card.selectable name="package" value="label" title="Label" meta="40 curators, a month" :price="300000" disabled reason="Needs a verified business account." />
    </fieldset>
</div>
