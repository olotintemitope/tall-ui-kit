{{-- Light sidebar, static preview (:fixed="false") --}}
<div class="relative overflow-hidden rounded-lg border border-line">
    <x-ui.sidebar variant="light" :fixed="false" label="Workspace">
        <x-slot:header><x-ui.sidebar.header home="#" /></x-slot:header>
        <x-slot:nav>
            <x-ui.sidebar.section>
                <x-ui.sidebar.item href="#" icon="layout-dashboard" label="Overview" :active="false" />
                <x-ui.sidebar.item href="#" icon="credit-card" label="Transactions" :count="128" :active="true" />
                <x-ui.sidebar.item href="#" icon="wallet" label="Payouts" badge="3" badge-variant="warning" :active="false" />
            </x-ui.sidebar.section>
            <x-ui.sidebar.section label="Campaigns">
                <x-ui.sidebar.group label="Campaigns" icon="megaphone">
                    <x-ui.sidebar.item href="#" label="Running" nested :active="false" />
                    <x-ui.sidebar.item href="#" label="Drafts" :count="4" nested :active="false" />
                </x-ui.sidebar.group>
                <x-ui.sidebar.item href="#" icon="chart-column" label="Reports" new :active="false" />
                <x-ui.sidebar.item href="#" icon="landmark" label="Billing" disabled reason="Only owners can open billing." />
                <x-ui.sidebar.item href="https://wa.me/2348000000000" icon="message-circle" label="Support" external />
            </x-ui.sidebar.section>
        </x-slot:nav>
        <x-slot:footer>
            <x-ui.sidebar.account name="Adaeze Nwosu" email="adaeze@kudipay.ng" role="Owner" settings-href="#" />
        </x-slot:footer>
    </x-ui.sidebar>
</div>
