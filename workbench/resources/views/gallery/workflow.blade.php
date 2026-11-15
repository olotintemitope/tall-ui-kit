{{--
    Workbench /workflow: steppers, timelines, tag inputs, avatar groups, sortable lists,
    sliders, banners, trees and the progress family, on neutral sample data. The live
    parts are small Livewire children (workbench/app/Livewire/Workflow), so each round
    trip carries one demo only.
--}}
@php
    $now = '2026-11-15 14:00:00';
    $steps = [
        ['label' => 'Account', 'description' => 'Email and password.'],
        ['label' => 'Business details', 'description' => 'Registered name and address.'],
        ['label' => 'Bank account', 'description' => 'The account number did not match the name.', 'state' => 'error'],
        ['label' => 'Review', 'description' => 'Check everything once.'],
        ['label' => 'Go live'],
    ];
    $activity = [
        ['key' => 'a1', 'time' => '2026-11-15 13:10:00', 'actor' => 'Ada Obi', 'text' => 'approved the payout PAY-9F3C21AB.', 'status' => ['Approved', 'success']],
        ['key' => 'a2', 'time' => '2026-11-15 11:42:00', 'actor' => 'Bola Ade', 'text' => 'commented on the request.', 'body' => "The bank changed its cut-off time.\nWe can send this before 3pm."],
        ['key' => 'a3', 'time' => '2026-11-15 09:05:00', 'actor' => 'Chidi Eze', 'text' => 'attached the statement.', 'attachment' => ['name' => 'statement-october-2026-first-bank-business-account.pdf', 'href' => '#timeline', 'meta' => '240 KB']],
        ['key' => 'a4', 'time' => '2026-11-14 17:30:00', 'actor' => 'Payments system', 'text' => 'held the payout for review.', 'status' => ['On hold', 'warning']],
        ['key' => 'a5', 'time' => '2026-11-14 16:02:00', 'actor' => 'Dayo Bello', 'text' => 'requested a payout of ₦1,250,000.'],
        ['key' => 'a6', 'time' => '2026-11-12 10:00:00', 'actor' => 'Efe Okoro', 'text' => 'updated the bank details.'],
        ['key' => 'a7', 'time' => '2026-11-10 08:15:00', 'actor' => 'Ada Obi', 'text' => 'created the account.'],
    ];
    $people = ['Ada Obi', 'Bola Ade', 'Chidi Eze', 'Dayo Bello', 'Efe Okoro', 'Funmi Lawal', 'Gbenga Musa'];
