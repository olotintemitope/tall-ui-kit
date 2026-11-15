<?php

namespace Workbench\App\Livewire\Workflow;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * Workbench /workflow: x-ui.tree with checks bound to $checked and the "Archive" folder
 * loaded lazily through children(). The id comes from the browser: only known folders
 * answer, anything else is a 404.
 */
class TreeDemo extends Component
{
    /** @var list<string> */
    public array $checked = ['q1-report'];

    public ?string $picked = 'brand';

    #[Renderless]
    public function children(string $id): array
    {
        $folders = [
            'archive' => [
                ['id' => 'archive-2025', 'label' => '2025', 'hasChildren' => true],
                ['id' => 'archive-2024', 'label' => '2024', 'hasChildren' => true],
                ['id' => 'archive-notes', 'label' => 'Notes from the 2023 offsite.md'],
            ],
            'archive-2025' => [
                ['id' => 'archive-2025-tax', 'label' => 'Tax return.pdf'],
                ['id' => 'archive-2025-audit', 'label' => 'Audit letter.pdf'],
            ],
            'archive-2024' => [],
        ];

        abort_unless(array_key_exists($id, $folders), 404);

        return $folders[$id];
    }

    public function render(): View
    {
        return view('livewire.workflow.tree-demo');
    }
}
