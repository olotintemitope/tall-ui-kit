{{--
    A group of related controls under one name: <fieldset> and <legend>. The legend is in
    the field-label role (or the subheading role with `size="subheading"` for a section
    of a form); an optional description in meta sits under it and is linked to the
    group with aria-describedby. No border or box: grouping comes from the legend and
    16px spacing. Props: legend, description, size (field | subheading), hideLegend
    (keep it for assistive tech only), disabled (disables every control inside, natively),
    invalid (data-invalid for the error summary), id.
--}}
@props(['legend', 'description' => null, 'size' => 'field', 'hideLegend' => false, 'disabled' => false, 'id' => null])

@php
    $id = $id ?? 'fieldset-'.\Illuminate\Support\Str::slug($legend);
@endphp

<fieldset {{ $attributes->class(['min-w-0']) }} @disabled($disabled) @if ($description) aria-describedby="{{ $id }}-description" @endif id="{{ $id }}" data-fieldset>
    <legend @class([
        'sr-only' => $hideLegend,
        'type-field-label' => $size !== 'subheading',
        'type-subheading' => $size === 'subheading',
        'float-left w-full' => ! $hideLegend,
    ])>{{ $legend }}</legend>
    @if ($description)
        <p id="{{ $id }}-description" class="type-meta clear-left pt-1">{{ $description }}</p>
    @endif
    <div class="clear-left flex flex-col gap-4 pt-3">{{ $slot }}</div>
</fieldset>
