{{-- Type to filter --}}
<x-ui.field label="Customer" name="comboCustomer" hint="Type a name or company.">
    <x-ui.combobox placeholder="Search customers" value="cust-104" width="lg" :options="[
        'cust-101' => 'Adaeze Nwankwo, Ada Foods',
        'cust-102' => 'Babatunde Fashola, Eko Logistics',
        'cust-103' => 'Chinonso Eze, Kora Studio',
        'cust-104' => 'Damilola Adeyemi, Lagos Nights Records',
        'cust-105' => 'Emeka Obi, Obi and Sons',
        'cust-106' => 'Funmilayo Ojo, Ojo Pharmacy',
    ]" />
</x-ui.field>
