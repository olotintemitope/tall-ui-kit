<?php

namespace Workbench\App\Livewire\Workflow;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /workflow: x-ui.tag-input and x-ui.slider bound with wire:model.live, the
 * bound values printed back from the server so every round trip can be seen.
 */
class FieldsDemo extends Component
{
    /** @var list<string> */
    public array $genres = ['Afrobeats', 'Amapiano'];

    public int $budget = 25000;

    /** @var array{0: int, 1: int} */
    public array $followers = [10000, 250000];

    public function updatedGenres(): void
    {
        $this->validate(['genres' => 'array|max:5', 'genres.*' => 'string|max:24']);
    }

    public function render(): View
    {
        return view('livewire.workflow.fields-demo');
    }
}
