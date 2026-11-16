{{--
    Workbench /community: posts, reactions, comment threads, polls, profiles, member
    cards, the inbox, channels and a leaderboard on neutral sample data. The live parts
    are small Livewire children (workbench/app/Livewire/Community).
--}}
@php
    $channels = [
        ['key' => 'starred', 'label' => 'Starred', 'channels' => [
            ['key' => 'showcase', 'name' => 'december-showcase', 'href' => '#showcase', 'active' => true],
            ['key' => 'finance', 'name' => 'finance-approvals', 'href' => '#finance', 'mentions' => 3, 'private' => true],
        ]],
        ['key' => 'channels', 'label' => 'Channels', 'channels' => [
            ['key' => 'general', 'name' => 'general', 'href' => '#general', 'unread' => true],
            ['key' => 'creators', 'name' => 'creators-lagos-abuja-and-port-harcourt', 'href' => '#creators', 'unread' => true],
            ['key' => 'random', 'name' => 'random', 'href' => '#random', 'muted' => true],
        ]],
        ['key' => 'archived', 'label' => 'Archived', 'open' => false, 'channels' => [
            ['key' => 'summer', 'name' => 'summer-tour-2026', 'href' => '#summer', 'unread' => true],
        ]],
    ];
    $board = [
        ['key' => 'r1', 'rank' => 1, 'label' => 'Tunde Bakare', 'sub' => 'Afrobeats', 'value' => 48210, 'change' => 2],
        ['key' => 'r2', 'rank' => 2, 'label' => 'Amaka Obi', 'sub' => 'Highlife', 'value' => 45990, 'change' => -1],
        ['key' => 'r3', 'rank' => 3, 'label' => 'Ngozi Eze', 'sub' => 'Gospel', 'value' => 39100, 'change' => 0],
        ['key' => 'r4', 'rank' => 4, 'label' => 'Kelechi Nwosu-Adeyemi', 'sub' => 'Amapiano', 'value' => 30440, 'new' => true],
        ['key' => 'r5', 'rank' => 5, 'label' => 'Dayo Bello', 'sub' => 'Street pop', 'value' => 28800, 'change' => 3, 'current' => true],
        ['key' => 'r6', 'rank' => 6, 'label' => 'Efe Okoro', 'sub' => 'R&B', 'value' => 21075, 'change' => -4],
    ];
@endphp
<x-workbench::gallery.layout title="Community">
    <x-ui.page-header eyebrow="UI kit" title="Community" description="Posts, reactions, comments, polls, people, channels and rankings. Optimistic where it helps, one Livewire call per action." />

    <div class="space-y-10" data-community-gallery>
        <section aria-labelledby="feed" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="feed">Feed</x-ui.section-label>
            <div class="max-w-2xl">
                @livewire(\Workbench\App\Livewire\Community\FeedDemo::class)
            </div>
        </section>

        <section aria-labelledby="reactions" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="reactions">Reactions</x-ui.section-label>
            <x-ui.reactions label="Reactions to the announcement" subject="announcement" :reactions="[['key' => 'like', 'count' => 1240, 'mine' => true], ['key' => 'love', 'count' => 18], ['key' => 'wow', 'count' => 2]]" />
            <x-ui.reactions label="Reactions with a custom set" subject="release" size="sm" :options="['up' => ['▲', 'Agree'], 'down' => ['▼', 'Disagree'], 'question' => ['?', 'Question']]" :reactions="[['key' => 'up', 'count' => 7]]" />
        </section>

        @livewire(\Workbench\App\Livewire\Community\PeopleDemo::class)

        <div class="grid grid-cols-1 gap-10 lg:grid-cols-2">
            <section aria-labelledby="channels" class="min-w-0 space-y-3">
                <x-ui.section-label as="h2" id="channels">Channel list</x-ui.section-label>
                <div class="max-w-xs">
                    <x-ui.channel-list label="Workspace channels" :sections="$channels" />
                </div>
            </section>

            <section aria-labelledby="leaderboard" class="min-w-0 space-y-3">
                <x-ui.section-label as="h2" id="leaderboard">Leaderboard</x-ui.section-label>
                <x-ui.leaderboard label="Top artists this week" value-label="streams" :rows="$board" />
            </section>
        </div>
    </div>
</x-workbench::gallery.layout>
