<?php

namespace Workbench\App\Livewire;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use RuntimeException;
use TallUiKit\Livewire\Concerns\WithDataTable;
use TallUiKit\Support\DataTable\Action;
use TallUiKit\Support\DataTable\Column;
use TallUiKit\Support\DataTable\Filter;
use Workbench\App\DataTable\DemoPayment;
use Workbench\App\DataTable\DemoTables;

/**
 * Workbench /tables: x-data-table on 320 invented payments (DemoTables, a private
 * SQLite connection, so it works without seeding): 14 columns, 7 filters, bulk
 * actions including a destructive one with a confirm, row actions and a stacked
 * mobile layout. Exports are app-side (see docs/components/data-table.md).
 */
#[Layout('gallery.layout')]
#[Title('Tables')]
class TableGallery extends Component
{
    use WithDataTable;

    public const STATUS_BADGES = [
        'completed' => ['label' => 'Completed', 'variant' => 'success'],
        'pending' => ['label' => 'Pending', 'variant' => 'warning'],
        'failed' => ['label' => 'Failed', 'variant' => 'danger'],
        'refunded' => ['label' => 'Refunded', 'variant' => 'neutral'],
    ];

    /** Demo layout switch: scroll (sideways with a sticky first column) or stack (cards below 640px). */
    #[Url(as: 'layout', except: 'scroll')]
    public string $demoLayout = 'scroll';

    /** Demo state switch: live, empty (no rows at all) or error (the query fails). */
    #[Url(as: 'state', except: 'live')]
    public string $demoState = 'live';

    public function mount(): void
    {
        $this->demoLayout = in_array($this->demoLayout, ['scroll', 'stack'], true) ? $this->demoLayout : 'scroll';
        $this->demoState = in_array($this->demoState, ['live', 'empty', 'error'], true) ? $this->demoState : 'live';
    }

    public function updatedDemoState(): void
    {
        $this->mount();
        $this->tableMemo = [];
        $this->resetPage();
    }

    public function updatedDemoLayout(): void
    {
        $this->mount();
    }

    public function resetDemo(): void
    {
        DemoTables::reset();
        $this->clearSelection();
        $this->dispatch('toast', type: 'success', message: 'Sample data reset');
    }

    public function render(): View
    {
        return view('livewire.table-gallery', ['table' => $this->dataTable(), 'properties' => $this->tableProperties()]);
    }

    protected function tableQuery(): Builder
    {
        if ($this->demoState === 'error') {
            throw new RuntimeException('Sample failure for the error state.');
        }

        $query = DemoPayment::query();

        return $this->demoState === 'empty' ? $query->whereRaw('1 = 0') : $query;
    }

    protected function tableColumns(): array
    {
        return [
            Column::make('reference', 'Reference')->required()->role('mono')->sortable()->searchable(prefix: true)->exact('/^NB-\d{5}$/i')->width('w-28'),
            Column::make('created_at', 'Date')->role('meta')->sortable()->format(fn (DemoPayment $p): string => $p->created_at->format('j M Y'))->secondary(fn (DemoPayment $p): string => $p->created_at->format('H:i'))->export('date', fn (DemoPayment $p) => $p->created_at),
            Column::make('customer', 'Customer')->role('strong')->sortable('customer.name')->searchable(['customer.name', 'customer.email'])->with('customer:id,name,email,country')->format(fn (DemoPayment $p): ?string => $p->customer?->name)->secondary(fn (DemoPayment $p): ?string => $p->customer?->email)->width('max-w-56'),
            Column::make('country', 'Country')->hidden()->sortable('customer.country')->with('customer:id,name,email,country')->format(fn (DemoPayment $p): ?string => $p->customer?->country),
            Column::make('campaign', 'Campaign')->sortable()->searchable()->format(fn (DemoPayment $p): ?string => $p->campaign)->width('max-w-48'),
            Column::make('platform', 'Platform')->sortable()->format(fn (DemoPayment $p): string => ucfirst($p->platform)),
            Column::make('tier', 'Tier')->hidden()->sortable()->format(fn (DemoPayment $p): string => ucfirst($p->tier)),
            Column::make('slots', 'Slots')->numeric()->hidden()->sortable()->export('number'),
            Column::make('amount', 'Amount')->numeric()->sortable()->view('livewire.table-gallery.amount')->export('money'),
            Column::make('fee', 'Platform fee')->numeric()->sortable()->hidden()->ability('*')->view('livewire.table-gallery.fee')->export('money'),
            Column::make('currency', 'Currency')->hidden()->sortable(),
            Column::make('status', 'Status')->sortable()->view('livewire.table-gallery.status')->export('text', fn (DemoPayment $p): string => self::STATUS_BADGES[$p->status]['label'] ?? $p->status),
            Column::make('method', 'Method')->hidden()->sortable(),
            Column::make('flagged', 'Review')->sortable()->format(fn (DemoPayment $p): ?string => $p->flagged ? 'Flagged' : null)->export('text', fn (DemoPayment $p): string => $p->flagged ? 'Flagged' : ''),
            Column::make('notes', 'Notes')->hidden()->searchable()->width('max-w-48'),
        ];
    }

