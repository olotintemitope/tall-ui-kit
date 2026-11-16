{{-- Taller, invalid and disabled --}}
<div class="flex max-w-xl flex-col gap-4">
    <x-ui.field label="Pitch to curators" name="textareaPitch" error="Write at least 80 characters.">
        <x-ui.textarea rows="5">Afrobeats single, out Friday.</x-ui.textarea>
    </x-ui.field>
    <x-ui.field label="Reason for refund" name="textareaRefund" hint="Locked once the refund is sent.">
        <x-ui.textarea disabled>Duplicate charge on order ORD-55120.</x-ui.textarea>
    </x-ui.field>
</div>
