{{-- Only from the list, bound with x-model --}}
<div x-data="{ approvers: ['Kemi Adeyemi'] }" class="flex flex-col gap-2">
    <x-ui.field label="Approvers" name="tagApprovers">
        <x-ui.tag-input x-model="approvers" :value="['Kemi Adeyemi']" :allow-custom="false" placeholder="Add a person" :suggestions="[
            ['value' => 'Kemi Adeyemi', 'hint' => 'Finance lead'],
            ['value' => 'Tunde Bakare', 'hint' => 'Owner'],
            ['value' => 'Zainab Bello', 'hint' => 'Operations'],
        ]" />
    </x-ui.field>
    <p class="type-meta"><span x-text="approvers.length">1</span> of 3 people will approve payouts.</p>
</div>
