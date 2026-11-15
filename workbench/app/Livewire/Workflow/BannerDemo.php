<?php

namespace Workbench\App\Livewire\Workflow;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use TallUiKit\Livewire\Concerns\WithDismissals;

/**
 * Workbench /workflow: an x-ui.banner kept dismissed on the server (persist="server",
 * the session-backed DismissStore through WithDismissals).
 */
class BannerDemo extends Component
{
    use WithDismissals;

    public function resetDemo(): void
    {
        session()->forget('tall_ui_kit_checklist.banner:payouts-2026-11');
    }

    protected function dismissibleKeys(): array
    {
        return ['banner:payouts-2026-11'];
    }

    public function render(): View
    {
        return view('livewire.workflow.banner-demo');
    }
}
