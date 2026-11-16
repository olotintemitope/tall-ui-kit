{{-- Summary cards: one metric each --}}
<div class="grid gap-4 md:grid-cols-3">
    <x-ui.card.summary label="Paid out" :value="8450000" money :previous="7120000" period="30d" :sparkline="[42, 51, 48, 60, 58, 66, 71]" sparkline-label="Paid out, last 7 weeks" />
    <x-ui.card.summary label="Open invoices" :value="37" :previous="44" period="30d" :up-is-good="false" />
    <x-ui.card.summary label="Failed payments" :value="null" sub="Not enough data yet" />
</div>
