{{--
    Multi-line text, at least 3 rows, resizes vertically. Props: rows (3), width (default
    full), invalid. Wiring and passthrough as x-ui.input.
--}}
@props(['rows' => 3, 'invalid' => null, 'width' => null])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName);
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
@endphp

<textarea {{ $attributes->except('id')->merge([
    'id' => $controlId,
    'name' => $attributes->get('name', $name),
    'rows' => max(3, (int) $rows),
    'aria-invalid' => $isInvalid ? 'true' : null,
    'aria-required' => $required ? 'true' : null,
    'aria-describedby' => FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message),
    'data-ui' => 'textarea',
    'data-control-box' => true,
])->class([
    'block min-h-[76px] resize-y rounded-md border bg-surface px-3 py-2 font-body text-body text-ink placeholder:text-ink-muted outline-none transition-[border-color,box-shadow] duration-100',
    FormControl::width($width, 'full'),
    'disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:text-ink-muted',
    'border-danger focus:ring-2 focus:ring-danger/20' => $isInvalid,
    'border-control hover:border-control-hover focus:border-focus focus:ring-2 focus:ring-primary/20' => ! $isInvalid,
]) }}>{{ $slot }}</textarea>
