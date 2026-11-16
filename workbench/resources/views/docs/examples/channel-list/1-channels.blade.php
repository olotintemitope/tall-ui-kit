{{-- Sections, unread dots, mentions and a collapsed section --}}
<div class="max-w-xs">
    <x-ui.channel-list label="Docs channels" :sections="[
        ['key' => 'starred', 'label' => 'Starred', 'channels' => [
            ['key' => 'showcase', 'name' => 'december-showcase', 'href' => '#showcase', 'active' => true],
            ['key' => 'finance', 'name' => 'finance-approvals', 'href' => '#finance', 'mentions' => 3, 'private' => true],
        ]],
        ['key' => 'channels', 'label' => 'Channels', 'channels' => [
            ['key' => 'general', 'name' => 'general', 'href' => '#general', 'unread' => true],
            ['key' => 'random', 'name' => 'random', 'href' => '#random', 'muted' => true],
        ]],
        ['key' => 'archived', 'label' => 'Archived', 'open' => false, 'channels' => [
            ['key' => 'summer', 'name' => 'summer-tour-2026', 'href' => '#summer', 'unread' => true],
        ]],
    ]" />
</div>
