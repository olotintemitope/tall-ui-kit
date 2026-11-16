{{-- Slot mode with absolute times and a rich body --}}
<x-ui.timeline label="Invoice INV-2041 history" id="invoice-history">
    <x-ui.timeline.item time="2026-11-14 10:00:00" actor="Ngozi Eze" text="created the invoice." time-mode="absolute" />
    <x-ui.timeline.item time="2026-11-14 10:02:00" actor="Billing" text="emailed it to accounts@zenithfoods.example.com." time-mode="absolute" />
    <x-ui.timeline.item time="2026-11-15 08:30:00" actor="Ngozi Eze" text="changed the due date." :status="['Edited', 'neutral']" time-mode="absolute">
        <x-ui.dl layout="inline" dense :items="[
            ['label' => 'Before', 'value' => '21 Nov 2026'],
            ['label' => 'After', 'value' => '28 Nov 2026'],
        ]" />
    </x-ui.timeline.item>
</x-ui.timeline>
