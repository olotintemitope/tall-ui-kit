{{-- A short multi-step dialog --}}
<x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'invite-member')">Invite teammate</x-ui.button>

<x-ui.dialog.steps name="invite-member" title="Invite a teammate" :steps="2" submit-label="Send invite">
    <div data-dialog-step="1" class="flex flex-col gap-4">
        <x-ui.field label="Email address" name="inviteEmail" required>
            <x-ui.input type="email" width="full" />
        </x-ui.field>
    </div>
    <div data-dialog-step="2" class="flex flex-col gap-4">
        <x-ui.field label="Role" name="inviteRole">
            <x-ui.select :options="['viewer' => 'Viewer', 'finance' => 'Finance', 'admin' => 'Admin']" selected="viewer" />
        </x-ui.field>
    </div>
</x-ui.dialog.steps>
