# Page recipes

Each recipe uses only kit components. Copy the shape, then check it against [rules.md](rules.md).

## List page (search, filters, bulk actions)

```php
use TallUiKit\Livewire\Concerns\WithDataTable;
use TallUiKit\Support\DataTable\{Action, Column, Filter};

class Index extends Component
{
    use WithDataTable;

    protected function tableQuery(): Builder { return Payout::query(); }       // only rows this user may see

    protected function tableColumns(): array
    {
        return [
            Column::make('reference', 'Reference')->required()->role('mono')->sortable()->searchable(prefix: true),
            Column::make('customer', 'Customer')->role('strong')->sortable('user.name')->searchable(['user.name', 'user.email'])
                ->with('user:id,name,email')->format(fn ($p) => $p->user?->name)->secondary(fn ($p) => $p->user?->email),
            Column::make('amount', 'Amount')->numeric()->sortable()->view('payouts.cells.amount'),
            Column::make('status', 'Status')->sortable()->view('payouts.cells.status'),
        ];
    }

    protected function tableFilters(): array
    {
        return [
            Filter::status('status', 'Status')->options(['pending' => ['label' => 'Pending', 'variant' => 'warning'], 'paid' => ['label' => 'Paid', 'variant' => 'success']]),
            Filter::dateRange('date', 'Requested')->column('created_at'),
        ];
    }

    protected function tableBulkActions(): array
    {
        return [
            Action::bulk('approve', 'Approve')->handle(function (Builder $rows, int $count): string {
                $rows->update(['status' => 'approved']);

                return "Approved {$count} payouts";                         // shown as the success toast
            }),
        ];
    }

    protected function tableLayout(): string { return 'stack'; }               // cards on phones, never a sideways page

    public function render(): View { return view('payouts.index', ['table' => $this->dataTable()]); }
}
```

```blade
<x-ui.page-header title="Payouts" description="Requests waiting for review." />
<x-data-table :table="$table" />
```

Cells: `payouts/cells/status.blade.php` is one line, e.g. `<x-ui.badge lean size="sm" :variant="$variant">{{ $label }}</x-ui.badge>`; money cells `<x-ui.money lean :value="$row->amount" />`.

## Detail page

```blade
<x-ui.page-header :title="'Payout '.$payout->reference" eyebrow="Payouts">
    <x-slot:actions>
        <x-ui.button variant="outline" x-on:click="$dispatch('open-confirm', 'reject')">Reject</x-ui.button>
        <x-ui.button wire:click="approve" loading="approve">Approve</x-ui.button>
    </x-slot:actions>
</x-ui.page-header>

<div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_320px]">
    <x-ui.card.detail title="Request" :status="$payout->status_label" :status-variant="$payout->status_variant" :items="[
        ['label' => 'Amount', 'value' => \TallUiKit\Support\Format::money($payout->amount, $payout->currency)],
        ['label' => 'Reference', 'value' => $payout->reference, 'numeric' => true],
        ['label' => 'Requested', 'value' => $payout->created_at->format('j M Y, H:i')],
    ]" />
    <x-ui.card.list title="History" :empty="'No changes yet.'">
        @foreach ($events as $event)
            <x-ui.card.row :title="$event->label" :meta="$event->at" wire:key="e-{{ $event->id }}" />
        @endforeach
    </x-ui.card.list>
</div>

<x-ui.confirm.reason name="reject" title="Reject this payout?" description="The curator is told why." confirm-label="Reject payout"
    action="reject" :reasons="['details' => 'Bank details do not match', 'other' => 'Other']" />
```

## Dashboard section (flat, not a card grid)

```blade
<section aria-labelledby="money" class="space-y-3">
    <x-ui.section-label as="h2" id="money">Money</x-ui.section-label>
    <x-ui.stat-strip :columns="4">
        <x-ui.stat label="Revenue" :value="$revenue" money sub="Last 30 days" />
        <x-ui.stat label="Fees" :value="$fees" money />
        <x-ui.stat label="Payouts" :value="$payouts" money />
        <x-ui.stat label="Refunds" :value="$refunds" />
    </x-ui.stat-strip>
    <x-ui.chart type="line" title="Revenue" period="Last 30 days" money :labels="$days"
        :series="[['name' => 'Revenue', 'token' => 'chart-accent', 'points' => $points]]" :previous="$previous" toggle>
        <x-slot:actions><x-ui.segmented size="sm" label="Period" wire:model.live="period" :options="['7d' => '7d', '30d' => '30d', '90d' => '90d']" /></x-slot:actions>
    </x-ui.chart>
</section>
```

## Dialog form

```blade
<x-ui.button x-on:click="$dispatch('open-dialog', 'rename')">Rename</x-ui.button>

<x-ui.dialog.form name="rename" title="Rename playlist" action="rename" submit-label="Save name">
    <x-ui.field label="Name" name="name" required>
        <x-ui.input wire:model="name" width="full" />
    </x-ui.field>
</x-ui.dialog.form>
```

The method returns nothing to close, a string to show an inline error, or leaves a field `aria-invalid` to stay open. Success toasts come from the server.

## Settings page

Tinted surface, one `x-ui.form-section` per topic (280px text column plus a panel), `x-ui.form guard` for the unsaved-changes prompt, `x-ui.form-actions sticky` when the form is long, `x-ui.error-summary` when more than 3 fields can fail. See [components/forms.md](forms.md) for the single, split, section-nav and wizard layouts.

## Empty, loading, error

- Empty: `x-ui.empty-state title="No payouts yet." description="..."` (one line, optional action). Never an icon tile.
- First load: the skeleton preset of the same height (`x-ui.skeleton.table`, `.stats`, `.chart`, `.list`).
- Refresh: `x-ui.loading-overlay target="period"` on the region.
- Error: `x-ui.alert tone="danger"` in place of the content.
