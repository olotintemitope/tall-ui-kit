{{-- Date and time, and time alone --}}
<div class="flex flex-wrap gap-4">
    <x-ui.field label="Send invoice at" name="dpSendAt">
        <x-ui.datetime value="2026-11-20T09:00" :minute-step="15" />
    </x-ui.field>
    <x-ui.field label="Daily cut-off" name="dpCutoff">
        <x-ui.time-input value="16:00" hours="12" />
    </x-ui.field>
</div>
