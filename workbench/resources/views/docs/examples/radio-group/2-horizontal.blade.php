{{-- Horizontal, with a hint and an error --}}
<div class="flex flex-col gap-6">
    <x-ui.radio-group name="rgTerms" label="Payment terms" hint="Days until the invoice is due." selected="14" orientation="horizontal" :options="['7' => '7 days', '14' => '14 days', '30' => '30 days']" />
    <x-ui.radio-group name="rgType" label="Account type" orientation="horizontal" required invalid :options="['personal' => 'Personal', 'business' => 'Business']" />
</div>
