{{--
    Text input, h-9. Props: type (text; email, url, tel, search, number and the date and
    time types get the right inputmode, autocomplete, autocapitalize and spellcheck by
    default, each overridable as an attribute), size (sm h-8 for dense toolbars | default h-9, `md` is an alias | lg h-10),
    width (full|lg|md|sm|xs; default md, or the field's width), invalid (force the error
    state). Slots: prefix and suffix (a currency sign or a unit), inside the border with a
    hairline divider (`<x-slot:prefix plain>` drops the divider, as x-ui.search does). Inside x-ui.field it takes the field's id, hint and error wiring; on its
    own it looks up $errors by name or wire:model. Other attributes (wire:model, x-model,
    disabled, placeholder) go to the <input>.
--}}
@props(['type' => 'text', 'size' => 'default', 'invalid' => null, 'width' => null])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName);
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;

    $box = [
        'flex items-stretch overflow-hidden rounded-md border bg-surface transition-[border-color,box-shadow] duration-100',
        FormControl::width($width, in_array($type, ['date', 'time', 'month', 'week'], true) ? 'date' : ($type === 'datetime-local' ? 'datetime' : 'md')),
        FormControl::height($size),
        'border-danger focus-within:ring-2 focus-within:ring-danger/20' => $isInvalid,
        'border-control hover:border-control-hover focus-within:border-focus focus-within:ring-2 focus-within:ring-primary/20' => ! $isInvalid,
        'has-disabled:bg-surface-subtle has-disabled:cursor-not-allowed has-disabled:border-dashed has-disabled:hover:border-control',
    ];

    // Keyboard and autofill hints per type; an explicit attribute always wins.
    $typeDefaults = match ($type) {
        'email' => ['inputmode' => 'email', 'autocomplete' => 'email', 'autocapitalize' => 'none', 'spellcheck' => 'false'],
        'url' => ['inputmode' => 'url', 'autocomplete' => 'url', 'autocapitalize' => 'none', 'spellcheck' => 'false'],
        'tel' => ['inputmode' => 'tel', 'autocomplete' => 'tel'],
        'search' => ['inputmode' => 'search', 'enterkeyhint' => 'search'],
        'number' => ['inputmode' => 'decimal'],
        default => [],
    };

    $controlAttributes = $attributes->except(['id', 'class', 'aria-describedby'])->merge($typeDefaults)->merge([
        'type' => $type,
        'id' => $controlId,
        'name' => $attributes->get('name', $name),
        'aria-invalid' => $isInvalid ? 'true' : null,
        'aria-required' => $required ? 'true' : null,
        'data-ui' => 'input',
    ])->merge([
        // The field's hint or error first, then any extra description the caller passed (a strength hint, a country prefix).
        'aria-describedby' => implode(' ', array_unique(array_filter(explode(' ', FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message).' '.$attributes->get('aria-describedby'))))) ?: null,
    ]);
@endphp

<div {{ $attributes->only('class')->class($box) }} data-control-box>
    @isset($prefix)
        <span @class(['flex shrink-0 select-none items-center type-meta', 'pl-3 -mr-1.5' => $prefix->attributes->has('plain'), 'border-r border-line px-2.5' => ! $prefix->attributes->has('plain')]) aria-hidden="true">{{ $prefix }}</span>
    @endisset
    <input {{ $controlAttributes->class([
        'peer min-w-0 flex-1 bg-transparent px-3 font-body text-body text-ink placeholder:text-ink-muted outline-none tabular-nums',
        'disabled:cursor-not-allowed disabled:text-ink-muted [&::-webkit-search-cancel-button]:hidden',
    ]) }} />
    @isset($suffix)
        <span @class(['flex shrink-0 select-none items-center type-meta', 'pr-1.5' => $suffix->attributes->has('plain'), 'border-l border-line px-2.5' => ! $suffix->attributes->has('plain')])>{{ $suffix }}</span>
    @endisset
</div>
