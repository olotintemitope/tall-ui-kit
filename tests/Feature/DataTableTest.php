<?php

use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use TallUiKit\Livewire\Concerns\WithDataTable;
use TallUiKit\Support\DataTable\Action;
use TallUiKit\Support\DataTable\Column;
use TallUiKit\Support\DataTable\Filter;
use TallUiKit\Support\DataTable\InvalidTableDefinition;
use Workbench\App\DataTable\DemoPayment;
use Workbench\App\DataTable\DemoTables;
use Workbench\App\Livewire\TableGallery;

/*
 * WithDataTable and x-data-table, exercised on the workbench /tables page (320 invented
 * payments on a private SQLite connection, rebuilt before each test).
 */

beforeEach(function () {
    $this->withoutVite();
    DemoTables::reset();
});

function galleryTable(array $query = []): Testable
{
    return Livewire::withQueryParams($query)->test(TableGallery::class)->call('loadTable');
}

/** @return list<string> row ids in render order */
function renderedRowIds(Testable $component): array
{
    preg_match_all('/<tr wire:key="r(\d+)"/', $component->html(), $m);

    return $m[1];
}

describe('search', function () {
    it('searches the declared columns and relations, word by word', function () {
        $component = galleryTable()->set('search', 'ada obi');
        $ids = renderedRowIds($component);
        $expected = DemoPayment::query()->whereHas('customer', fn ($q) => $q->where('name', 'Ada Obi'))->count();

        expect($ids)->not->toBeEmpty()
            ->and($component->instance()->dataTable()->total)->toBe($expected)
            ->and($component->html())->toContain('<mark>Ada</mark>')->toContain('<mark>Obi</mark>');
    });

    it('ignores terms shorter than two characters and trims the term', function () {
        expect(galleryTable()->set('search', 'a')->instance()->dataTable()->total)->toBe(320)
            ->and(galleryTable()->set('search', '   ')->instance()->dataTable()->total)->toBe(320)
            ->and(galleryTable()->set('search', '  Danfo Diaries  ')->instance()->dataTable()->term)->toBe('Danfo Diaries');
    });

    it('escapes LIKE wildcards', function () {
        // "100%_Pure Vibes" is the only campaign with a % and an _; unescaped, "%_" would match everything.
        $component = galleryTable()->set('search', '%_');

        expect($component->instance()->dataTable()->total)->toBe(DemoPayment::query()->where('campaign', '100%_Pure Vibes')->count())
            ->and(galleryTable()->set('search', '0%_P')->instance()->dataTable()->total)->toBe(DemoPayment::query()->where('campaign', '100%_Pure Vibes')->count());
    });

    it('matches an id or an exact reference instead of a text search', function () {
        $payment = DemoPayment::query()->find(17);

        expect(renderedRowIds(galleryTable()->set('search', '#17')))->toBe(['17'])
            ->and(renderedRowIds(galleryTable()->set('search', '17')))->toBe(['17'])
            ->and(renderedRowIds(galleryTable()->set('search', strtolower($payment->reference))))->toBe([])
            ->and(renderedRowIds(galleryTable()->set('search', $payment->reference)))->toBe(['17']);
    });
});

describe('sort', function () {
    it('sorts allow-listed columns only and falls back to the default', function () {
        $component = galleryTable(['sort' => 'secret_column', 'dir' => 'sideways']);

        $component->assertSet('sort', '')->assertSet('direction', '');
        expect(renderedRowIds($component)[0])->toBe((string) DemoPayment::query()->orderByDesc('created_at')->orderByDesc('id')->value('id'));
    });

    it('cycles ascending, descending, then the default, and sorts through a relation', function () {
        $component = galleryTable();

        $component->call('sortBy', 'customer')->assertSet('sort', 'customer')->assertSet('direction', 'asc');
        $first = DemoPayment::query()->join('demo_customers', 'demo_customers.id', '=', 'demo_payments.customer_id')
            ->orderBy('demo_customers.name')->orderBy('demo_payments.id')->value('demo_payments.id');
        expect(renderedRowIds($component)[0])->toBe((string) $first);

        $component->call('sortBy', 'customer')->assertSet('direction', 'desc');
        $component->call('sortBy', 'customer')->assertSet('sort', '')->assertSet('direction', '');
        $component->call('sortBy', 'notes')->assertSet('sort', '');
    });

    it('keeps the direction to asc or desc and adds an id tie-breaker', function () {
        $component = galleryTable(['sort' => 'platform', 'dir' => 'DROP']);
        $component->assertSet('direction', 'asc');

        $ids = renderedRowIds($component);
        $expected = DemoPayment::query()->orderBy('platform')->orderBy('id')->limit(25)->pluck('id')->map(fn ($id) => (string) $id)->all();
        expect($ids)->toBe($expected);
    });
});

