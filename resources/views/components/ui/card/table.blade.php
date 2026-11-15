{{--
    Table card: a header with actions, a flush x-ui.table edge to edge, and a footer
    for pagination. Props: title, description, caption (the table's hidden caption,
    defaults to the title), paginator (renders x-ui.pagination in the footer; pass
    livewire inside Livewire views), livewire, empty, loading (skeleton rows), rows
    (skeleton row count), error (message).
    Slots: actions, head (x-ui.table.th cells), default (x-ui.table.row rows), footer
    (replaces the pagination).
--}}
@props(['title', 'description' => null, 'caption' => null, 'paginator' => null, 'livewire' => false, 'empty' => 'Nothing here yet.', 'loading' => false, 'rows' => 5, 'error' => null])

<x-ui.card :title="$title" :description="$description" flush {{ $attributes }} data-card-table>
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset

    @if ($error)
        <div class="p-4"><x-ui.alert tone="danger">{{ $error }}</x-ui.alert></div>
    @elseif ($loading)
        <x-ui.skeleton.table :rows="$rows" :label="'Loading '.$title" flush />
    @elseif (! $slot->hasActualContent())
        <p class="type-meta px-4 py-3">{{ $empty }}</p>
    @else
        <x-ui.table flush :caption="$caption ?? $title">
            @isset($head)
                <x-slot:head>{{ $head }}</x-slot:head>
            @endisset
            {{ $slot }}
        </x-ui.table>
    @endif

    @if (isset($footer) && ! $error)
        <x-slot:footer>{{ $footer }}</x-slot:footer>
    @elseif ($paginator && ! $error && ! $loading && $paginator->hasPages())
        <x-slot:footer>
            <x-ui.pagination class="w-full" :paginator="$paginator" :livewire="$livewire" :aria-label="$title.' pagination'" />
        </x-slot:footer>
    @endif
</x-ui.card>
