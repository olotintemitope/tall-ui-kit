<?php

namespace Workbench\App\Livewire\Workflow;

use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Workbench /workflow: x-ui.sortable sends its new order to reorder() in one call. The
 * keys come from the browser, so the method accepts only a permutation of the known keys.
 */
class SortableDemo extends Component
{
    public const METHODS = ['card' => 'Card', 'transfer' => 'Bank transfer', 'mobile' => 'Mobile money', 'ussd' => 'USSD', 'wallet' => 'Wallet balance'];

    /** @var list<string> */
    public array $order = ['card', 'transfer', 'mobile', 'ussd', 'wallet'];

    public int $saves = 0;

    /** @param  list<string>  $keys */
    public function reorder(array $keys): void
    {
        $keys = array_values(array_map('strval', $keys));
        $known = array_keys(self::METHODS);
        abort_unless(count($keys) === count($known) && array_diff($known, $keys) === [] && count(array_unique($keys)) === count($keys), 422);

        $this->order = $keys;
        $this->saves++;
    }

    public function render(): View
    {
        return view('livewire.workflow.sortable-demo', ['methods' => self::METHODS]);
    }
}
