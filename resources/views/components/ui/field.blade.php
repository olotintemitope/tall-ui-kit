{{--
    One form field: label row, control, then a single line for the hint or the error.
    Props: label, name (dot or bracket notation; error lookup and id), id (override),
    hint (caption; replaced by the error, never both), error (explicit message; otherwise
    $errors->first(name)), required (muted " *" plus aria-required on the control; no
    native validation), width (full|lg|md|sm|xs, passed to the control).
    The x-ui control in the slot picks up the id, aria-describedby, aria-invalid and width
    through @aware: `<x-ui.field label="Email" name="email"><x-ui.input wire:model="email" /></x-ui.field>`.
    Spacing: label to control 6px, control to hint or error 4px; nothing is reserved
    when both are absent.
    inline: the horizontal layout (x-ui.form-layout.inline): from sm the label (and the
    hint under it) sit in a 220px column on the left, the control and its error on the
    right, centred on the control; stacked on phones.
--}}
@props(['label' => null, 'name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null, 'inline' => false])

@php
    use TallUiKit\Support\FormControl;

    $controlId = FormControl::id($id, $name);
    $message = FormControl::error($errors ?? null, $name, $error);
    // When no field width is explicitly requested, controls should all fill
    // their column. This keeps textareas, inputs and selects aligned in both
    // single- and multi-column forms, regardless of their individual defaults.
    $uniformControlWidth = $width === null
        ? '[&_[data-control-box]]:w-full [&_[data-control-box]]:max-w-none [&_[data-ui-select]]:w-full [&_[data-ui-select]]:max-w-none'
        : null;
@endphp

@if ($inline)
<div {{ $attributes->class(['grid min-w-0 grid-cols-1 gap-x-6 gap-y-1.5 py-3 sm:grid-cols-[220px_minmax(0,1fr)] sm:items-center', $uniformControlWidth]) }} @if ($message) data-invalid @endif data-field-inline>
    <div class="flex min-w-0 flex-col">
        @if ($label)
            <label @if ($controlId) for="{{ $controlId }}" id="{{ $controlId }}-label" @endif class="type-field-label">
                {{ $label }}@if ($required)<span class="text-ink-muted" aria-hidden="true"> *</span>@endif
            </label>
        @endif
        @if ($hint && ! $message)
            <p @if ($controlId) id="{{ FormControl::hintId($controlId) }}" @endif class="type-caption mt-0.5">{{ $hint }}</p>
        @endif
    </div>
    <div class="flex min-w-0 flex-col">
        {{ $slot }}
        @if ($message)
            <p @if ($controlId) id="{{ FormControl::errorId($controlId) }}" @endif class="type-caption text-danger-text! mt-1" aria-live="polite">{{ $message }}</p>
        @endif
    </div>
</div>
@else
<div {{ $attributes->class(['flex min-w-0 flex-col', $uniformControlWidth]) }} @if ($message) data-invalid @endif>
    @if ($label)
        <label @if ($controlId) for="{{ $controlId }}" id="{{ $controlId }}-label" @endif class="type-field-label mb-1.5">
            {{ $label }}@if ($required)<span class="text-ink-muted" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    {{ $slot }}

    @if ($message)
        <p @if ($controlId) id="{{ FormControl::errorId($controlId) }}" @endif class="type-caption text-danger-text! mt-1" aria-live="polite">{{ $message }}</p>
    @elseif ($hint)
        <p @if ($controlId) id="{{ FormControl::hintId($controlId) }}" @endif class="type-caption mt-1">{{ $hint }}</p>
    @endif
</div>
@endif
