{{-- Conversations with search, a group chat and unread counts --}}
<div class="max-w-md rounded-lg border border-line">
    <x-ui.chat.inbox label="Docs conversations" now="2026-11-16 14:00:00" :conversations="[
        ['key' => 'a', 'name' => 'Bola Ade', 'preview' => 'Rates are in the folder too.', 'time' => '2026-11-16 13:12:00', 'active' => true, 'href' => '#bola'],
        ['key' => 'b', 'name' => 'Showcase crew', 'people' => ['Ada Obi', 'Bola Ade', 'Chidi Eze'], 'preview' => 'Dayo: Parking is behind the hall.', 'time' => '2026-11-16 12:58:00', 'unread' => 3, 'href' => '#crew'],
        ['key' => 'c', 'name' => 'Chidi Eze', 'preview' => 'Sent the invoice.', 'time' => '2026-11-15 17:20:00', 'muted' => true, 'href' => '#chidi'],
    ]">
        <x-slot:search>
            <x-ui.search placeholder="Search conversations" size="sm" width="full" />
        </x-slot:search>
    </x-ui.chat.inbox>
</div>
