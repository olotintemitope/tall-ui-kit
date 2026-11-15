<x-workbench::gallery.layout title="Overlays">
    <x-ui.page-header eyebrow="Gallery" title="Overlays" description="Menus and popovers open below their trigger; dialogs are native modal dialogs; toasts sit above modals." />

    <div class="space-y-8">
        <section aria-labelledby="menus" class="space-y-3">
            <x-ui.section-label as="h2" id="menus">Menus, popovers and tooltips</x-ui.section-label>
            <div class="flex flex-wrap items-center gap-3">
                <x-ui.dropdown align="left">
                    <x-slot:trigger>
                        <x-ui.button variant="outline">Actions <x-ui.icon name="chevron-down" /></x-ui.button>
                    </x-slot:trigger>
                    <x-ui.dropdown.label>Payment</x-ui.dropdown.label>
                    <x-ui.dropdown.item><x-ui.icon name="eye" /> View details</x-ui.dropdown.item>
                    <x-ui.dropdown.item><x-ui.icon name="copy" /> Copy reference</x-ui.dropdown.item>
                    <x-ui.dropdown.item><x-ui.icon name="flag" /> Show this customer's other transactions</x-ui.dropdown.item>
                    <x-ui.dropdown.separator />
                    <x-ui.dropdown.item variant="danger"><x-ui.icon name="trash-2" /> Delete</x-ui.dropdown.item>
                </x-ui.dropdown>

                <x-ui.popover label="Fee details" title="How fees work">
                    <x-ui.text role="meta">A 10% platform fee is taken from each payment before payout.</x-ui.text>
                </x-ui.popover>

                <x-ui.tooltip text="Copies the reference">
                    <x-ui.button variant="outline" size="icon" aria-label="Copy reference"><x-ui.icon name="copy" /></x-ui.button>
                </x-ui.tooltip>
            </div>
        </section>

        <section aria-labelledby="dialogs" class="space-y-3">
            <x-ui.section-label as="h2" id="dialogs">Dialogs and confirmations</x-ui.section-label>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'demo-dialog')">Dialog</x-ui.button>
                <x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'demo-sheet')">Sheet</x-ui.button>
                <x-ui.button variant="outline" x-data x-on:click="$dispatch('open-confirm', 'demo-confirm')">Confirm</x-ui.button>
                <x-ui.button variant="destructive" x-data x-on:click="$dispatch('open-confirm', 'demo-destroy')">Delete payment</x-ui.button>
                <x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'demo-alert')">Alert dialog</x-ui.button>
            </div>

            <x-ui.dialog name="demo-dialog" title="Edit campaign" description="Applies to new submissions only.">
                <x-ui.field label="Campaign name" name="campaignName">
                    <x-ui.input value="Danfo Diaries" width="full" />
                </x-ui.field>
                <x-slot:footer>
                    <x-ui.button variant="ghost" x-on:click="$dispatch('close-dialog', 'demo-dialog')">Cancel</x-ui.button>
                    <x-ui.button x-on:click="$toast({ variant: 'success', title: 'Saved.' }); $dispatch('close-dialog', 'demo-dialog')">Save</x-ui.button>
                </x-slot:footer>
            </x-ui.dialog>

            <x-ui.sheet name="demo-sheet" title="Payment NB-10234" description="Paid 14 Nov 2026, 09:30.">
                <x-ui.dl>
                    <x-ui.dl.item label="Amount"><x-ui.money :value="125000" /></x-ui.dl.item>
                    <x-ui.dl.item label="Status"><x-ui.badge variant="success">Completed</x-ui.badge></x-ui.dl.item>
                </x-ui.dl>
            </x-ui.sheet>

            <x-ui.confirm name="demo-confirm" title="Send the payout now?" description="The money leaves today and cannot be recalled." confirm-label="Send payout" />
            <x-ui.confirm.destructive name="demo-destroy" title="Delete this payment?" description="The record is removed for everyone." confirm-label="Delete payment" />
            <x-ui.alert-dialog name="demo-alert" title="Export started" description="We email the file when it is ready." acknowledge-label="Got it" />
        </section>

        <section aria-labelledby="toasts" class="space-y-3">
            <x-ui.section-label as="h2" id="toasts">Toasts</x-ui.section-label>
            <div class="flex flex-wrap items-center gap-2" x-data>
                <x-ui.button variant="outline" x-on:click="$toast({ variant: 'success', title: 'Payout sent.', description: 'Reaches the bank within a day.' })">Success</x-ui.button>
                <x-ui.button variant="outline" x-on:click="$toast({ variant: 'error', title: 'Transfer failed.', description: 'Check the account number.' })">Error</x-ui.button>
                <x-ui.button variant="outline" x-on:click="$toast({ variant: 'warning', title: 'Two campaigns end this week.' })">Warning</x-ui.button>
                <x-ui.button variant="outline" x-on:click="$toast('Copied to clipboard.')">Neutral</x-ui.button>
            </div>
        </section>
    </div>
</x-workbench::gallery.layout>
