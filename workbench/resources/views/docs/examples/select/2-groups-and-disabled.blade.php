{{-- Groups and unavailable options --}}
<x-ui.field label="Settlement account" name="selectAccount">
    <x-ui.select selected="gtb-ops" :options="[
        'Naira accounts' => ['gtb-ops' => 'GTBank, operations', 'zenith-payroll' => 'Zenith Bank, payroll'],
        'Dollar accounts' => ['dom-usd' => 'Access Bank, domiciliary'],
        ['value' => 'kuda', 'label' => 'Kuda, awaiting verification', 'disabled' => true],
    ]" />
</x-ui.field>
