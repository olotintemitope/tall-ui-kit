{{-- Description list layouts: stacked, inline and grid --}}
<div class="grid gap-6 md:grid-cols-3">
    <x-ui.dl :items="[
        ['label' => 'Customer', 'value' => 'Ngozi Eze'],
        ['label' => 'Email', 'value' => 'ngozi.eze@harmattan-textiles.example.com'],
    ]" />
    <x-ui.dl layout="inline" dense :items="[
        ['label' => 'Subtotal', 'value' => '₦420,000', 'numeric' => true],
        ['label' => 'VAT (7.5%)', 'value' => '₦31,500', 'numeric' => true],
        ['label' => 'Total', 'value' => '₦451,500', 'numeric' => true],
    ]" />
    <x-ui.dl layout="grid">
        <x-ui.dl.item label="Reference" numeric>INV-2041</x-ui.dl.item>
        <x-ui.dl.item label="Status"><x-ui.badge variant="warning">Open</x-ui.badge></x-ui.dl.item>
        <x-ui.dl.item label="Issued">14 Nov 2026</x-ui.dl.item>
        <x-ui.dl.item label="Due">28 Nov 2026</x-ui.dl.item>
    </x-ui.dl>
</div>
