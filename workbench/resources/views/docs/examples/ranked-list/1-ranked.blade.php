{{-- Ranked rows with links --}}
<div class="max-w-lg">
    <x-ui.ranked-list>
        <x-ui.ranked-item :rank="1" label="Zenith Foods" sub="38 invoices" href="#zenith-foods"><x-ui.money :value="6420000" /></x-ui.ranked-item>
        <x-ui.ranked-item :rank="2" label="Kano Agro Ltd" sub="21 invoices" href="#kano-agro"><x-ui.money :value="3180000" /></x-ui.ranked-item>
        <x-ui.ranked-item :rank="3" label="Adebayo-Williams Logistics" sub="9 invoices" href="#adebayo-williams"><x-ui.money :value="1250000" /></x-ui.ranked-item>
    </x-ui.ranked-list>
</div>