describe('filters', function () {
    it('applies each filter type and ignores unknown or invalid filters', function () {
        $count = fn (array $filters) => galleryTable(['filters' => $filters])->instance()->dataTable()->total;

        expect($count(['status' => ['failed', 'nope']]))->toBe(DemoPayment::query()->where('status', 'failed')->count())
            ->and($count(['platform' => 'blog']))->toBe(DemoPayment::query()->where('platform', 'blog')->count())
            ->and($count(['tier' => ['pro', 'starter']]))->toBe(DemoPayment::query()->whereIn('tier', ['pro', 'starter'])->count())
            ->and($count(['flagged' => '1']))->toBe(DemoPayment::query()->where('flagged', true)->count())
            ->and($count(['amount' => ['min' => '100000', 'max' => '150000']]))->toBe(DemoPayment::query()->whereBetween('amount', [100000, 150000])->count())
            ->and($count(['country' => 'Ghana']))->toBe(DemoPayment::query()->whereHas('customer', fn ($q) => $q->where('country', 'Ghana'))->count())
            ->and($count(['nonsense' => 'x', 'platform' => 'myspace']))->toBe(320)
            ->and($count(['amount' => ['min' => 'abc'], 'date' => ['from' => '2026-02-31']]))->toBe(320)
            ->and($count(['amount' => ['min' => 500, 'max' => 10]]))->toBe(320);
    });

    it('filters a date range inclusively in Lagos time', function () {
        $first = DemoPayment::query()->orderBy('created_at')->first()->created_at->timezone('Africa/Lagos')->format('Y-m-d');

        $total = galleryTable(['filters' => ['date' => ['from' => $first, 'to' => $first]]])->instance()->dataTable()->total;
        expect($total)->toBe(DemoPayment::query()->whereBetween('created_at', [$first.' 00:00:00', $first.' 23:59:59'])->count())
            ->and($total)->toBeGreaterThan(0);
    });

    it('shows active filters as tokens, removes one value, and clears all', function () {
        $component = galleryTable(['filters' => ['status' => ['pending', 'failed'], 'platform' => 'blog']]);

        expect(collect($component->instance()->dataTable()->tokens)->pluck('label')->all())->toBe(['Status: Pending', 'Status: Failed', 'Platform: Blog']);
        $component->assertSeeHtml('aria-label="Remove filter: Status: Failed"');

        $component->call('removeFilter', 'status', 'pending')->assertSet('filters.status', ['failed']);
        $component->call('clearFilters')->assertSet('filters', []);
    });
});

describe('pagination', function () {
    it('resets to page 1 when search, filters, sort or page size change', function (string $method, array $args) {
        $component = galleryTable(['page' => 3]);
        expect($component->instance()->getPage())->toBe(3);

        $component->{$method}(...$args);
        expect($component->instance()->getPage())->toBe(1);
    })->with([
        'search' => ['set', ['search', 'ada']],
        'filter' => ['set', ['filters.platform', 'blog']],
        'sort' => ['call', ['sortBy', 'amount']],
        'per page' => ['set', ['perPage', 50]],
    ]);

    it('allows only 10, 25, 50 or 100 rows per page', function () {
        galleryTable(['per' => 1000])->assertSet('perPage', 25);
        $component = galleryTable(['per' => 50])->assertSet('perPage', 50);
        expect(renderedRowIds($component))->toHaveCount(50);
        $component->set('perPage', 7)->assertSet('perPage', 25);
    });

    it('shows the range and the page size', function () {
        galleryTable(['page' => 2])->assertSeeHtml("Showing 26\u{2013}50 of 320")->assertSeeHtml('aria-label="Next page"');
    });
});

