{{-- Workbench /community: PeopleDemo. --}}
<div class="min-w-0 space-y-10" data-people-demo>
    <section aria-labelledby="profile-header" class="min-w-0 space-y-3">
        <x-ui.section-label as="h2" id="profile-header">Profile header</x-ui.section-label>
        <x-ui.profile-header
            key="u-ada"
            name="Ada Obi"
            handle="adaobi"
            role="Events lead, Lagos"
            bio="Runs the December showcase and the monthly creator meet-ups. Messages about bookings go to the events channel."
            verified
            :following="$following['u-ada']"
            follow-action="follow"
            message-href="#chat"
            :level="3"
            :stats="[['label' => 'Followers', 'value' => 12480], ['label' => 'Following', 'value' => 312], ['label' => 'Posts', 'value' => 1045], ['label' => 'Events', 'value' => 38]]"
        />
    </section>

    <section aria-labelledby="member-cards" class="min-w-0 space-y-3">
        <x-ui.section-label as="h2" id="member-cards">Member card</x-ui.section-label>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.member-card key="u-bola" name="Bola Ade" role="Producer, works on the showcase sound and stage plan" :following="$following['u-bola']" follow-action="follow" message-href="#chat" :stats="[['label' => 'Followers', 'value' => 2310], ['label' => 'Posts', 'value' => 88]]" />
            <x-ui.member-card key="u-chidi" name="Chidi Eze" role="Finance" :following="$following['u-chidi']" follow-action="follow" message-href="#chat" :stats="[['label' => 'Followers', 'value' => 940], ['label' => 'Posts', 'value' => 12]]" />
            <x-ui.member-card key="u-efe" name="Efe Okoro-Williamson-Adebayo" role="Partnerships" :following="$following['u-efe']" follow-action="follow" :stats="[['label' => 'Followers', 'value' => 1000000], ['label' => 'Posts', 'value' => 3]]" />
        </div>
    </section>

    <section aria-labelledby="inbox" class="min-w-0 space-y-3">
        <x-ui.section-label as="h2" id="inbox">Inbox</x-ui.section-label>
        <div class="max-w-md rounded-lg border border-line">
            <x-ui.chat.inbox
                label="Conversations"
                action="openConversation"
                :now="$now"
                :conversations="[
                    ['key' => 'k1', 'name' => 'Bola Ade', 'preview' => 'Rates are in the folder too.', 'time' => '2026-11-16 13:12:00', 'unread' => $unread['k1'], 'active' => $open === 'k1'],
                    ['key' => 'k2', 'name' => 'Showcase crew', 'people' => ['Ada Obi', 'Bola Ade', 'Chidi Eze', 'Dayo Bello'], 'preview' => 'Dayo: The crew arrives an hour before load-in, so plan the parking behind the hall for the vans and the generator truck.', 'time' => '2026-11-16 12:58:00', 'unread' => $unread['k2'], 'active' => $open === 'k2'],
                    ['key' => 'k3', 'name' => 'Chidi Eze', 'preview' => 'Sent the invoice.', 'time' => '2026-11-15 17:20:00', 'unread' => $unread['k3'], 'active' => $open === 'k3', 'muted' => true],
                    ['key' => 'k4', 'name' => 'Support, ticket TKT-4F21C9', 'preview' => 'We have refunded the duplicate charge.', 'time' => '2026-11-10 08:00:00', 'unread' => $unread['k4'], 'active' => $open === 'k4'],
                ]"
            >
                <x-slot:search>
                    <x-ui.search placeholder="Search conversations" size="sm" width="full" />
                </x-slot:search>
                <x-slot:filters>
                    <x-ui.segmented label="Show" size="sm" :options="['all' => 'All', 'unread' => 'Unread']" value="all" />
                </x-slot:filters>
            </x-ui.chat.inbox>
        </div>
    </section>
</div>
