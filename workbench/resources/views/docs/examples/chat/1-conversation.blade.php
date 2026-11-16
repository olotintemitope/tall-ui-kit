{{-- A conversation from data, with day separators, grouping and the composer --}}
<x-ui.chat
    label="Conversation with Ada Obi"
    framed
    height="h-96"
    now="2026-11-16 14:00:00"
    :typing="['Ada']"
    :maxlength="2000"
    id="docs-chat"
    :messages="[
        ['key' => 'd1', 'author' => 'Ada Obi', 'time' => '2026-11-15 09:12:00', 'body' => 'The October statement is ready.'],
        ['key' => 'd2', 'author' => 'Ada Obi', 'time' => '2026-11-15 09:13:00', 'body' => 'Totals match the bank: https://example.com/statements/october'],
        ['key' => 'd3', 'mine' => true, 'time' => '2026-11-16 09:40:00', 'body' => 'Thanks @ada, checking it now.', 'seen' => true],
    ]"
>
    <x-slot:composer>
        <x-ui.chat.composer label="Message to Ada Obi" :maxlength="2000" id="docs-chat-composer" />
    </x-slot:composer>
</x-ui.chat>
