<?php

namespace Workbench\App\Livewire\Community;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /community: x-ui.poll voting. One choice per viewer; a second vote, an
 * unknown option or another poll's key is refused.
 */
class PollDemo extends Component
{
    /** @var list<string> */
    public array $voted = [];

    /** @var array<string, int> */
    public array $votes = ['a' => 48, 'b' => 31, 'c' => 9];

    /** @param  list<string>  $choices */
    public function vote(string $poll, array $choices): void
    {
        $choices = array_values(array_intersect(array_map('strval', $choices), array_keys($this->votes)));
        abort_if($poll !== 'launch-time' || count($choices) !== 1 || $this->voted !== [], 422);
        $this->votes[$choices[0]]++;
        $this->voted = $choices;
    }

    public function render(): View
    {
        return view('livewire.community.poll-demo', ['now' => FeedDemo::NOW]);
    }
}
