{{--
    Custom single or multiple select: the WAI-ARIA APG "select-only combobox". No native
    <select> anywhere in the app (PopupPlacementTest guards it). Behaviour lives in
    resources/js/select.js (`uiSelect`); docs/ui-kit.md "x-ui.select" has the keyboard map.

    Markup: a <button role="combobox" aria-haspopup="listbox"> trigger showing the chosen
    label (it wraps, never cut), a hidden input per value for plain form posts, and a
    popup anchored below the trigger (x-anchor.bottom-start.noflip, fixed, so tables,
    dialogs and sheets never clip it) holding a role="listbox". Options are rendered here
    once, as bare role="option" elements inside x-ignore (no Alpine per option); the
    listbox styles them (app.css `.ui-select-list`). Focus stays on the trigger and the
    active option is announced through aria-activedescendant.

    Props: options (value => label, or a list of ['value' => , 'label' => , 'disabled' => ];
    a string key whose value is an array of value => label is a group), placeholder (an
    empty-value first option), selected (initial value without wire:model; a list with
    `multiple`), size (sm 32px | default 36px, `md` is an alias | lg 40px), width (auto, the default: as wide as the
    longest label plus the chevron, at least 6rem, never wider than its container; or
    full|lg|md|sm|xs), invalid, multiple (a listbox with checkmarks; the trigger lists the
    chosen labels and wraps), rows (kept for the old API: the list scrolls at 18rem).
    <option>s in the slot are parsed into options.
    Binding: wire:model (any modifier) or x-model on the component; .live fires when a
    value is chosen, never while arrowing through the list. wire:island (the island the
    update re-renders) goes on the root with wire:model. Inside a Livewire view the
    bound property's current value is read on the server, so the first paint and every
    morph show the right label. Wiring (id, label, hint, error, aria-describedby) as
    x-ui.input; other attributes (aria-label, data-*, x-on:*, disabled) go to the trigger.
--}}
@props(['options' => [], 'placeholder' => null, 'selected' => null, 'size' => 'default', 'invalid' => null, 'width' => null, 'multiple' => false, 'rows' => 5])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\FormControl;
    use Illuminate\Support\Str;

    $model = FormControl::model($attributes);
    $fieldName = $name ?? $model;
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'select-'.Str::lower(Str::random(8));
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $isDisabled = (bool) $attributes->get('disabled', false);
    $nativeRequired = (bool) $attributes->get('required', false);
    $inputName = $attributes->get('name', $name);

    $option = fn ($value, $label, $disabled = false) => ['value' => (string) $value, 'label' => (string) $label, 'disabled' => (bool) $disabled];
    $normalised = collect($options)->map(fn ($item, $key) => is_array($item) && array_key_exists('value', $item)
        ? $option($item['value'], $item['label'] ?? $item['value'], $item['disabled'] ?? false)
        : (is_array($item)
            ? ['group' => (string) $key, 'options' => collect($item)->map(fn ($child, $value) => is_array($child) ? $option($child['value'], $child['label'] ?? $child['value'], $child['disabled'] ?? false) : $option($value, $child))->values()->all()]
            : $option($key, $item)))->values();

    // <option>s passed in the slot (the old native API).
    if (trim((string) $slot) !== '') {
        preg_match_all('/<option\b([^>]*)>(.*?)<\/option>/si', (string) $slot, $found, PREG_SET_ORDER);
        foreach ($found as [, $attrs, $text]) {
            preg_match('/\bvalue\s*=\s*(["\'])(.*?)\1/si', $attrs, $v);
            $label = html_entity_decode(trim(strip_tags($text)), ENT_QUOTES | ENT_HTML5);
            $normalised->push($option(isset($v[2]) ? html_entity_decode($v[2], ENT_QUOTES | ENT_HTML5) : $label, $label, (bool) preg_match('/\bdisabled\b/i', $attrs)));
            if (preg_match('/\bselected\b/i', $attrs) && $selected === null) {
                $selected = isset($v[2]) ? html_entity_decode($v[2], ENT_QUOTES | ENT_HTML5) : $label;
            }
        }
    }

    $flat = $normalised->flatMap(fn ($o) => isset($o['group']) ? $o['options'] : [$o])->values();

    // The bound Livewire property (read during the component's render), else `selected`.
    $current = $selected;
    if ($model !== null && ! str_starts_with($model, '$') && isset($__livewire) && is_object($__livewire)) {
        try {
            $current = data_get($__livewire, $model, $selected);
        } catch (\Throwable) {
            $current = $selected;
        }
    }
    $chosen = collect(is_array($current) ? $current : ($current === null ? [] : [$current]))->map(fn ($v) => (string) $v)->values()->all();
    if (! $multiple) {
        $chosen = array_slice($chosen, 0, 1);
        // As a native select: nothing matches and there is no placeholder, so the first option shows.
        $matches = $flat->contains(fn ($o) => in_array($o['value'], $chosen, true));
        if (! $matches && $placeholder === null) {
            $first = $flat->first(fn ($o) => ! $o['disabled']) ?? $flat->first();
            $chosen = $first ? [$first['value']] : [];
        } elseif (! $matches) {
            $chosen = [''];
        }
    }
    $chosenLabels = $flat->filter(fn ($o) => in_array($o['value'], $chosen, true))->pluck('label');
    $label = $multiple
        ? ($chosenLabels->implode(', ') ?: ($placeholder ?? 'None selected'))
        : ($chosenLabels->first() ?? $placeholder ?? '');

    $auto = ! $multiple && (blank($width) || $width === 'auto' || ! in_array($width, ['full', 'lg', 'md', 'sm', 'xs'], true));
    $longest = collect([$placeholder, ...$flat->pluck('label')])->filter(fn ($l) => filled($l))->sortByDesc(fn ($l) => mb_strlen((string) $l))->first();

    // wire:island sits beside wire:model on the root: Livewire reads it from the element that made the update.
    $rootAttributes = $attributes->filter(fn ($v, $key) => in_array($key, ['class', 'style', 'wire:key', 'x-show', 'x-cloak'], true)
        || str_starts_with($key, 'wire:model') || str_starts_with($key, 'wire:island') || $key === 'x-model' || str_starts_with($key, 'x-model.'));
    $triggerAttributes = $attributes->filter(fn ($v, $key) => ! in_array($key, ['class', 'style', 'wire:key', 'x-show', 'x-cloak', 'id', 'name', 'required', 'aria-describedby'], true)
        && ! str_starts_with($key, 'wire:model') && ! str_starts_with($key, 'wire:island') && $key !== 'x-model' && ! str_starts_with($key, 'x-model.'));
    $describedBy = implode(' ', array_unique(array_filter(explode(' ', FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message).' '.$attributes->get('aria-describedby'))))) ?: null;
    $listLabel = $attributes->get('aria-label');
    $initial = $multiple ? $chosen : ($chosen[0] ?? '');
    // Kept small (a country list is 250 of these): select.js adds ids and aria-selected="false" on first open.
    $optionHtml = fn ($o) => '<div role="option"'
        .($o['value'] !== $o['label'] ? ' data-value="'.e($o['value']).'"' : '')
        .(in_array($o['value'], $chosen, true) ? ' aria-selected="true"' : '')
        .($o['disabled'] ? ' aria-disabled="true"' : '').'>'.e($o['label']).'</div>';
