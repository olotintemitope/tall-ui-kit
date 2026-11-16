<?php

namespace Workbench\App\Livewire\Files;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /files: x-ui.file-manager's loading, error and empty states. Try again flips
 * the error state to loaded, so the Retry round trip can be seen.
 */
class StatesDemo extends Component
{
    public bool $failed = true;

    public function retry(): void
    {
        $this->failed = false;
    }

    public function render(): View
    {
        return view('livewire.files.states-demo');
    }
}
