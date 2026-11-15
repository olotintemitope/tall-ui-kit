<x-workbench::gallery.layout title="Forms">
    <x-ui.page-header eyebrow="Gallery" title="Forms" description="Fields, custom selects, choices and a form page on a tinted surface. Nothing here is saved." />

    <x-ui.form-page class="-mx-4 px-4 py-6 sm:-mx-6 sm:px-6">
        <x-ui.form x-data="{ status: 'active', tab: 'profile' }" x-on:submit.prevent="$toast({ variant: 'success', title: 'Saved.' })">
            <div class="space-y-6">
                <x-ui.form-section title="Profile" description="Shown to the people you work with.">
                    <x-ui.panel>
                        <div class="space-y-4">
                            <x-ui.field-row cols="2">
                                <x-ui.field label="Display name" name="displayName" hint="Up to 60 characters." required>
                                    <x-ui.input value="Lagos Nights Records" width="full" />
                                </x-ui.field>
                                <x-ui.field label="Email" name="email">
                                    <x-ui.input type="email" value="ops@example.com" width="full" />
                                </x-ui.field>
                            </x-ui.field-row>
                            <x-ui.field label="Bio" name="bio" hint="Plain text.">
                                <x-ui.textarea rows="3" />
                            </x-ui.field>
                            <x-ui.field label="Payout threshold" name="payoutThreshold" error="Enter at least 5,000.">
                                <x-ui.input value="250" width="sm">
                                    <x-slot:prefix>₦</x-slot:prefix>
                                </x-ui.input>
                            </x-ui.field>
                            <x-ui.field label="Disabled field" name="locked">
                                <x-ui.input value="Locked by your plan" disabled />
                            </x-ui.field>
                        </div>
                        <x-slot:footer>
                            <x-ui.form-actions>
                                <x-ui.button variant="ghost">Discard</x-ui.button>
                                <x-ui.button type="submit">Save changes</x-ui.button>
                            </x-ui.form-actions>
                        </x-slot:footer>
                    </x-ui.panel>
                </x-ui.form-section>

                <x-ui.form-section title="Choices" description="Custom listboxes only: no native select, and every popup opens below its trigger.">
                    <x-ui.panel>
                        <div class="space-y-4">
                            <x-ui.field label="Payout day" name="payoutDay">
                                <x-ui.select x-model="status" :options="['active' => 'Active', 'paused' => 'Paused', 'archived' => 'Archived']" />
                            </x-ui.field>
                            <x-ui.field label="Currency" name="currency">
                                <x-ui.select placeholder="Choose a currency" :options="['NGN' => 'Naira (NGN)', 'USD' => 'US dollar (USD)']" />
                            </x-ui.field>
                            <x-ui.field label="Genre" name="genre">
                                <x-ui.combobox placeholder="Type to filter" :options="['afrobeats' => 'Afrobeats', 'amapiano' => 'Amapiano', 'highlife' => 'Highlife', 'gospel' => 'Gospel', 'hip-hop' => 'Hip-hop']" />
                            </x-ui.field>
                            <x-ui.field label="Start date" name="startDate">
                                <x-ui.date />
                            </x-ui.field>
                            <x-ui.field label="Find a payment" name="q">
                                <x-ui.search placeholder="Search payments by reference or email" short-placeholder="Search payments" />
                            </x-ui.field>
                            <x-ui.radio-group name="plan" label="Plan" selected="growth" variant="cards" :options="[
                                ['value' => 'starter', 'label' => 'Starter', 'description' => 'Up to 5 campaigns a month.'],
                                ['value' => 'growth', 'label' => 'Growth', 'description' => 'Up to 25 campaigns a month.'],
                                ['value' => 'pro', 'label' => 'Pro', 'description' => 'No limit.', 'disabled' => true],
                            ]" />
                            <x-ui.radio-group name="schedule" label="Schedule" selected="weekly" orientation="horizontal" :options="['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly']" />
                            <x-ui.checkbox name="terms" label="Email me a weekly summary" description="Every Monday at 08:00." />
                            <x-ui.checkbox name="sms" label="Disabled choice" disabled />
                            <x-ui.switch name="alerts" label="Payout alerts" description="Sent when a payout leaves." checked />
                            <x-ui.field label="Artwork" name="artwork">
                                <x-ui.file accept="image/*" hint="PNG or JPG, up to 5 MB." />
                            </x-ui.field>
                        </div>
                    </x-ui.panel>
                </x-ui.form-section>

                <x-ui.form-section title="Views and periods" description="Tabs switch views of one subject; the segmented control switches a period of the same data.">
                    <x-ui.panel>
                        <div class="space-y-4">
                            <x-ui.tabs label="Account sections" x-model="tab" :tabs="['profile' => 'Profile', 'payouts' => ['label' => 'Payouts', 'count' => 3], 'team' => 'Team', 'audit' => ['label' => 'Audit', 'disabled' => true]]">
                                <x-ui.tabs.panel name="profile"><x-ui.text class="pt-3">Profile settings.</x-ui.text></x-ui.tabs.panel>
                                <x-ui.tabs.panel name="payouts"><x-ui.text class="pt-3">Three payouts are waiting.</x-ui.text></x-ui.tabs.panel>
                                <x-ui.tabs.panel name="team"><x-ui.text class="pt-3">Team members.</x-ui.text></x-ui.tabs.panel>
                            </x-ui.tabs>
                            <x-ui.segmented label="Period" value="30d" :options="['7d' => '7 days', '30d' => '30 days', '90d' => '90 days']" />
                            <x-ui.segmented label="View" value="table" size="sm" :options="['table' => ['label' => 'Table', 'icon' => 'table', 'iconOnly' => true], 'chart' => ['label' => 'Chart', 'icon' => 'chart-line', 'iconOnly' => true]]" />
                        </div>
                    </x-ui.panel>
                </x-ui.form-section>
            </div>
        </x-ui.form>
    </x-ui.form-page>
</x-workbench::gallery.layout>