@endphp

<div {{ $rootAttributes->class([
    'group/select relative',
    'inline-grid min-w-24 max-w-full align-middle' => $auto,
    'grid' => ! $auto,
    FormControl::width($width, 'sm') => ! $auto,
]) }} x-data="uiSelect(@js($initial), @js((bool) $multiple))" x-modelable="value" data-ui-select @if ($multiple) data-multiple @endif @if ($auto) data-select-auto @endif>
    @if ($auto && $longest)
        <span class="invisible [grid-area:1/1] h-0 overflow-hidden whitespace-pre pl-3 pr-9 font-body text-body" aria-hidden="true" data-select-sizer>{{ $longest }}</span>
    @endif
    <button type="button" {{ $triggerAttributes->merge([
        'id' => $controlId,
        'role' => 'combobox',
        'aria-haspopup' => 'listbox',
        'aria-expanded' => 'false',
        'aria-controls' => $controlId.'-listbox',
        'aria-invalid' => $isInvalid ? 'true' : null,
        'aria-required' => ($required || $nativeRequired) ? 'true' : null,
        'aria-describedby' => $describedBy,
        'data-ui' => 'select',
    ])->class([
        'relative [grid-area:1/1] flex w-full items-center rounded-md border bg-surface pl-3 pr-9 text-left font-body text-body text-ink outline-none transition-[border-color,box-shadow] duration-100 cursor-default',
        FormControl::height($size, true),
        FormControl::size($size) === 'sm' ? 'py-1' : (FormControl::size($size) === 'lg' ? 'py-2' : 'py-1.5'),
        'disabled:cursor-not-allowed disabled:border-dashed disabled:bg-surface-subtle disabled:text-ink-muted',
        'border-danger focus:ring-2 focus:ring-danger/20' => $isInvalid,
        'border-control enabled:hover:border-control-hover focus:border-focus focus:ring-2 focus:ring-primary/20 aria-expanded:border-focus' => ! $isInvalid,
    ]) }} x-ref="trigger" x-bind:aria-expanded="open ? 'true' : 'false'" x-bind:aria-activedescendant="activeId" x-on:click="toggle()" x-on:keydown="key($event)" x-on:keyup.space.prevent x-on:blur="blurred($event)">
        <span class="min-w-0 flex-1 wrap-anywhere" data-select-label @if ($multiple) data-none="{{ $placeholder ?? 'None selected' }}" @endif x-text="text">{{ $label }}</span>
        <x-ui.icon name="chevron-down" class="pointer-events-none absolute right-2.5 top-1/2 size-4 -translate-y-1/2 text-ink-muted group-data-[loading]/select:hidden" />
        {{-- Loading (Livewire marks the component data-loading while its request runs): a 12px ring replaces the chevron. --}}
        <span class="pointer-events-none absolute right-3 top-[calc(50%-6px)] hidden size-3 animate-spin rounded-full border border-ink-muted/30 border-t-ink motion-reduce:animate-pulse group-data-[loading]/select:block" aria-hidden="true"></span>
    </button>
    @if (filled($inputName) && ! $multiple && $nativeRequired)
        {{-- A text input, so the browser's required check runs; select.js moves focus to the trigger when it fails. --}}
        <input type="text" name="{{ $inputName }}" value="{{ $initial }}" required tabindex="-1" aria-hidden="true" @disabled($isDisabled) class="pointer-events-none absolute bottom-0 left-3 size-px opacity-0" x-on:invalid.prevent="invalidated()" data-select-input>
    @elseif (filled($inputName))
        <span hidden data-select-inputs data-name="{{ $inputName }}">@foreach ($multiple ? $chosen : [$initial] as $value)<input type="hidden" name="{{ $inputName }}" value="{{ $value }}" @disabled($isDisabled) data-select-input>@endforeach</span>
    @endif
    <div x-ref="popup" wire:ignore.self x-show="open" x-cloak x-anchor.bottom-start.noflip.offset.6.fixed="$refs.trigger" x-on:mousedown.prevent x-on:click="pick($event)" x-on:mousemove="hover($event)" class="z-50 w-max min-w-24 max-w-[min(28rem,calc(100vw-1rem))] rounded-lg border border-line bg-popover text-popover-foreground shadow-popover" data-select-popup>
        <div role="listbox" id="{{ $controlId }}-listbox" @if ($listLabel) aria-label="{{ $listLabel }}" @else aria-labelledby="{{ $controlId }}-label" @endif @if ($multiple) aria-multiselectable="true" @endif x-ignore class="ui-select-list max-h-72 overflow-y-auto overscroll-contain p-1 font-body text-body">@if ($placeholder !== null && ! $multiple){!! $optionHtml(['value' => '', 'label' => $placeholder, 'disabled' => false]) !!}@endif
            @foreach ($normalised as $item)@if (isset($item['group']))<div role="group" aria-labelledby="{{ $controlId }}-g{{ $loop->index }}"><div role="presentation" id="{{ $controlId }}-g{{ $loop->index }}" class="type-label px-2 pb-1 pt-2">{{ $item['group'] }}</div>@foreach ($item['options'] as $child){!! $optionHtml($child) !!}@endforeach</div>@else{!! $optionHtml($item) !!}@endif @endforeach
        </div>
    </div>
</div>