@endphp
<x-workbench::gallery.layout title="Workflow">
    <x-ui.page-header eyebrow="UI kit" title="Workflow" description="Steppers, activity, tags, people, ordering, ranges, announcements, trees and progress. Data in, Livewire-friendly." />

    <div class="space-y-10" data-workflow-gallery>
        <section aria-labelledby="banners" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="banners">Banner</x-ui.section-label>
            <div class="-mx-4 space-y-px sm:-mx-6">
                <x-ui.banner tone="info" title="New: payouts in US dollars." action-label="See how it works" action-href="#banners">Creators with a USD account can be paid in dollars from December.</x-ui.banner>
                <x-ui.banner tone="success" dismissible name="statements-ready-2026-11" persist="local">Your October statements are ready to download.</x-ui.banner>
                <x-ui.banner tone="warning" dismissible persist="none">Scheduled maintenance on Sunday from 02:00 to 03:00. Exports may be slow.</x-ui.banner>
                <x-ui.banner tone="danger" title="Card payment failed." action-label="Update the card" action-href="#banners">Your plan stays active until 30 November.</x-ui.banner>
            </div>
            @livewire(\Workbench\App\Livewire\Workflow\BannerDemo::class)
        </section>

        <section aria-labelledby="stepper" class="min-w-0 space-y-6">
            <x-ui.section-label as="h2" id="stepper">Stepper</x-ui.section-label>
            <x-ui.stepper label="Set up payouts" :steps="$steps" :current="4" clickable="all" />
            <div class="grid grid-cols-1 gap-8 md:grid-cols-2">
                <x-ui.stepper label="Verification" orientation="vertical" :current="2" :steps="[['label' => 'Upload your ID', 'description' => 'A passport or national ID card.'], ['label' => 'Take a selfie', 'description' => 'We compare it with the photo on your ID.'], ['label' => 'Wait for review', 'description' => 'Usually under an hour.']]" />
                <div class="space-y-4">
                    <x-ui.text role="meta">Inline (the wizard's header):</x-ui.text>
                    <x-ui.stepper label="Campaign steps" variant="inline" :steps="['Song', 'Targets', 'Details', 'Review', 'Pay']" :current="3" clickable />
                </div>
            </div>
        </section>

        <section aria-labelledby="progress" class="min-w-0 space-y-6">
            <x-ui.section-label as="h2" id="progress">Progress</x-ui.section-label>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.progress label="Profile complete" :value="60" />
                <x-ui.progress label="Delivered streams" :value="1240" :max="2000" format="count" size="md" label-position="end" tone="success" />
                <x-ui.progress label="Video upload" :value="2100000" :max="5000000" :buffer="3400000" format="bytes" size="lg" />
                <x-ui.progress label="Storage used" :value="92" tone="warning" size="md" />
                <x-ui.progress label="Failed import" :value="35" tone="danger" label-position="end" />
                <x-ui.progress label="Checking the file" />
                <x-ui.progress label="Hidden label, still named" :value="40" label-position="hidden" />
            </div>
            <div class="flex flex-wrap items-center gap-6" data-ring-row>
                <x-ui.progress-ring label="Syncing" :value="25" :size="24" />
                <x-ui.progress-ring label="Upload" :value="62" :size="40" show-value />
                <x-ui.progress-ring label="Course progress" :value="80" :size="64" show-value tone="success" />
                <x-ui.progress-ring label="Quota" :value="95" :size="40" tone="danger" show-value />
                <x-ui.progress-ring label="Loading the report" :size="40" />
            </div>
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.progress-steps label="Onboarding" :total="5" :current="3" />
                <x-ui.progress-steps label="Quiz" :total="4" :current="2" size="md" tone="success" :labels="['Basics', 'Royalties and splits', 'Distribution', 'Done']" />
            </div>
        </section>

        <section aria-labelledby="uploads" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="uploads">Upload progress</x-ui.section-label>
            <ul aria-label="Sample uploads" class="divide-y divide-line">
                <x-ui.upload-progress as="li" name="cover-art-final-final-v3-approved-by-label-2026-11-15.png" :size="2400000" :progress="45" status="uploading" />
                <x-ui.upload-progress as="li" name="contract.pdf" :size="180000" status="done" />
                <x-ui.upload-progress as="li" name="stems.zip" :size="512000000" :progress="70" status="failed" error="The connection dropped. Try again." />
                <x-ui.upload-progress as="li" name="lyrics.txt" :size="2400" status="queued" />
            </ul>
            @livewire(\Workbench\App\Livewire\Workflow\UploadDemo::class)
        </section>

        <section aria-labelledby="timeline" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="timeline">Timeline</x-ui.section-label>
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <x-ui.timeline :items="$activity" group-by-day :limit="4" :now="$now" label="Payout activity" />
                <x-ui.timeline :items="array_slice($activity, 0, 3)" :now="$now" time-mode="absolute" label="Recent activity" />
            </div>
        </section>

        <section aria-labelledby="people" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="people">Avatar group</x-ui.section-label>
            <div class="flex flex-wrap items-center gap-6">
                <x-ui.avatar-group :people="$people" label="Reviewers" />
                <x-ui.avatar-group :people="array_slice($people, 0, 3)" size="default" />
                <x-ui.avatar-group :people="$people" :max="3" size="lg" label="Team" />
                <x-ui.avatar-group :people="['Ada Obi']" size="xs" />
            </div>
        </section>

        <section aria-labelledby="fields" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="fields">Tag input and slider (live)</x-ui.section-label>
            @livewire(\Workbench\App\Livewire\Workflow\FieldsDemo::class)
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                <x-ui.field label="Promo codes" name="promo" hint="Letters and digits, 4 to 12, no suggestions.">
                    <x-ui.tag-input :value="['LAUNCH24']" pattern="[A-Z0-9]{4,12}" pattern-message="Use 4 to 12 capital letters or digits." placeholder="Add a code" />
                </x-ui.field>
                <div class="space-y-5">
                    <x-ui.slider label="Volume" :value="40" format="percent" />
                    <x-ui.slider label="Disabled" :value="30" disabled />
                </div>
            </div>
        </section>

        <section aria-labelledby="sortable" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="sortable">Sortable (live)</x-ui.section-label>
            <div class="max-w-xl">
                @livewire(\Workbench\App\Livewire\Workflow\SortableDemo::class)
            </div>
        </section>

        <section aria-labelledby="trees" class="min-w-0 space-y-3">
            <x-ui.section-label as="h2" id="trees">Tree (live, Archive loads lazily)</x-ui.section-label>
            @livewire(\Workbench\App\Livewire\Workflow\TreeDemo::class)
        </section>
    </div>
</x-workbench::gallery.layout>
