{{-- Tabs with panels, bound with x-model --}}
<div x-data="{ tab: 'payouts' }">
    <x-ui.tabs label="Account sections" x-model="tab" :tabs="[
        'profile' => 'Profile',
        'payouts' => ['label' => 'Payouts', 'count' => 3],
        'team' => 'Team',
        'audit' => ['label' => 'Audit log', 'disabled' => true],
    ]">
        <x-ui.tabs.panel name="profile">
            <x-ui.text>Business name, address and tax number.</x-ui.text>
        </x-ui.tabs.panel>
        <x-ui.tabs.panel name="payouts">
            <x-ui.text>Three payouts are waiting for review.</x-ui.text>
        </x-ui.tabs.panel>
        <x-ui.tabs.panel name="team">
            <x-ui.text>Eight people can see this account.</x-ui.text>
        </x-ui.tabs.panel>
    </x-ui.tabs>
</div>
