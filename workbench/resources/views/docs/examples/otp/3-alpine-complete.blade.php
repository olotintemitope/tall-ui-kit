{{-- Bound value and the otp-complete event --}}
<div x-data="{ code: '' }" x-on:otp-complete="$toast({ variant: 'success', title: 'Phone number verified.' })" class="flex flex-col gap-3">
    <x-ui.otp id="otp-phone" x-model="code" label="Code sent to +234 803 ••• 4567" />
    <p class="type-meta">Entered: <span class="type-numeric text-ink" x-text="code || 'nothing yet'">nothing yet</span></p>
</div>
