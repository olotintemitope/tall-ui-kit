{{-- With Livewire --}}
<x-ui.code-block label="resources/views/livewire/settings/payouts.blade.php" copy>{{ implode("\n", [
    '<'.'x-ui.form wire:submit="save" guard>',
    '    <'.'x-ui.form-section title="Payouts" description="Where and when we pay you.">',
    '        <'.'x-ui.panel>',
    '            <'.'x-ui.field label="Payout day" name="payoutDay" required>',
    '                <'.'x-ui.select wire:model="payoutDay" :options="$days" />',
    '            </'.'x-ui.field>',
    '            <'.'x-slot:footer>',
    '                <'.'x-ui.form-actions>',
    '                    <'.'x-ui.button variant="ghost" type="button" x-on:click="$dispatch(\'form-reset\')">Discard</'.'x-ui.button>',
    '                    <'.'x-ui.button type="submit" loading="save">Save changes</'.'x-ui.button>',
    '                </'.'x-ui.form-actions>',
    '            </'.'x-slot:footer>',
    '        </'.'x-ui.panel>',
    '    </'.'x-ui.form-section>',
    '</'.'x-ui.form>',
]) }}</x-ui.code-block>
