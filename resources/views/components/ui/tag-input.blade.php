{{--
    Tag input (docs/components/workflow.md, "Tag input"): several short text values in one
    field. Bind an array with wire:model / x-model on the component (one round trip per
    change with .live, none with plain wire:model). Plain forms: `name` posts name[].

    Adding: type and press Enter or the separator (","), paste "a, b, c" (or one per
    line: each becomes a tag), pick a suggestion, or leave the field (`addOnBlur`).
    Removing: each tag's x button ("Remove <tag>"), Backspace in the empty input (the
    last tag), or Backspace / Delete on a tag. Keyboard between tags: Left at the start
    of the input moves to the last tag, Left and Right move between tags, Home and End to
    the ends, Right after the last tag or Escape goes back to the input. Focus never
    falls to <body>: after a removal it moves to the previous tag, else the next, else
    the input.

    Checked before a tag is added (and said in a polite message under the field):
    empty, too long (`maxLength`), `pattern` (a JS regex source, matched whole like the
    HTML pattern attribute; `patternMessage` explains it), a duplicate (case-insensitive
    unless `caseSensitive`), over `max`, or not a suggestion when `allowCustom` is false.
    The browser check is a convenience: validate the array again on the server.

    Suggestions (optional): the input becomes an APG combobox with a listbox below it
    (never flipped above; $revealBelow scrolls when there is no room). Typing filters,
    Down and Up move, Enter picks, Escape closes; tags already chosen are left out.

    Look: the x-ui.input box (1px `control` border, 6px radius, focus ring) holding the
    tags as `surface-muted` chips (6px radius, never pills) in body text; the count
    ("3 of 5") in caption below when `max` is set. Long tags wrap inside their chip.

    Props: value (initial, without a binding), suggestions (strings, or [value, hint]),
    max, maxLength (50), pattern, patternMessage, allowCustom (true), caseSensitive
    (false), separator (","), addOnBlur (true), placeholder ("Add a tag"), size, width
    (md), invalid, label (accessible name outside x-ui.field), name (hidden inputs),
    removeLabel ("Remove"), empty ("No matches"), id.
--}}
@props([
    'value' => [], 'suggestions' => [], 'max' => null, 'maxLength' => 50, 'pattern' => null, 'patternMessage' => null,
    'allowCustom' => true, 'caseSensitive' => false, 'separator' => ',', 'addOnBlur' => true, 'placeholder' => 'Add a tag',
    'size' => 'default', 'width' => null, 'invalid' => null, 'label' => null, 'removeLabel' => 'Remove', 'empty' => 'No matches',
])
@aware(['name' => null, 'id' => null, 'hint' => null, 'error' => null, 'required' => false, 'width' => null])

@php
    use TallUiKit\Support\FormControl;

    $fieldName = $name ?? FormControl::model($attributes);
    $controlId = FormControl::id($attributes->get('id', $id), $fieldName) ?? 'tags-'.substr(md5(json_encode([$label, $suggestions, $placeholder])), 0, 6);
    $message = FormControl::error($errors ?? null, $fieldName, $error);
    $isInvalid = $invalid ?? (bool) $message;
    $list = collect($suggestions)->map(fn ($s) => is_array($s) ? ['value' => (string) ($s['value'] ?? $s[0]), 'hint' => $s['hint'] ?? null] : ['value' => (string) $s, 'hint' => null])->values();
    $tags = array_values(array_map('strval', (array) $value));
    $hasList = $list->isNotEmpty();
    $describedBy = trim(FormControl::describedBy($controlId, filled($hint) && ! $message, (bool) $message).' '.$controlId.'-status'.($max ? ' '.$controlId.'-count' : ''));
    $options = [
        'suggestions' => $list, 'max' => $max === null ? null : (int) $max, 'maxLength' => (int) $maxLength,
        'pattern' => $pattern, 'patternMessage' => $patternMessage, 'allowCustom' => (bool) $allowCustom,
        'caseSensitive' => (bool) $caseSensitive, 'separator' => $separator, 'addOnBlur' => (bool) $addOnBlur, 'id' => $controlId,
    ];
@endphp

<div
    x-data="uiTagInput(@js($options))"
    data-value="{{ json_encode($tags) }}"
    x-modelable="tags"
    {{ $attributes->whereStartsWith(['wire:model', 'x-model']) }}
    x-on:focusout="blur($event)"
    {{ $attributes->only('class')->class(['relative flex flex-col gap-1', FormControl::width($width, 'md')]) }}
    data-tag-input
