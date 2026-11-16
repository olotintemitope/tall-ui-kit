{{-- Confirm: a question, one consequence, a verb --}}
<div x-data x-on:confirmed.window="$event.detail === 'send-payout' && $toast({ variant: 'success', title: 'Payout sent.' })">
    <x-ui.button x-on:click="$dispatch('open-dialog', 'send-payout')">Send payout</x-ui.button>
</div>

<x-ui.confirm name="send-payout" title="Send this payout now?" description="₦1,250,000 leaves today and cannot be recalled." confirm-label="Send payout" />
