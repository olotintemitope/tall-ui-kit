{{--
    Workbench /checklist: x-ui.checklist in its states on neutral sample flows.
    The first checklist is live (the ChecklistGallery Livewire child): each action
    marks its step done. Collapse on the others stays in this browser.
--}}
@php
    $profile = [
        ['label' => 'Verify your email address', 'done' => true],
        ['label' => 'Add your name and photo', 'done' => true],
        ['label' => 'Add a phone number', 'hint' => 'Used only for sign-in codes.', 'done' => false, 'href' => '#checklist-links', 'actionLabel' => 'Add phone'],
        ['label' => 'Turn on two-step sign-in', 'done' => false, 'href' => '#checklist-links', 'actionLabel' => 'Turn on'],
    ];
    $launch = [
        ['label' => 'Write the release notes', 'done' => true],
        ['label' => 'Run the final checks', 'done' => true],
        ['label' => 'Publish the release', 'done' => true],
    ];
    $business = [
        ['label' => 'Upload a registration document', 'done' => true],
        ['label' => "Confirm the director's identity", 'done' => false, 'href' => '#checklist-collapsed', 'actionLabel' => 'Start'],
        ['label' => 'Add a bank account', 'done' => false, 'href' => '#checklist-collapsed', 'actionLabel' => 'Add account'],
    ];
@endphp
<x-workbench::gallery.layout title="Checklist">
    <x-ui.page-header eyebrow="UI kit" title="Checklist" description="Steps for onboarding, setup, verification or launch flows. Steps and actions are data; collapse and dismiss persist per name." />

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-2" data-checklist-gallery>
        <section aria-labelledby="checklist-live" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="checklist-live">Partial, next step highlighted (live)</x-ui.section-label>
            @livewire(\Workbench\App\Livewire\ChecklistGallery::class)
        </section>

        <section aria-labelledby="checklist-links" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="checklist-links">Partial, link actions</x-ui.section-label>
            <x-ui.checklist title="Complete your profile" :items="$profile" name="profile" collapsible :heading-level="3" />
        </section>

        <section aria-labelledby="checklist-done" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="checklist-done">All done</x-ui.section-label>
            <x-ui.checklist title="Launch checklist" :items="$launch" name="launch" dismissible persist="none" :heading-level="3" />
        </section>

        <section aria-labelledby="checklist-collapsed" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="checklist-collapsed">Collapsed</x-ui.section-label>
            <x-ui.checklist title="Verify your business" :items="$business" name="business" collapsible :collapsed="true" persist="none" :heading-level="3" />
        </section>

        <section aria-labelledby="checklist-loading" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="checklist-loading">Loading</x-ui.section-label>
            <x-ui.checklist title="Set up your workspace" loading :items="array_fill(0, 5, ['label' => 'Step'])" />
        </section>
    </div>
</x-workbench::gallery.layout>