>
    <div @class([
        'flex flex-wrap items-center gap-1 rounded-md border bg-white px-1 py-1 transition-[border-color,box-shadow] duration-100',
        $size === 'sm' ? 'min-h-8' : 'min-h-9',
        'border-red-600 focus-within:ring-2 focus-within:ring-red-600/20' => $isInvalid,
        'border-control hover:border-control-hover focus-within:border-focus focus-within:ring-2 focus-within:ring-primary/20' => ! $isInvalid,
    ]) x-on:click.self="$refs.input.focus()" data-control-box>
        {{-- Server-rendered chips for the first paint; Alpine's list replaces them on init. --}}
        @if ($tags)
            <ul class="flex max-w-full flex-wrap gap-1" aria-hidden="true" data-tag-ssr>
                @foreach ($tags as $tag)
                    <li class="ui-tag"><span class="min-w-0 wrap-anywhere">{{ $tag }}</span><span class="ui-tag-remove"><x-ui.icon name="x" class="size-3.5" /></span></li>
                @endforeach
            </ul>
        @endif
        <ul class="flex max-w-full flex-wrap gap-1" x-show="tags.length" x-cloak aria-label="{{ $label ?? 'Tags' }}, selected" data-tag-list>
            <template x-for="(tag, index) in tags" :key="tag">
                <li class="ui-tag" x-bind:data-tag-index="index">
                    <span class="min-w-0 wrap-anywhere" x-text="tag"></span>
                    <button
                        type="button"
                        tabindex="-1"
                        class="ui-tag-remove focus-ring"
                        x-bind:aria-label="@js($removeLabel) + ' ' + tag"
                        x-on:click="remove(index, true)"
                        x-on:keydown="tagKey($event, index)"
                        data-tag-remove
                    ><x-ui.icon name="x" class="size-3.5" /></button>
                </li>
            </template>
        </ul>
        <input
            x-ref="input"
            type="text"
            id="{{ $controlId }}"
            @if ($hasList)
                role="combobox"
                aria-autocomplete="list"
                aria-controls="{{ $controlId }}-listbox"
                aria-expanded="false"
                x-bind:aria-expanded="open ? 'true' : 'false'"
                x-bind:aria-activedescendant="activeId"
            @endif
            autocomplete="off"
            spellcheck="false"
            enterkeyhint="enter"
            placeholder="{{ $placeholder }}"
            @if ($label) aria-label="{{ $label }}" @endif
            @if ($isInvalid) aria-invalid="true" @else x-bind:aria-invalid="problem ? 'true' : null" @endif
            @if ($required) aria-required="true" @endif
            aria-describedby="{{ $describedBy }}"
            x-model="query"
            x-on:input="typed()"
            x-on:keydown="key($event)"
            x-on:paste="paste($event)"
            x-on:click="show()"
            {{ $attributes->except(['class', 'id'])->whereDoesntStartWith(['wire:model', 'x-model']) }}
            @class(['h-7 min-w-28 flex-1 bg-transparent px-1.5 font-body text-body text-ink placeholder:text-ink-muted outline-none', 'h-6' => $size === 'sm'])
            data-ui="tag-input"
        />
    </div>

    @if ($hasList)
        <ul
            id="{{ $controlId }}-listbox"
            role="listbox"
            @if ($label) aria-label="{{ $label }} suggestions" @else aria-labelledby="{{ $controlId }}-label" @endif
            x-show="open"
            x-cloak
            x-effect="open && $revealBelow($el, $refs.input)"
            x-transition:enter="transition ease-motion-out duration-150"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-motion-out duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute inset-x-0 top-full z-40 mt-1.5 max-h-64 overflow-y-auto rounded-lg border border-panel-border bg-white p-1 shadow-popover"
            data-tag-listbox
        >
            <template x-for="(option, index) in results" :key="option.value">
                <li
                    role="option"
                    x-bind:id="optionId(index)"
                    aria-selected="false"
                    x-bind:aria-selected="index === active ? 'true' : 'false'"
                    x-on:mousedown.prevent
                    x-on:click="pick(index)"
                    x-on:mousemove="active = index"
                    x-bind:class="index === active && 'bg-surface-subtle'"
                    class="flex min-h-8 cursor-pointer flex-col justify-center rounded-md px-2 py-1"
                >
                    <span class="type-body text-ink wrap-anywhere" x-text="option.value"></span>
                    <span class="type-caption wrap-anywhere" x-show="option.hint" x-text="option.hint"></span>
                </li>
            </template>
            <li x-show="results.length === 0" class="type-meta px-2 py-1.5" role="presentation">{{ $empty }}</li>
        </ul>
    @endif

    <div class="flex items-start justify-between gap-3">
        <p id="{{ $controlId }}-status" class="type-caption min-w-0 wrap-anywhere" x-bind:class="problem && 'text-red-700!'" aria-live="polite" x-text="status" data-tag-status></p>
        @if ($max)
            <p id="{{ $controlId }}-count" class="type-caption shrink-0 whitespace-nowrap" x-text="tags.length + ' of ' + {{ (int) $max }}" data-tag-count>{{ count($tags) }} of {{ (int) $max }}</p>
        @endif
    </div>

    @if ($name && $attributes->whereStartsWith(['wire:model', 'x-model'])->isEmpty())
        <template x-for="tag in tags" :key="tag"><input type="hidden" name="{{ $fieldName }}[]" x-bind:value="tag"></template>
    @endif
</div>