describe('selection and actions', function () {
    it('re-scopes selected ids through the table query before a bulk action', function () {
        $component = galleryTable(['state' => 'empty'])->set('selected', ['1', '2', 'x', '-3']);

        $component->assertSet('selected', ['1', '2']);
        // The empty state's query reaches no rows, so nothing is flagged even though the ids exist.
        DemoPayment::query()->whereKey([1, 2])->update(['flagged' => false]);
        $component->call('runBulkAction', 'flag')->assertDispatched('toast', type: 'info', message: 'Select at least one row.');
        expect(DemoPayment::query()->whereKey([1, 2])->where('flagged', true)->count())->toBe(0);

        $live = galleryTable()->set('selected', ['5', '6']);
        DemoPayment::query()->whereKey([5, 6])->update(['flagged' => false]);
        $live->call('runBulkAction', 'flag');
        expect(DemoPayment::query()->whereKey([5, 6])->where('flagged', true)->count())->toBe(2)
            ->and($live->get('selected'))->toBe([]);
    });

    it('selects every matching row as criteria, not ids', function () {
        $component = galleryTable(['filters' => ['platform' => 'social']])->call('selectAllMatchingRows');

        $component->assertSet('selectAllMatching', true);
        expect(count($component->get('selected')))->toBe(25)
            ->and($component->instance()->dataTable()->selectedCount)->toBe(DemoPayment::query()->where('platform', 'social')->count());

        $component->assertSeeHtml('selected');
        $component->call('runBulkAction', 'delete', true);
        expect(DemoPayment::query()->where('platform', 'social')->count())->toBe(0)
            ->and(DemoPayment::query()->count())->toBeGreaterThan(0);
    });

    it('needs confirmation for a destructive bulk action', function () {
        $component = galleryTable()->set('selected', ['9']);

        $component->call('runBulkAction', 'delete')->assertHasErrors('action');
        expect(DemoPayment::query()->whereKey(9)->exists())->toBeTrue();

        $component->call('runBulkAction', 'delete', true);
        expect(DemoPayment::query()->whereKey(9)->exists())->toBeFalse();
    });

    it('refuses unknown actions and actions the user may not run', function () {
        galleryTable()->set('selected', ['1'])->call('runBulkAction', 'drop_table')->assertNotFound();

        $component = Livewire::test(GatedActionTable::class);
        expect(collect($component->instance()->dataTable()->bulkActions)->pluck('key')->all())->toBe(['note']);
        $component->set('selected', ['1'])->call('runBulkAction', 'purge', true)->assertForbidden();
        expect(DemoPayment::query()->whereKey(1)->exists())->toBeTrue();
    });

    it('runs row actions on rows the query can reach, honouring visibility', function () {
        DemoPayment::query()->whereKey(12)->update(['flagged' => false]);
        $component = galleryTable();

        $component->call('runRowAction', 'flag', '12');
        expect(DemoPayment::query()->find(12)->flagged)->toBeTrue();

        galleryTable()->call('runRowAction', 'flag', '12')->assertForbidden();
        galleryTable()->call('runRowAction', 'delete', '13')->assertHasErrors('action');
        expect(DemoPayment::query()->whereKey(13)->exists())->toBeTrue();
        galleryTable()->call('runRowAction', 'flag', '99999')->assertNotFound();
        galleryTable(['state' => 'empty'])->call('runRowAction', 'delete', '14', true)->assertNotFound();
        expect(DemoPayment::query()->whereKey(14)->exists())->toBeTrue();
    });
});

