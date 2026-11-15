{{--
    x-data-table.tokens: the active-filter summary. One x-ui.filter-token per active value
    (a multi filter gives one per chosen option, so each can go on its own) in a list after
    a visually hidden "Active filters" heading, then "Clear all". Filters sitting at their
    default are not listed. Removing a token moves focus to the next token, then the
    previous one, then the search box. Props: table (TableView).
--}}
@props(['table'])

<div class="mt-3 flex flex-wrap items-center gap-2" data-dt-tokens>
    <h2 class="sr-only">Active filters</h2>
    <ul class="flex flex-wrap items-center gap-2">
        @foreach ($table->tokens as $token)
            @php $remove = 'removeFilter('.\Illuminate\Support\Js::from($token['key']).($token['value'] !== null ? ', '.\Illuminate\Support\Js::from($token['value']) : '').')'; @endphp
            <li wire:key="{{ $table->name }}-token-{{ $token['key'] }}-{{ $token['value'] ?? 'all' }}" data-dt-token>
                <x-ui.filter-token
                    :label="$token['label']"
                    x-on:click="removeToken($el)"
                    :wire:click="$remove"
                />
            </li>
        @endforeach
    </ul>
    <x-ui.button variant="ghost" size="sm" wire:click="clearFilters" x-on:click="setIntent({ kind: 'key', key: 'search' })" data-fk="clear-filters">Clear all</x-ui.button>
</div>
