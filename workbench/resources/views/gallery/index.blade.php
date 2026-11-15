<x-workbench::gallery.layout title="Basics">
    <x-ui.page-header eyebrow="Gallery" title="Basics" description="Type roles, buttons in every variant and state, badges, notices and inline elements." />

    <div class="space-y-8">
        <section aria-labelledby="type" class="space-y-3">
            <x-ui.section-label as="h2" id="type">Typography</x-ui.section-label>
            <x-ui.heading :level="2" size="title">Page title, title role</x-ui.heading>
            <x-ui.heading :level="3" size="heading">Section heading, heading role</x-ui.heading>
            <x-ui.heading :level="4" size="subheading">Sub-section, subheading role</x-ui.heading>
            <x-ui.text>Body text runs at 14/20 in ink-body. Long unbroken strings such as payouts-team@an-extremely-long-domain-name.example.com wrap instead of being cut off.</x-ui.text>
            <x-ui.text role="meta">Meta line, 13/18 in ink-muted.</x-ui.text>
            <x-ui.text role="caption">Caption, 12/16, for hints and footnotes.</x-ui.text>
            <x-ui.text>Inline: <x-ui.link href="#type">a text link</x-ui.link>, <x-ui.code>TXN-9F3C21AB</x-ui.code>, <x-ui.kbd>⌘K</x-ui.kbd> and <x-ui.money :value="1250000" /> with <x-ui.number :value="1204" />.</x-ui.text>
            <x-ui.code-block label="Payload" copy>{{ '{"reference": "TXN-9F3C21AB", "status": "completed"}' }}</x-ui.code-block>
        </section>

        <section aria-labelledby="buttons" class="space-y-3">
            <x-ui.section-label as="h2" id="buttons">Buttons</x-ui.section-label>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button>Save changes</x-ui.button>
                <x-ui.button variant="secondary">Secondary</x-ui.button>
                <x-ui.button variant="outline">Outline</x-ui.button>
                <x-ui.button variant="ghost">Ghost</x-ui.button>
                <x-ui.button variant="destructive">Delete</x-ui.button>
                <x-ui.button variant="link">Link button</x-ui.button>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button size="sm">Small</x-ui.button>
                <x-ui.button size="lg">Large</x-ui.button>
                <x-ui.button variant="outline"><x-ui.icon name="download" /> With icon</x-ui.button>
                <x-ui.button variant="outline" size="icon" aria-label="Settings"><x-ui.icon name="settings" /></x-ui.button>
                <x-ui.button variant="outline" :pressed="true">Toggle on</x-ui.button>
                <x-ui.button disabled>Disabled</x-ui.button>
                <x-ui.button variant="outline" disabled>Disabled outline</x-ui.button>
                <x-ui.button busy>Saving</x-ui.button>
            </div>
            <div class="flex flex-wrap items-center gap-2" aria-label="Forced states">
                <x-ui.button data-force="hover">Hover</x-ui.button>
                <x-ui.button data-force="active">Pressed</x-ui.button>
                <x-ui.button variant="outline" data-force="hover">Outline hover</x-ui.button>
            </div>
        </section>

        <section aria-labelledby="badges" class="space-y-3">
            <x-ui.section-label as="h2" id="badges">Badges and notices</x-ui.section-label>
            <div class="flex flex-wrap gap-2">
                <x-ui.badge>Neutral</x-ui.badge>
                <x-ui.badge variant="success">Completed</x-ui.badge>
                <x-ui.badge variant="warning">Pending</x-ui.badge>
                <x-ui.badge variant="danger">Failed</x-ui.badge>
                <x-ui.badge variant="info">Verified</x-ui.badge>
                <x-ui.badge variant="accent">New</x-ui.badge>
                <x-ui.badge variant="outline">Tag</x-ui.badge>
            </div>
            <x-ui.alert tone="success">Payout sent. It reaches the bank within one working day.</x-ui.alert>
            <x-ui.alert tone="warning">Two campaigns end this week.</x-ui.alert>
            <x-ui.alert tone="danger">The bank rejected the transfer. Check the account number.</x-ui.alert>
            <x-ui.alert>Exports include every column you can see.</x-ui.alert>
        </section>

        <section aria-labelledby="misc" class="space-y-3">
            <x-ui.section-label as="h2" id="misc">Avatars, filter tokens, loaders</x-ui.section-label>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.avatar name="Ada Obi" size="sm" />
                <x-ui.avatar name="Tunde Bakare" />
                <x-ui.avatar name="Kemi Adeyemi" size="lg" tone="dark" />
                <ul class="flex flex-wrap gap-2" aria-label="Active filters">
                    <li><x-ui.filter-token label="Status: Pending" /></li>
                    <li><x-ui.filter-token label="Platform: Playlist" /></li>
                </ul>
                <x-ui.spinner />
                <x-ui.loading-inline>Loading transactions</x-ui.loading-inline>
            </div>
        </section>
    </div>
</x-workbench::gallery.layout>