describe('definition validation (local and testing)', function () {
    it('throws on a default sort that is not a column', function () {
        expect(definitionError(BadDefaultSortTable::class))->toBe('Data table [BadDefaultSortTable]: default sort column [nope] is not a column. Known columns: id, name.');
    });

    it('throws on a default sort column that cannot sort', function () {
        expect(definitionError(UnsortableDefaultTable::class))->toBe('Data table [UnsortableDefaultTable]: default sort column [name] is not sortable.');
    });

    it('throws on a bad filter type, key or badge', function () {
        expect(fn () => Filter::make('kind', 'Kind', 'slider'))->toThrow(InvalidTableDefinition::class, 'Filter [kind] has an unknown type [slider]')
            ->and(fn () => Filter::select('Bad Key', 'X'))->toThrow(InvalidTableDefinition::class, 'lower snake case')
            ->and(fn () => Filter::status('s', 'S')->options(['a' => ['label' => 'A', 'variant' => 'purple']]))->toThrow(InvalidTableDefinition::class, 'unknown badge variant [purple]');
    });

    it('throws on duplicate columns, required hidden columns, empty options and bad defaults', function (string $component, string $message) {
        expect(definitionError($component))->toContain($message);
    })->with([
        [DuplicateColumnTable::class, 'column key [id] is defined twice.'],
        [RequiredHiddenTable::class, 'column [id] is required and hidden'],
        [EmptyOptionsTable::class, 'filter [kind] (select) has no options.'],
        [BadFilterDefaultTable::class, 'filter [kind] default "z" is not a valid value for it.'],
    ]);

    it('throws on an unknown column role or export type', function () {
        expect(fn () => Column::make('a', 'A')->role('loud'))->toThrow(InvalidTableDefinition::class, 'role [loud]')
            ->and(fn () => Column::make('a', 'A')->export('xml'))->toThrow(InvalidTableDefinition::class, 'export type [xml]')
            ->and(fn () => Column::make('A-B', 'A'))->toThrow(InvalidTableDefinition::class, 'lower snake case');
    });
});

/** The InvalidTableDefinition message a component throws on mount (Livewire wraps it in a ViewException). */
function definitionError(string $component): ?string
{
    try {
        Livewire::test($component);
    } catch (Throwable $e) {
        while ($e && ! $e instanceof InvalidTableDefinition) {
            $e = $e->getPrevious();
        }

        return $e?->getMessage();
    }

    return null;
}

/** Test components: minimal tables over the demo payments. */
abstract class DemoTestTable extends Component
{
    use WithDataTable;

    protected function tableDeferred(): bool
    {
        return false;
    }

    protected function tableQuery(): Builder
    {
        return DemoPayment::query();
    }

    protected function tableColumns(): array
    {
        return [Column::make('id', 'ID')->sortable(), Column::make('name', 'Name')];
    }

    public function render(): string
    {
        return '<div>{{ count($this->dataTable()->columns) }}</div>';
    }
}

class BadDefaultSortTable extends DemoTestTable
{
    protected function tableDefaultSort(): ?array
    {
        return ['nope', 'desc'];
    }
}

class UnsortableDefaultTable extends DemoTestTable
{
    protected function tableDefaultSort(): ?array
    {
        return ['name', 'asc'];
    }
}

class DuplicateColumnTable extends DemoTestTable
{
    protected function tableColumns(): array
    {
        return [Column::make('id', 'ID'), Column::make('id', 'Again')];
    }
}

class RequiredHiddenTable extends DemoTestTable
{
    protected function tableColumns(): array
    {
        return [Column::make('id', 'ID')->required()->hidden()];
    }
}

class EmptyOptionsTable extends DemoTestTable
{
    protected function tableFilters(): array
    {
        return [Filter::select('kind', 'Kind')];
    }
}

class BadFilterDefaultTable extends DemoTestTable
{
    protected function tableFilters(): array
    {
        return [Filter::select('kind', 'Kind')->options(['a' => 'A'])->default('z')];
    }
}

class GatedActionTable extends DemoTestTable
{
    protected function tableBulkActions(): array
    {
        return [
            Action::bulk('note', 'Note')->handle(fn () => 'ok'),
            Action::bulk('purge', 'Purge')->ability('*')->destructive('Purge?', 'Gone.')->handle(fn (Builder $rows) => $rows->delete()),
        ];
    }
}
