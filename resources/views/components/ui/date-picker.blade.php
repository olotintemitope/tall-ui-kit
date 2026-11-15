{{--
    The custom date and time picker behind x-ui.date, x-ui.datetime, x-ui.time-input,
    x-ui.month, x-ui.week and x-ui.date-range (use those; this is their shared engine).
    No native picker: the browser's popup cannot be kept below its field or styled.
    Behaviour: resources/js/datepicker.js (`uiDatePicker`), parsing and formats in
    date-core.js; docs/components/forms.md "Dates and times" has the keyboard map.

    Markup: a text field showing the formatted value ("15 Nov 2026"; typing is parsed
    leniently on Enter or blur: 15/11/2026, 15 Nov 2026, ISO), a calendar (clock) button,
    a hidden input per value for plain form posts, and a popup anchored below the field
    (x-anchor.bottom-start.noflip, fixed, so tables, dialogs and sheets never clip it).
    The popup is empty until first opened; the script builds it then (one grid, no Alpine
    per cell). The server prints the label from the bound value, so the first paint and
    every morph show it.

    Props: mode (date | datetime | time | month | week | range), size (default h-9 | sm
    h-8), width (full|lg|md|sm|xs; default w-40, datetime w-56, range w-64), invalid,
    min, max (in the value format), step (seconds, time and datetime: sets the minute
    step), minuteStep (5), hours (24 | 12: display only, the value stays H:i), disabledDates
    (a list of Y-m-d dates and weekday tokens: sat, sun, weekends; or the name of a global
    JS function given a Y-m-d date that returns true to disable it), weekStart (0 Sunday to
    6 Saturday; default the visitor's locale, else Monday; week mode is always Monday),
    defaultTime (09:00, the time a datetime takes when only a day is chosen), placeholder,
    defer (start the picker's Alpine later, docs/performance.md). Range only: presets
    (true for Last 7 days, Last 30 days, This month, Custom; or keys and labels, see
    DateInput::PRESETS, or ['label', 'start', 'end'] entries; false for none), start and
    end (initial values without a binding), startName and endName (hidden inputs; default
    name[start] and name[end]).
    Values: date Y-m-d, time H:i, month Y-m, week 2026-W47, datetime Y-m-d\TH:i; a range
    is two dates.
    Binding: wire:model (any modifier) or x-model on the component. A range binds two
    properties with wire:model.start="from" and wire:model.end="to" (add .live to either
    for a request when the range is chosen; one request for both), or one array
    ['start' => , 'end' => ] with wire:model. Wiring (id, label, hint, error,
    aria-describedby) as x-ui.input; other attributes (aria-label, required, disabled,
    data-*, autofocus) go to the text field.
--}}
@props([
    'mode' => 'date', 'size' => 'default', 'invalid' => null, 'width' => null, 'min' => null, 'max' => null, 'step' => null,
    'minuteStep' => null, 'hours' => 24, 'disabledDates' => null, 'weekStart' => null, 'defaultTime' => '09:00', 'placeholder' => null,
    'defer' => false, 'presets' => true, 'start' => null, 'end' => null, 'startName' => null, 'endName' => null,
])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\DateInput;
    use TallUiKit\Support\FormControl;
    use Illuminate\Support\Str;

    $mode = in_array($mode, DateInput::MODES, true) ? $mode : 'date';
    $isRange = $mode === 'range';
    $valueMode = $isRange ? 'date' : $mode;

    // A range binds two properties: wire:model.start(.live)="from" and wire:model.end="to".
    $bind = [];
    foreach ($attributes->getAttributes() as $key => $path) {
        if (preg_match('/^wire:model\.(start|end)((?:\.[\w-]+)*)$/', $key, $m) && is_string($path)) {
            $bind[$m[1]] = ['name' => $path, 'live' => str_contains($m[2], '.live')];
        }
    }
    $attributes = $attributes->filter(fn ($v, $key) => ! preg_match('/^wire:model\.(start|end)(\.|$)/', $key));
    $bind = $isRange && isset($bind['start'], $bind['end']) ? $bind : null;

    $model = FormControl::model($attributes);
    $fieldName = $name ?? $model ?? ($bind['start']['name'] ?? null);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'date-'.Str::lower(Str::random(8));
    $message = FormControl::error($errors ?? null, $fieldName, $error)
        ?? ($bind ? (FormControl::error($errors ?? null, $bind['start']['name']) ?? FormControl::error($errors ?? null, $bind['end']['name'])) : null);
    $isInvalid = $invalid ?? (bool) $message;
    $isDisabled = (bool) $attributes->get('disabled', false);
    $isRequired = $required || (bool) $attributes->get('required', false);
    $inputName = $attributes->get('name', $name);

    // The bound Livewire property (read during the component's render), else `value` / start and end.
    $livewire = isset($__livewire) && is_object($__livewire) ? $__livewire : null;
    $read = function (?string $path, mixed $fallback) use ($livewire) {
        if ($path === null || str_starts_with($path, '$') || ! $livewire) {
            return $fallback;
        }
        try {
            return data_get($livewire, $path, $fallback);
        } catch (\Throwable) {
            return $fallback;
        }
    };
    $current = $read($model, $attributes->get('value'));

    if ($isRange) {
        $rangeStart = is_array($current) ? ($current['start'] ?? null) : $start;
        $rangeEnd = is_array($current) ? ($current['end'] ?? null) : $end;
        if ($bind) {
            $rangeStart = $read($bind['start']['name'], $rangeStart);
            $rangeEnd = $read($bind['end']['name'], $rangeEnd);
        }
        $value = ['start' => DateInput::normalize('date', $rangeStart), 'end' => DateInput::normalize('date', $rangeEnd)];
        $label = DateInput::displayRange($value['start'], $value['end']);
    } else {
        $value = DateInput::normalize($mode, $current);
        $label = DateInput::display($mode, $value, (int) $hours);
    }

    $minuteStep ??= in_array($mode, ['time', 'datetime'], true) && is_numeric($step) && (int) $step >= 60 ? max(1, intdiv((int) $step, 60)) : 5;
    $config = array_filter([
        'value' => $value,
        'disabledDates' => is_string($disabledDates) || is_array($disabledDates) ? (is_array($disabledDates) ? array_values(array_map(fn ($d) => is_string($d) && strlen($d) === 3 ? strtolower($d) : (DateInput::normalize('date', $d) ?: strtolower((string) $d)), $disabledDates)) : $disabledDates) : null,
        'weekStart' => is_numeric($weekStart) ? ((int) $weekStart) % 7 : null,
        'hours' => (int) $hours === 12 ? 12 : null,
        'minuteStep' => (int) $minuteStep !== 5 ? (int) $minuteStep : null,
        'defaultTime' => $defaultTime !== '09:00' ? DateInput::normalize('time', $defaultTime) : null,
        'presets' => $isRange ? DateInput::presets($presets) : null,
        'bind' => $bind,
    ], fn ($v) => $v !== null && $v !== '' && $v !== []);
    // The value is not in x-data: a morph that changed x-data would start the component again (and close an open popup).
    unset($config['value']);
    $minValue = DateInput::normalize($valueMode, $min);
    $maxValue = DateInput::normalize($valueMode, $max);

    $noun = ['date' => 'date', 'datetime' => 'date and time', 'time' => 'time', 'month' => 'month', 'week' => 'week', 'range' => 'dates'][$mode];
    $placeholder ??= [
        'date' => 'DD/MM/YYYY', 'datetime' => 'DD/MM/YYYY, HH:MM', 'time' => (int) $hours === 12 ? 'HH:MM am' : 'HH:MM',
        'month' => 'MM/YYYY', 'week' => 'YYYY-Www', 'range' => 'DD/MM/YYYY – DD/MM/YYYY',
    ][$mode];

    $widthClass = in_array($width, ['full', 'lg', 'md', 'sm', 'xs'], true)
        ? FormControl::width($width)
        : ($isRange ? 'w-64' : FormControl::width(null, $mode === 'datetime' ? 'datetime' : 'date'));

    $rootAttributes = $attributes->filter(fn ($v, $key) => in_array($key, ['class', 'style', 'wire:key', 'x-show', 'x-cloak'], true)
        || str_starts_with($key, 'wire:model') || str_starts_with($key, 'wire:island') || $key === 'x-model' || str_starts_with($key, 'x-model.'));
    $textAttributes = $attributes->filter(fn ($v, $key) => ! in_array($key, ['class', 'style', 'wire:key', 'x-show', 'x-cloak', 'id', 'name', 'value', 'type', 'aria-describedby'], true)
        && ! str_starts_with($key, 'wire:model') && ! str_starts_with($key, 'wire:island') && $key !== 'x-model' && ! str_starts_with($key, 'x-model.'));
    $describedBy = implode(' ', array_unique(array_filter(explode(' ', FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message).' '.$attributes->get('aria-describedby'))))) ?: null;
    // On the root: the script's config (x-data never holds the value), the value, min and max as data attributes.
    $state = array_filter([
        'x-data' => 'uiDatePicker('.($config ? \Illuminate\Support\Js::from($config) : '').')',
        'data-value' => $isRange ? json_encode($value) : $value,
        'x-modelable' => $bind ? null : 'value',
        'data-mode' => $mode,
        'data-min' => $minValue,
        'data-max' => $maxValue,
    ], fn ($v) => $v !== null && $v !== '');

    // A wire:model binding carries the value; hidden inputs are for plain form posts (and x-model).
    $wired = $bind || collect(array_keys($attributes->getAttributes()))->contains(fn ($key) => str_starts_with($key, 'wire:model'));
    $hidden = $wired ? [] : ($isRange
        ? (filled($inputName) || $startName || $endName ? ['start' => $startName ?? $inputName.'[start]', 'end' => $endName ?? $inputName.'[end]'] : [])
        : (filled($inputName) ? ['' => $inputName] : []));
@endphp

{{-- Kept small (a Livewire island re-renders it): looks live in the kit CSS (.ui-date-*), listeners are added by datepicker.js. --}}
<div {{ $rootAttributes->class(['relative max-w-full', $widthClass])->merge($state) }} data-ui-date{{ $defer ? ' data-ui-defer' : '' }}>
<div x-ref="box" @class(['ui-date-box', 'ui-date-sm' => $size === 'sm', 'ui-date-invalid' => $isInvalid]) data-control-box>
<input type="text" {{ $textAttributes->merge([
    'id' => $controlId,
    'value' => $label,
    'placeholder' => $placeholder,
    'autocomplete' => 'off',
    'spellcheck' => 'false',
    'aria-invalid' => $isInvalid ? 'true' : null,
    'aria-required' => $isRequired ? 'true' : null,
    'aria-describedby' => $describedBy,
    'data-ui' => 'date',
])->class(['ui-date-text']) }} x-ref="text">
<button type="button" class="ui-date-btn" x-ref="button" aria-label="Choose {{ $noun }}" aria-haspopup="dialog" aria-controls="{{ $controlId }}-popup" x-bind:aria-expanded="open" @disabled($isDisabled)><span class="ui-date-icon"></span></button>
</div>
@foreach ($hidden as $key => $hiddenName)
<input type="hidden" name="{{ $hiddenName }}" value="{{ $isRange ? $value[$key] : $value }}" @disabled($isDisabled) data-date-value="{{ $key }}">
@endforeach
<span class="sr-only" aria-live="polite" x-ref="status"></span>
<div x-ref="popup" id="{{ $controlId }}-popup" class="ui-date-pop" wire:ignore x-show="open" x-cloak x-anchor.bottom-start.noflip.offset.6.fixed="$refs.box" role="dialog" aria-modal="true" aria-label="Choose {{ $noun }}"></div>
</div>