    protected function tableFilters(): array
    {
        return [
            Filter::status('status', 'Status')->options(self::STATUS_BADGES),
            Filter::select('platform', 'Platform')->options(['playlist' => 'Playlist', 'social' => 'Social', 'blog' => 'Blog'])->placeholder('All platforms'),
            Filter::multi('tier', 'Tier')->options(['starter' => 'Starter', 'growth' => 'Growth', 'pro' => 'Pro']),
            Filter::dateRange('date', 'Date')->column('created_at'),
            Filter::numberRange('amount', 'Amount')->column('amount')->between(0, 10_000_000),
            Filter::boolean('flagged', 'Flagged for review')->column('flagged'),
            Filter::select('country', 'Country')->column('customer.country')->options(['Nigeria' => 'Nigeria', 'Ghana' => 'Ghana', 'Kenya' => 'Kenya', 'United Kingdom' => 'United Kingdom', 'United States' => 'United States']),
        ];
    }

    protected function tableBulkActions(): array
    {
        return [
            Action::bulk('flag', 'Flag for review')->handle(function (Builder $rows, int $count): string {
                $rows->update(['flagged' => true]);

                return 'Flagged '.number_format($count).' '.($count === 1 ? 'payment' : 'payments');
            }),
            Action::bulk('delete', 'Delete')->icon('trash-2')->destructive('Delete payments?', 'The selected sample payments are removed. Reset the sample data to bring them back.')->handle(function (Builder $rows, int $count): string {
                $rows->delete();

                return 'Deleted '.number_format($count).' '.($count === 1 ? 'payment' : 'payments');
            }),
        ];
    }

    protected function tableRowActions(): array
    {
        return [
            Action::row('open', 'Open')->href(fn (DemoPayment $p): string => '/tables?q='.urlencode($p->reference)),
            Action::row('flag', 'Flag for review')->visible(fn (DemoPayment $p): bool => ! $p->flagged)->handle(function (DemoPayment $p): string {
                $p->update(['flagged' => true]);

                return 'Flagged '.$p->reference;
            }),
            Action::row('unflag', 'Clear flag')->visible(fn (DemoPayment $p): bool => $p->flagged)->handle(function (DemoPayment $p): string {
                $p->update(['flagged' => false]);

                return 'Cleared the flag on '.$p->reference;
            }),
            Action::row('delete', 'Delete')->icon('trash-2')->destructive('Delete this payment?', 'The sample payment is removed. Reset the sample data to bring it back.')->handle(function (DemoPayment $p): string {
                $p->delete();

                return 'Deleted '.$p->reference;
            }),
        ];
    }

    protected function tableDefaultSort(): ?array
    {
        return ['created_at', 'desc'];
    }

    protected function tableCaption(): string
    {
        return 'Sample payments';
    }

    protected function tableNoun(): string
    {
        return 'payments';
    }

    protected function tableLayout(): string
    {
        return $this->demoLayout;
    }

    protected function tableRowLabel(Model $row): string
    {
        return $row->reference;
    }

    protected function tableEmpty(): array
    {
        return ['title' => 'No payments yet.', 'description' => 'Payments appear here once a campaign is paid for.', 'actionLabel' => 'Reset sample data', 'actionHref' => '/tables'];
    }
}
