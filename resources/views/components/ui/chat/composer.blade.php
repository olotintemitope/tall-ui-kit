{{--
    The message box of x-ui.chat, also used alone (a comment reply, a support form). A
    textarea that grows with its text up to 6 rows (then scrolls), an optional Attach
    button and Send. Enter sends and Shift+Enter adds a line (`enterSends` false: Enter
    adds a line and Ctrl or Cmd+Enter sends); typing with an input method (IME) never
    sends early. Control scale: the textarea, Attach and Send are 36px tall.

    One Livewire call per message: `action` is called with any `params`, the trimmed text
    and an idempotency key, e.g. send('Hello', '4f1c...'). The key is made once per draft
    and kept until the server accepts it, so a double click, a double Enter or a retry
    after a failed request can never post twice: Send is blocked while the call runs, and
    the server can ignore a key it has seen. The draft clears only after success (the
    method returns anything but false); on failure it stays with "Not sent. Try again."
    Without `action` it dispatches a bubbling `ui-chat-send` event, detail { body, key,
    params }, and clears. After a send, a bubbling `ui-composer-sent` event follows.

    Attach (`attach`, a Livewire property for wire:model uploads) opens the file picker
    and shows x-ui.upload-progress.list under the box; the app reads the property in its
    send method. `maxlength` adds a counter ("120 / 2,000", danger text in the last 10%).
    Disabled: the textarea is disabled (no opacity: the kit's dashed look) and
    `disabledReason` replaces the hint. Escape dispatches a bubbling `ui-composer-cancel`.

    Props: action, params (list), label ("Message": the textarea's name), placeholder
    ("Write a message"), hint (the keyboard hint), enterSends (true), maxlength, attach,
    accept, multiple (true), disabled (false), disabledReason, submitLabel ("Send"), size
    (sm | md | lg: the control scale, md 36px), id, autofocus (false), replySlot (false:
    x-ui.comment-thread's reply box, which takes its parent key from the closest
    [data-reply-slot] instead of `params`).
    Mentions: `mentions` (list of ['key' => ..., 'name' => ...]: the people who can be
    mentioned) turns the textarea into a combobox. Typing "@" opens a list below the box
    (above it only when the composer is docked at the foot of a chat that clips it and
    has no room below: the one exception to the kit's popup rule); typing narrows it; Up and Down move, Enter or Tab inserts
    "@Name ", Escape closes the list and keeps the draft. A bubbling
    `ui-composer-mention` event, detail { key, name, params }, follows each pick.
    `mentionsLabel` ("People") names the list. A person may carry an `avatar` URL (else
    their initial is drawn). The picked people's keys still in the text are passed to
    `action` after the idempotency key: send(body, key, ['u1', 'u2']) (and in the
    `ui-chat-send` detail as `mentions`).
    `mentionSearch` (a Livewire method taking the typed query and returning the same
    list shape) finds people beyond the loaded list, 300ms after typing stops; it also
    turns mentions on when `mentions` starts empty.
    Tools can call `insert(text)` (adds text at the caret: an emoji picker) and
    `startMention()` (an "@" button).
    Typing: `typingAction` (a Livewire method) is called with `params` while the person
    types, at most once every 3 seconds, so others can see "is typing".
    With tools, a composer narrower than 28rem (a phone, a side panel) puts the text box
    on its own row and the buttons under it, Send on the right, so the box never squeezes.
    Slots: `tools` (icon buttons beside Attach: x-ui.button size="icon", outline or
    ghost, each with an aria-label) and `context` (a line above the box, e.g. "Replying
    to Ada" with a cancel button).
--}}
@props([
    'action' => null, 'params' => [], 'label' => 'Message', 'placeholder' => 'Write a message', 'hint' => null,
    'enterSends' => true, 'maxlength' => null, 'attach' => null, 'accept' => null, 'multiple' => true,
    'disabled' => false, 'disabledReason' => null, 'submitLabel' => 'Send', 'size' => 'md', 'id' => null, 'autofocus' => false,
    'replySlot' => false, 'mentions' => [], 'mentionsLabel' => 'People', 'typingAction' => null, 'mentionSearch' => null,
])

@php
    $id ??= 'composer-'.substr(md5($label.($action ?? '').json_encode($params)), 0, 6);
    $hint ??= $enterSends ? 'Enter to send, Shift+Enter for a new line.' : 'Ctrl+Enter or Cmd+Enter to send.';
    $size = in_array($size, ['sm', 'md', 'lg', 'default'], true) ? ($size === 'default' ? 'md' : $size) : 'md';
    $icon = ['sm' => 'icon-sm', 'md' => 'icon', 'lg' => 'icon-lg'][$size];
    $button = ['sm' => 'sm', 'md' => 'default', 'lg' => 'lg'][$size];
    $options = [
        'action' => $action,
        'params' => array_values((array) $params),
        'enterSends' => (bool) $enterSends,
        'maxlength' => $maxlength ? (int) $maxlength : null,
        'disabled' => (bool) $disabled,
        'files' => (bool) $attach,
        'replySlot' => (bool) $replySlot,
        'mentions' => array_values(array_map(fn ($person) => ['key' => (string) ($person['key'] ?? ''), 'name' => (string) ($person['name'] ?? ''), 'avatar' => isset($person['avatar']) && $person['avatar'] !== '' ? (string) $person['avatar'] : null], array_filter((array) $mentions, fn ($person) => is_array($person) && ($person['name'] ?? '') !== ''))),
        'typingAction' => $typingAction,
        'mentionSearch' => $mentionSearch,
    ];
    $hasMentions = $options['mentions'] !== [] || $mentionSearch;
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<form x-data="uiComposer(@js($options))" x-on:submit.prevent="send()" novalidate {{ $attributes->class(['ui-composer @container relative min-w-0']) }} id="{{ $id }}" data-composer > @isset($context) <div class="mb-2 flex min-w-0 items-center justify-between gap-2 type-meta" data-composer-context>{{ $context }}</div> @endisset <label for="{{ $id }}-input" class="sr-only">{{ $label }}</label> <div @class(['flex items-end gap-2', 'flex-wrap' => isset($tools)]) data-composer-row> @if ($attach) <input type="file" id="{{ $id }}-files" class="sr-only" tabindex="-1" aria-hidden="true" wire:model="{{ $attach }}" @if ($multiple) multiple @endif @if ($accept) accept="{{ $accept }}" @endif x-ref="files" data-composer-files> <x-ui.button variant="outline" :size="$icon" type="button" aria-label="Attach files" :disabled="$disabled" x-on:click="$refs.files.click()" data-composer-attach><x-ui.icon name="paperclip" /></x-ui.button> @endif @isset($tools) <div class="flex shrink-0 items-end gap-2" data-composer-tools>{{ $tools }}</div> @endisset <textarea x-ref="input" id="{{ $id }}-input" name="body" rows="1" x-model="body" x-on:keydown="keydown($event)" x-on:input="grow(); detect(); typing()" @if ($hasMentions) x-on:click="detect()" x-on:blur="mention = null" role="combobox" aria-autocomplete="list" aria-controls="{{ $id }}-mentions" aria-expanded="false" x-bind:aria-expanded="mentionOpen ? 'true' : 'false'" x-bind:aria-activedescendant="activeId" @endif @if ($maxlength) maxlength="{{ (int) $maxlength }}" @endif placeholder="{{ $placeholder }}" aria-describedby="{{ $id }}-hint @if ($maxlength) {{ $id }}-count @endif" x-bind:aria-invalid="failed ? 'true' : null" @disabled($disabled) @if ($autofocus) autofocus @endif @class(['ui-composer-input', '@max-md:order-first @max-md:basis-full' => isset($tools)]) data-size="{{ $size }}" data-composer-input ></textarea> <x-ui.button type="submit" :size="$button" :class="isset($tools) ? '@max-md:ms-auto' : null" :disabled="$disabled" x-bind:aria-disabled="canSend ? 'false' : 'true'" x-bind:aria-busy="sending ? 'true' : null" data-composer-send>{{ $submitLabel }}</x-ui.button> </div> <div class="mt-1 flex flex-wrap items-baseline justify-between gap-x-3"> <p id="{{ $id }}-hint" class="type-caption" x-bind:class="failed && 'text-danger-text'" x-text="failed ? 'Not sent. Try again.' : @js($disabled && $disabledReason ? $disabledReason : $hint)">{{ $disabled && $disabledReason ? $disabledReason : $hint }}</p> @if ($maxlength) <p id="{{ $id }}-count" class="type-caption tabular-nums" x-bind:class="nearLimit && 'text-danger-text'" x-text="counter" data-composer-count>0 / {{ number_format((int) $maxlength) }}</p> @endif </div> @if ($hasMentions) <ul id="{{ $id }}-mentions" role="listbox" aria-label="{{ $mentionsLabel }}" x-show="mentionOpen" x-cloak x-effect="mentionOpen && placeMentions($el)" x-bind:class="mentionAbove ? 'bottom-full mb-1.5' : 'top-full mt-1.5'" x-bind:data-placement="mentionAbove ? 'above' : 'below'" class="absolute inset-x-0 z-40 max-h-64 overflow-y-auto rounded-lg border border-panel-border bg-popover p-1 shadow-popover" data-composer-mentions> <template x-for="(person, index) in suggestions" :key="person.key"> <li role="option" x-bind:id="`${$root.id}-mention-${index}`" x-bind:aria-selected="index === active ? 'true' : 'false'" x-on:mousedown.prevent x-on:click="pickMention(index)" x-on:mousemove="active = index" x-bind:class="index === active && 'bg-surface-subtle'" class="flex min-h-8 cursor-pointer items-center gap-2 rounded-md px-2 py-1"> <img x-show="person.avatar" x-bind:src="person.avatar" alt="" class="size-6 shrink-0 rounded-full object-cover"> <span x-show="! person.avatar" class="inline-flex size-6 shrink-0 items-center justify-center rounded-full bg-surface-muted type-caption text-ink" x-text="person.name.slice(0, 1).toUpperCase()" aria-hidden="true"></span> <span class="min-w-0 type-body text-ink wrap-anywhere" x-text="person.name"></span></li> </template> </ul> <span class="sr-only" aria-live="polite" x-text="mentionStatus" data-composer-mention-status></span> @endif @if ($attach) <x-ui.upload-progress.list :for="$attach" :multiple="$multiple" label="Attachments" class="mt-2" /> @endif </form>
