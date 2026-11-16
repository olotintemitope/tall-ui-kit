{{-- Livewire usage: the live table runs in the workbench gallery --}}
<x-ui.code-block label="app/Livewire/Payouts/Index.php" copy>{{ <<<'PHP'
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use TallUiKit\Livewire\Concerns\WithDataTable;
use TallUiKit\Support\DataTable\{Column, Filter};

class Index extends Component
{
    use WithDataTable;

    protected function tableQuery(): Builder
    {
        return Payout::query()->whereBelongsTo(auth()->user()->team);
    }

    protected function tableColumns(): array
    {
        return [
            Column::make('reference', 'Reference')->required()->role('mono')->sortable(),
            Column::make('curator', 'Curator')->role('strong')->searchable(['curator_name']),
            Column::make('amount', 'Amount')->numeric()->sortable()->export('money'),
        ];
    }

    protected function tableFilters(): array
    {
        return [
            Filter::status('status', 'Status')->options([
                'pending' => ['label' => 'Pending', 'variant' => 'warning'],
                'paid' => ['label' => 'Paid', 'variant' => 'success'],
            ]),
        ];
    }

    public function render(): View
    {
        return view('livewire.payouts.index', ['table' => $this->dataTable()]);
    }
}
PHP }}</x-ui.code-block>
{{-- The '<'.'x-' split stops Blade compiling the tag inside this docs example. --}}
<x-ui.code-block label="resources/views/livewire/payouts/index.blade.php" copy class="mt-4">{{ '<'.'x-data-table :table="$table" heading="Payouts" />' }}</x-ui.code-block>
