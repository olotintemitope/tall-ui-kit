{{-- Steps without names, and with labels --}}
<div class="grid gap-6 md:grid-cols-2">
    <x-ui.progress-steps label="Onboarding" :total="5" :current="3" />
    <x-ui.progress-steps label="Campaign setup" :total="4" :current="2" size="md" :labels="['Song', 'Targets', 'Budget', 'Review']" />
</div>
