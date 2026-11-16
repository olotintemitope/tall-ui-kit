{{-- A payload with a label and a copy button --}}
<x-ui.code-block label="Webhook payload: payout.paid" copy>{{ json_encode([
    'event' => 'payout.paid',
    'reference' => 'PO-7F21C9',
    'amount' => 325000,
    'currency' => 'NGN',
    'paid_at' => '2026-11-15T14:02:11+01:00',
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</x-ui.code-block>
