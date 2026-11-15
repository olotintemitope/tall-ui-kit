{{-- The live checklist on workbench /checklist (ChecklistGallery). --}}
@php
    $has = fn (string $step): bool => in_array($step, $done, true);
    $workspace = [
        ['key' => 'account', 'label' => 'Create your account', 'done' => $has('account')],
        ['key' => 'invite', 'label' => 'Invite your team', 'hint' => 'Teammates get an email with a link to join.', 'done' => $has('invite'), 'action' => "complete('invite')", 'actionLabel' => 'Invite'],
        ['key' => 'billing', 'label' => 'Add a payment method', 'hint' => 'You are not charged until the trial ends.', 'done' => $has('billing'), 'action' => "complete('billing')", 'actionLabel' => 'Add card'],
        ['key' => 'domain', 'label' => 'Connect your domain', 'done' => $has('domain'), 'action' => "complete('domain')", 'actionLabel' => 'Connect'],
        ['key' => 'import', 'label' => 'Import existing data', 'hint' => 'CSV or a spreadsheet export.', 'optional' => true, 'done' => $has('import'), 'action' => "complete('import')", 'actionLabel' => 'Import'],
    ];
@endphp
<div class="min-w-0 space-y-3" data-checklist-live-demo>
    <x-ui.checklist
        title="Set up your workspace"
        description="A few steps before your team can start."
        :items="$workspace"
        name="workspace"
        persist="server"
        collapsible
        dismissible
        :heading-level="3"
    />
    <x-ui.button variant="ghost" size="sm" wire:click="resetDemo" data-reset-demo>
        <x-ui.icon name="refresh-cw" />
        Reset sample
    </x-ui.button>
</div>
