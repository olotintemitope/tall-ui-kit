<?php

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use TallUiKit\Livewire\Concerns\WithChecklist;

/**
 * The live checklist on workbench /checklist (gallery/checklist.blade.php embeds it):
 * its actions mark steps done on the server, so the morph, the polite status, the
 * check and the bar fill can be tried; collapse and dismiss go to the session store
 * (persist="server", WithChecklist). A child component, so a round trip carries only
 * this checklist.
 */
class ChecklistGallery extends Component
{
    use WithChecklist;

    public const STEPS = ['account', 'invite', 'billing', 'domain', 'import'];

    /** @var list<string> Workspace steps done so far. */
    public array $done = ['account'];

    /** The sample's progress lives in the session too, so a reload keeps it. */
    public function mount(): void
    {
        $this->done = session('checklist_demo_done', ['account']);
    }

    public function complete(string $step): void
    {
        abort_unless(in_array($step, self::STEPS, true), 422);

        $this->done = array_values(array_unique([...$this->done, $step]));
        session(['checklist_demo_done' => $this->done]);
    }

    public function resetDemo(): void
    {
        $this->done = ['account'];
        session()->forget(['checklist_demo_done', 'tall_ui_kit_checklist']);
    }

    public function render(): View
    {
        return view('livewire.checklist-gallery');
    }

    protected function checklistKeys(): array
    {
        return ['workspace'];
    }

    /** The server agrees to a dismissal only once every required step is done. */
    protected function checklistCanDismiss(string $key): bool
    {
        return array_diff(['account', 'invite', 'billing', 'domain'], $this->done) === [];
    }
}
