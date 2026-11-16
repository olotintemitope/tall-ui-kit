{{-- From a Livewire action --}}
<x-ui.code-block label="app/Livewire/Payouts/Show.php" copy>{{ <<<'PHP'
public function approve(): void
{
    $this->authorize('approve', $this->payout);
    $this->payout->approve();

    $this->dispatch('toast',
        variant: 'success',
        title: 'Payout approved.',
        action: ['label' => 'Undo', 'method' => 'undoApprove'],
    );
}
PHP }}</x-ui.code-block>
