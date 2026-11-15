{{--
    x-data-table.properties: the "Properties" panel under each table on /admin/ui/tables.
    Generated from the component's own definitions (WithDataTable::tableProperties()), so
    it cannot drift from the code: the table settings and current state as a two-column
    dl, then columns, filters and actions as flush kit tables, one row per definition.
    The definition tables do not change between requests, so they are printed once (first
    render) inside wire:ignore; the State list is re-rendered with every request.
    Props: properties (array: section name => list of rows), title (Properties), name (id prefix).
--}}
@props(['properties', 'title' => 'Properties', 'name' => 'dt'])
@php $initial = ! app('livewire')->isLivewireRequest(); @endphp

<section {{ $attributes->class(['mt-6 border-t border-line pt-4']) }} aria-labelledby="{{ $name }}-properties" data-dt-properties>
    <h2 id="{{ $name }}-properties" class="type-subheading">{{ $title }}</h2>

    <div class="mt-3 grid gap-6 lg:grid-cols-2">
        @foreach (['Table', 'State'] as $section)
            @if (! empty($properties[$section]))
                <div>
                    <x-ui.section-label as="h3" class="mb-2">{{ $section }}</x-ui.section-label>
                    <dl class="divide-y divide-line border-y border-line">
                        @foreach ($properties[$section] as $row)
                            <div class="flex items-baseline justify-between gap-4 py-1.5">
                                <dt class="type-numeric text-ink-muted">{{ $row['name'] }}</dt>
                                <dd class="type-numeric text-ink text-right break-all" data-prop="{{ $row['name'] }}">{{ $row['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        @endforeach
    </div>

    <div wire:ignore data-dt-properties-definitions>@if ($initial)
    @foreach (['Columns', 'Filters', 'Actions'] as $section)
        @if (! empty($properties[$section]))
            <div class="mt-6">
                <x-ui.section-label as="h3" class="mb-2">{{ $section }} ({{ count($properties[$section]) }})</x-ui.section-label>
                <x-ui.table :caption="$section.' properties'">
                    <x-slot:head>
                        @foreach (array_keys($properties[$section][0]) as $heading)
                            <x-ui.table.th>{{ $heading }}</x-ui.table.th>
                        @endforeach
                    </x-slot:head>
                    @foreach ($properties[$section] as $i => $row)
                        <x-ui.table.row wire:key="{{ $name }}-prop-{{ $section }}-{{ $i }}">
                            @foreach ($row as $key => $value)
                                <x-ui.table.td :mono="$key !== 'label'">{{ $value === '' ? '' : $value }}</x-ui.table.td>
                            @endforeach
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table>
            </div>
        @endif
    @endforeach
    @endif</div>
</section>
