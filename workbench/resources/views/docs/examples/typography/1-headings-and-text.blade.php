{{-- Headings and text roles --}}
<div class="space-y-3">
    <x-ui.heading :level="2" size="title">Payouts</x-ui.heading>
    <x-ui.heading :level="3">November payout run</x-ui.heading>
    <x-ui.heading :level="4" size="subheading">Bank transfers</x-ui.heading>
    <x-ui.section-label>Summary</x-ui.section-label>
    <x-ui.text>42 curators are due ₦1,512,000 on 20 November. Requests made after 18:00 go into the next run.</x-ui.text>
    <x-ui.text role="body-strong">Approve before 15:00 to pay today.</x-ui.text>
    <x-ui.text role="meta">Last run: 13 Nov 2026, paid in 2 hours.</x-ui.text>
    <x-ui.text role="caption" tone="muted">Times are in West Africa Time.</x-ui.text>
    <x-ui.text tone="danger">Two accounts failed name checks.</x-ui.text>
    <x-ui.text tone="success">All invoices for October are settled.</x-ui.text>
</div>
