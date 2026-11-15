{{-- Workbench /dates (DateGallery): each picker with the value the server holds beside it. --}}
<div class="space-y-8" data-date-gallery>
    <section aria-labelledby="dates-single" class="space-y-4">
        <x-ui.section-label as="h2" id="dates-single">Date, time, month and week</x-ui.section-label>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.field label="Launch date" name="launch" required hint="Weekends are closed.">
                <x-ui.date wire:model.live="launch" min="2026-01-01" max="2027-12-31" :disabled-dates="['weekends', '2026-12-25']" />
            </x-ui.field>
            <x-ui.field label="Deadline" name="deadline" hint="Today or later.">
                <x-ui.date wire:model.blur="deadline" :min="now()->toDateString()" />
            </x-ui.field>
            <x-ui.field label="Campaign starts" name="startsAt">
                <x-ui.datetime wire:model.live="startsAt" step="900" />
            </x-ui.field>
            <x-ui.field label="Digest time" name="digest" hint="24-hour, 15-minute steps.">
                <x-ui.time-input wire:model.live="digest" step="900" />
            </x-ui.field>
            <x-ui.field label="Drop time" name="dropTime" hint="12-hour display.">
                <x-ui.time-input wire:model.live="dropTime" hours="12" min="08:00" max="22:00" />
            </x-ui.field>
            <x-ui.field label="Report month" name="month">
                <x-ui.month wire:model.live="month" min="2025-01" max="2027-12" />
            </x-ui.field>
            <x-ui.field label="Payout week" name="week">
                <x-ui.week wire:model.live="week" />
            </x-ui.field>
            <x-ui.field label="Disabled" name="disabledDemo">
                <x-ui.date value="2026-11-21" disabled />
            </x-ui.field>
        </div>
    </section>

    <section aria-labelledby="dates-range" class="space-y-4">
        <x-ui.section-label as="h2" id="dates-range">Range</x-ui.section-label>
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <x-ui.field label="Two properties" name="from">
                <x-ui.date-range wire:model.start.live="from" wire:model.end.live="to" />
            </x-ui.field>
            <x-ui.field label="One array, no presets" name="period">
                <x-ui.date-range wire:model.live="period" :presets="false" />
            </x-ui.field>
        </div>
    </section>

    <section aria-labelledby="dates-dialog" class="space-y-4">
        <x-ui.section-label as="h2" id="dates-dialog">Inside a dialog</x-ui.section-label>
        <x-ui.button variant="outline" x-on:click="$dispatch('open-dialog', 'date-dialog')">Schedule</x-ui.button>
        <x-ui.dialog name="date-dialog" title="Schedule the campaign" description="It starts on the day you choose.">
            <x-ui.field label="Start date" name="dialogDate">
                <x-ui.date wire:model="dialogDate" :min="now()->addDay()->toDateString()" />
            </x-ui.field>
            <x-slot:footer>
                <x-ui.button variant="ghost" x-on:click="$dispatch('close-dialog', 'date-dialog')">Cancel</x-ui.button>
                <x-ui.button x-on:click="$toast({ variant: 'success', title: 'Scheduled.' }); $dispatch('close-dialog', 'date-dialog')">Schedule</x-ui.button>
            </x-slot:footer>
        </x-ui.dialog>
    </section>

    <section aria-labelledby="dates-values" class="space-y-3">
        <x-ui.section-label as="h2" id="dates-values">What the server holds</x-ui.section-label>
        <x-ui.dl>
            @foreach (['launch', 'deadline', 'from', 'to', 'startsAt', 'digest', 'dropTime', 'month', 'week', 'dialogDate'] as $property)
                <x-ui.dl.item :label="$property"><span class="type-numeric" data-server-value="{{ $property }}">{{ $this->{$property} === '' ? 'empty' : $this->{$property} }}</span></x-ui.dl.item>
            @endforeach
            <x-ui.dl.item label="period"><span class="type-numeric" data-server-value="period">{{ ($period['start'] ?? '') ?: 'empty' }} to {{ ($period['end'] ?? '') ?: 'empty' }}</span></x-ui.dl.item>
        </x-ui.dl>
        <x-ui.button wire:click="save" loading="save">Validate on the server</x-ui.button>
    </section>

    <section aria-labelledby="dates-many" class="space-y-3">
        <x-ui.section-label as="h2" id="dates-many">Many pickers, started lazily</x-ui.section-label>
        <p class="type-meta">Each starts when it nears the screen, gets focus or in idle time (defer).</p>
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
            @foreach (range(1, 24) as $i)
                <x-ui.date defer :value="now()->addDays($i)->toDateString()" :aria-label="'Day '.$i" width="full" />
            @endforeach
        </div>
    </section>
</div>
