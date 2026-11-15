{{--
    x-data-table.states: the non-row states. Props: state, table (TableView), colspan.
      skeleton     first load: skeleton rows inside the real table body, at the final row
                   height: body-line boxes in 40px rows (32px compact), and a body plus meta
                   line with the 8px padding for two-line columns, so nothing jumps when the
                   rows arrive. Announced once ("Loading transactions").
      empty        the dataset is empty: one line (tableEmpty() title) plus an optional
                   action (actionLabel + actionHref, or the slot).
      no-results   search or filters match nothing: "No results for 'xyz'" and a "Clear
                   search and filters" button (focus goes to the search box).
      error        the query failed: an inline danger x-ui.alert with Retry.
    Refreshing while rows are on screen is x-ui.loading-overlay around the table (kit).
--}}
@props(['state', 'table', 'colspan' => 1])

@switch($state)
    @case('skeleton')
        @php $widths = ['w-20', 'w-32', 'w-24', 'w-16', 'w-28', 'w-14']; @endphp
        @for ($i = 0; $i < min(10, $table->perPage); $i++)
            <tr wire:key="{{ $table->name }}-skeleton-{{ $i }}" aria-hidden="true" data-dt-skeleton>
                @if ($table->bulkActions !== [])
                    <td><x-ui.skeleton class="size-4" /></td>
                @endif
                @foreach ($table->columns as $c => $column)
                    @php $two = $column->secondaryLine !== null || $column->wrap; $w = $widths[($c + $i) % count($widths)]; @endphp
                    <td @if ($column->numeric) data-n @endif @if ($two) data-wrap @endif>
                        <x-ui.skeleton.line role="body" :width="$w" @class(['justify-end' => $column->numeric]) />
                        @if ($two)<x-ui.skeleton.line role="meta" width="w-24" @class(['justify-end' => $column->numeric]) />@endif
                    </td>
                @endforeach
                @if ($table->rowActions !== [])
                    <td></td>
                @endif
            </tr>
        @endfor
        {{-- One real row while loading, so the table always has a row (explicit roles for the stacked layout). The announcement is the table's live region outside the <table>: a status role inside a row is invalid ARIA. --}}
        <tr class="sr-only" role="row" data-dt-loading-row><td colspan="{{ $colspan }}" role="cell">Loading {{ $table->noun }}</td></tr>
        @break

    @case('empty')
        <div class="mt-3" data-dt-empty>
            <x-ui.empty-state
                :title="$table->empty['title']"
                :description="$table->empty['description'] ?? null"
                :action-label="$table->empty['actionLabel'] ?? null"
                :action-href="$table->empty['actionHref'] ?? null"
            >
                @if (trim((string) $slot) !== '')
                    <x-slot:action>{{ $slot }}</x-slot:action>
                @endif
            </x-ui.empty-state>
        </div>
        @break

    @case('no-results')
        <div class="mt-3 border-t border-line py-8" data-dt-no-results>
            <p class="type-body-strong">
                @if ($table->term !== '')
                    No results for &lsquo;{{ $table->term }}&rsquo;@if ($table->tokens !== []) with these filters @endif
                @else
                    No results for these filters
                @endif
            </p>
            <x-ui.button variant="outline" size="sm" class="mt-3" wire:click="clearSearchAndFilters" x-on:click="setIntent({ kind: 'key', key: 'search' })" data-fk="clear-all">
                Clear search and filters
            </x-ui.button>
        </div>
        @break

    @case('error')
        <x-ui.alert tone="danger" class="mt-3" data-dt-error>
            <span>{{ $table->error }}</span>
            <x-ui.button variant="link" size="sm" class="h-auto px-0" wire:click="retryTable" data-fk="retry">Retry</x-ui.button>
        </x-ui.alert>
        @break
@endswitch
