{{-- Meter: a measurement in a known range --}}
<div class="grid gap-6 md:grid-cols-2">
    <x-ui.meter label="Acceptance rate" :value="72" :low="40" :high="65" :optimum="100" />
    <x-ui.meter label="Storage used" :value="88" :low="60" :high="80" :optimum="0" />
</div>
