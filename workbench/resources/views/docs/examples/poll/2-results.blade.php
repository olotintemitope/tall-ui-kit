{{-- Voted (several choices) and ended --}}
<div class="space-y-8">
    <x-ui.poll key="docs-voted" question="Which sessions will you join?" multiple :voted="['a', 'c']" :options="[['key' => 'a', 'label' => 'Keynote', 'votes' => 120], ['key' => 'b', 'label' => 'Panel', 'votes' => 64], ['key' => 'c', 'label' => 'Workshop', 'votes' => 87]]" />
    <x-ui.poll key="docs-ended" question="Venue for the meet-up" closed :options="[['key' => 'a', 'label' => 'Lekki', 'votes' => 210], ['key' => 'b', 'label' => 'Yaba', 'votes' => 190]]" />
</div>
