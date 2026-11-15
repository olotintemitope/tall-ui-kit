{{--
    x-data-table.filter: one filter control, by type. Props: filter (Filter), context
    (inline: compact toolbar control with an accessible name; sheet: labelled control for
    the side sheet), name (table name, for ids). Rendered once inside wire:ignore: values
    come from $wire (wire:model, or filterHas() and filterCount() for multi filters).

      select, boolean   x-ui.select bound to filters.{key} ("All" or "Any" placeholder)
      multi, status     inline: x-ui.dropdown of menuitemcheckbox items (status options as
                        x-ui.badge); sheet: x-ui.checkbox list. Calls toggleFilterValue().
      date-range        two x-ui.date inputs bound to filters.{key}.from and .to
      number-range      two numeric x-ui.input bound to filters.{key}.min and .max (500ms debounce)

    Every value is re-validated on the server (Filter::normalize); this view only binds.
--}}
@props(['filter', 'context' => 'inline', 'name' => 'dt'])

@php
    /** @var \TallUiKit\Support\DataTable\Filter $filter */
    $id = $name.'-'.$context.'-filter-'.$filter->key;
    $inline = $context === 'inline';
    $model = 'filters.'.$filter->key;
    $fk = ($inline ? "" : "sheet-")."filter-".$filter->key;
@endphp

<div {{ $attributes->class(['flex', 'flex-col gap-1.5' => ! $inline, 'items-center gap-1.5' => $inline]) }} data-dt-filter="{{ $filter->key }}">
    @if (! $inline && ! $filter->isMulti())
        <label for="{{ $id }}" id="{{ $id }}-label" class="type-field-label">{{ $filter->label }}</label>
    @endif

    @switch($filter->type)
        @case('select')
        @case('boolean')
            <x-ui.select
                :id="$id"
                size="sm"
                :width="$inline ? 'auto' : 'full'"
                :options="collect($filter->options)->mapWithKeys(fn ($o) => [$o['value'] => $o['label']])->all()"
                :placeholder="$filter->defaultValue() !== null ? null : ($filter->placeholder ?? ($filter->type === 'boolean' ? 'Any' : 'All'))"
                wire:model.live="{{ $model }}"
                :aria-label="$inline ? $filter->label : null"
                data-fk="{{ $fk }}"
            />
            @break

        @case('multi')
        @case('status')
            @if ($inline)
                <x-ui.dropdown align="left" width="min-w-52">
                    <x-slot:trigger>
                        <x-ui.button variant="outline" size="sm" data-fk="{{ $fk }}">
                            {{ $filter->label }}
                            <x-ui.badge size="sm" variant="neutral" x-show="filterCount({{ \Illuminate\Support\Js::from($filter->key) }}) > 0" x-cloak><span class="sr-only">, </span><span x-text="filterCount({{ \Illuminate\Support\Js::from($filter->key) }})"></span><span class="sr-only"> selected</span></x-ui.badge>
                            <x-ui.icon name="chevron-down" class="text-ink-muted" />
                        </x-ui.button>
                    </x-slot:trigger>
                    @foreach ($filter->options as $option)
                        @php $has = 'filterHas('.\Illuminate\Support\Js::from($filter->key).', '.\Illuminate\Support\Js::from($option['value']).')'; @endphp
                        <x-ui.dropdown.item
                            role="menuitemcheckbox"
                            x-bind:aria-checked="{{ $has }} ? 'true' : 'false'"
                            data-keep-open
                            wire:click="toggleFilterValue('{{ $filter->key }}', '{{ $option['value'] }}')"
                        >
                            <x-ui.icon name="check" class="text-ink" x-bind:class="{{ $has }} ? '' : 'invisible'" />
                            @if ($option['variant'])
                                <x-ui.badge size="sm" :variant="$option['variant']">{{ $option['label'] }}</x-ui.badge>
                            @else
                                {{ $option['label'] }}
                            @endif
                        </x-ui.dropdown.item>
                    @endforeach
                </x-ui.dropdown>
            @else
                <fieldset class="flex flex-col gap-2">
                    <legend class="type-field-label mb-1.5">{{ $filter->label }}</legend>
                    @foreach ($filter->options as $option)
                        <x-ui.checkbox
                            :id="$id.'-'.$option['value']"
                            :label="$option['label']"
                            x-bind:checked="filterHas({{ \Illuminate\Support\Js::from($filter->key) }}, {{ \Illuminate\Support\Js::from($option['value']) }})"
                            wire:click="toggleFilterValue('{{ $filter->key }}', '{{ $option['value'] }}')"
                        />
                    @endforeach
                </fieldset>
            @endif
            @break

        @case('date-range')
            <div @class(['flex items-center gap-1.5', 'w-full' => ! $inline]) role="group" @if ($inline) aria-label="{{ $filter->label }}" @else aria-labelledby="{{ $id }}-label" @endif>
                @unless ($inline)<span id="{{ $id }}-label" class="sr-only">{{ $filter->label }}</span>@endunless
                <x-ui.date :id="$id" size="sm" :width="$inline ? null : 'full'" wire:model.live="{{ $model }}.from" :aria-label="$filter->label.' from'" data-fk="{{ $fk }}-from" />
                <span class="type-meta" aria-hidden="true">to</span>
                <x-ui.date :id="$id.'-to'" size="sm" :width="$inline ? null : 'full'" wire:model.live="{{ $model }}.to" :aria-label="$filter->label.' to'" data-fk="{{ $fk }}-to" />
            </div>
            @break

        @case('number-range')
            <div @class(['flex items-center gap-1.5', 'w-full' => ! $inline]) role="group" @if ($inline) aria-label="{{ $filter->label }}" @endif>
                <x-ui.input :id="$id" type="number" inputmode="decimal" size="sm" :width="$inline ? 'xs' : 'full'" class="min-w-24" placeholder="Min" wire:model.live.debounce.500ms="{{ $model }}.min" :aria-label="$filter->label.' minimum'" data-fk="{{ $fk }}-min" />
                <span class="type-meta" aria-hidden="true">to</span>
                <x-ui.input :id="$id.'-max'" type="number" inputmode="decimal" size="sm" :width="$inline ? 'xs' : 'full'" placeholder="Max" wire:model.live.debounce.500ms="{{ $model }}.max" :aria-label="$filter->label.' maximum'" data-fk="{{ $fk }}-max" />
            </div>
            @break
    @endswitch
</div>
