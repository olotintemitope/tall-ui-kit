{{--
    List card: a header, rows divided by 1px rules, and an optional "View all" footer.
    Rows are x-ui.kv (as="dl"), x-ui.ranked-item (as="ol") or x-ui.card.row (as="ul",
    the default: body-strong title, a meta line and an optional value).
    Props: title, description, as (ul | ol | dl), empty (one meta line when there are
    no rows), viewAll (href), viewAllLabel, loading (skeleton rows), rows (how many, 3), error (message).
    Slots: actions, default (rows).
--}}
@props(['title', 'description' => null, 'as' => 'ul', 'empty' => 'Nothing here yet.', 'viewAll' => null, 'viewAllLabel' => 'View all', 'loading' => false, 'rows' => 3, 'error' => null])

@php $tag = in_array($as, ['ul', 'ol', 'dl'], true) ? $as : 'ul'; @endphp

<x-ui.card :title="$title" :description="$description" flush {{ $attributes }} data-card-list>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    @if ($error)
        <div class="p-4"><x-ui.alert tone="danger">{{ $error }}</x-ui.alert></div>
    @elseif ($loading)
        <x-ui.skeleton.list :rows="$rows" :label="'Loading '.$title" class="px-4" flush />
    @elseif (! $slot->hasActualContent())
        <p class="type-meta px-4 py-3">{{ $empty }}</p>
    @else
        <{{ $tag }} class="px-4 *:last:border-b-0">{{ $slot }}</{{ $tag }}>
    @endif

    @if ($viewAll && ! $error)
        <x-slot:footer>
            <x-ui.link-arrow :href="$viewAll">{{ $viewAllLabel }}</x-ui.link-arrow>
        </x-slot:footer>
    @endif
</x-ui.card>
