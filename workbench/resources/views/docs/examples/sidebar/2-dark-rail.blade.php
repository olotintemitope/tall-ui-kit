{{-- Dark sidebar, collapsed to the icon rail --}}
<div class="relative overflow-hidden rounded-lg border border-line">
    <x-ui.sidebar variant="dark" :fixed="false" :collapsed="true" label="Admin">
        <x-slot:header><x-ui.sidebar.header home="#" /></x-slot:header>
        <x-slot:nav>
            <x-ui.sidebar.section label="Money">
                <x-ui.sidebar.item href="#" icon="layout-dashboard" label="Overview" :active="true" />
                <x-ui.sidebar.item href="#" icon="credit-card" label="Transactions" :count="128" :active="false" />
                <x-ui.sidebar.item href="#" icon="wallet" label="Payouts" :count="3" :active="false" />
            </x-ui.sidebar.section>
            <x-ui.sidebar.section label="People">
                <x-ui.sidebar.item href="#" icon="users" label="Customers" :active="false" />
                <x-ui.sidebar.item href="#" icon="settings" label="Settings" :active="false" />
            </x-ui.sidebar.section>
        </x-slot:nav>
        <x-slot:footer>
            <x-ui.sidebar.account name="Tunde Bakare" email="tunde@kudipay.ng" role="Admin" settings-href="#" />
        </x-slot:footer>
    </x-ui.sidebar>
</div>
