{{-- Edit in a form dialog, delete behind a destructive confirmation --}}
<div class="flex flex-wrap gap-2">
    <x-ui.button variant="outline" x-data x-on:click="$dispatch('open-dialog', 'pattern-rename')">Rename team</x-ui.button>
    <x-ui.button variant="destructive" x-data x-on:click="$dispatch('open-dialog', 'pattern-remove')">Remove member</x-ui.button>
</div>

<x-ui.dialog.form name="pattern-rename" title="Rename team" submit-label="Save name">
    <x-ui.field label="Team name" name="patternTeamName" required>
        <x-ui.input value="Payments" width="full" />
    </x-ui.field>
</x-ui.dialog.form>

<x-ui.confirm.destructive
    name="pattern-remove"
    title="Remove Ifeoma Nwosu?"
    description="She loses access to this workspace at once."
    confirm-label="Remove member"
/>
