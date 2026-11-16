{{-- Wizard (Livewire) --}}
<x-ui.code-block label="resources/views/livewire/campaigns/create.blade.php" copy>{{ implode("\n", [
    '<'.'x-ui.form-layout.wizard',
    '    title="New campaign"',
    '    :steps="[\'Song\', \'Targets\', \'Details\', \'Review\', \'Pay\']"',
    '    :current="$step"',
    '    flow="campaign"',
    '    finish-label="Pay ₦68,000"',
    '    :errors="[\'wizard.title\' => \'Title\', \'wizard.link\' => \'Link\', \'wizard.genre\' => \'Genre\', \'wizard.date\' => \'Release date\']"',
    '>',
    '    @'.'if ($step === 1)',
    '        <'.'x-ui.field label="Song link" name="wizard.link" required>',
    '            <'.'x-ui.input type="url" wire:model="wizard.link" width="full" />',
    '        </'.'x-ui.field>',
    '    @'.'endif',
    '</'.'x-ui.form-layout.wizard>',
]) }}</x-ui.code-block>
