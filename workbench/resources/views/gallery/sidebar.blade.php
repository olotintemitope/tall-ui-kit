<x-workbench::gallery.layout title="Sidebar">
    <x-ui.page-header eyebrow="Gallery" title="Sidebar" description="Static previews of the dark and light variants. In a shell the sidebar is fixed, collapses to an icon rail and becomes a drawer below lg." />

    <div class="grid gap-6 md:grid-cols-2">
        @foreach (['dark' => 'Dark sidebar', 'light' => 'Light sidebar'] as $variant => $label)
            <div class="h-[520px] overflow-auto rounded-lg border border-line">
                <x-ui.sidebar :variant="$variant" :fixed="false" :label="$label">
                    <x-slot:header><x-ui.sidebar.header home="#" /></x-slot:header>
                    <x-slot:nav>
                        <x-ui.sidebar.section>
                            <x-ui.sidebar.item href="#" icon="layout-dashboard" label="Overview" :active="true" />
                            <x-ui.sidebar.item href="#" icon="credit-card" label="Transactions" :count="128" />
                            <x-ui.sidebar.item href="#" icon="wallet" label="Payouts" badge="3" badge-variant="warning" />
                        </x-ui.sidebar.section>
                        <x-ui.sidebar.section label="Workspace">
                            <x-ui.sidebar.item href="#" icon="users" label="Team" />
                            <x-ui.sidebar.item href="#" icon="chart-column" label="Reports" new />
                            <x-ui.sidebar.item href="#" icon="settings" label="Billing" disabled reason="Owners only" />
                        </x-ui.sidebar.section>
                    </x-slot:nav>
                    <x-slot:footer>
                        <x-ui.sidebar.account name="Ada Obi" email="ada@example.com" role="Owner" settings-href="#" />
                    </x-slot:footer>
                </x-ui.sidebar>
            </div>
        @endforeach
    </div>
</x-workbench::gallery.layout>
