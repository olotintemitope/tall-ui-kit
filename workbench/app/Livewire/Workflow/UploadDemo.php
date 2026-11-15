<?php

namespace Workbench\App\Livewire\Workflow;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Workbench /workflow: Livewire file uploads followed by x-ui.upload-progress.list.
 * Nothing is stored: the temporary files are dropped with the session.
 */
class UploadDemo extends Component
{
    use WithFileUploads;

    /** @var array<int, TemporaryUploadedFile> */
    public array $files = [];

    public function updatedFiles(): void
    {
        $this->validate(['files' => 'array|max:5', 'files.*' => 'file|max:10240']);
    }

    public function removeFile(int $index): void
    {
        array_splice($this->files, $index, 1);
    }

    public function render(): View
    {
        return view('livewire.workflow.upload-demo');
    }
}
