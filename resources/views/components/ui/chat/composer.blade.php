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
--}}
@props([
    'action' => null, 'params' => [], 'label' => 'Message', 'placeholder' => 'Write a message', 'hint' => null,
    'enterSends' => true, 'maxlength' => null, 'attach' => null, 'accept' => null, 'multiple' => true,
    'disabled' => false, 'disabledReason' => null, 'submitLabel' => 'Send', 'size' => 'md', 'id' => null, 'autofocus' => false,
    'replySlot' => false,
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
    ];
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<form x-data="uiComposer(@js($options))" x-on:submit.prevent="send()" novalidate {{ $attributes->class(['ui-composer min-w-0']) }} id="{{ $id }}" data-composer > <label for="{{ $id }}-input" class="sr-only">{{ $label }}</label> <div class="flex items-end gap-2"> @if ($attach) <input type="file" id="{{ $id }}-files" class="sr-only" tabindex="-1" aria-hidden="true" wire:model="{{ $attach }}" @if ($multiple) multiple @endif @if ($accept) accept="{{ $accept }}" @endif x-ref="files" data-composer-files> <x-ui.button variant="outline" :size="$icon" type="button" aria-label="Attach files" :disabled="$disabled" x-on:click="$refs.files.click()" data-composer-attach><x-ui.icon name="paperclip" /></x-ui.button> @endif <textarea x-ref="input" id="{{ $id }}-input" name="body" rows="1" x-model="body" x-on:keydown="keydown($event)" x-on:input="grow()" @if ($maxlength) maxlength="{{ (int) $maxlength }}" @endif placeholder="{{ $placeholder }}" aria-describedby="{{ $id }}-hint @if ($maxlength) {{ $id }}-count @endif" x-bind:aria-invalid="failed ? 'true' : null" @disabled($disabled) @if ($autofocus) autofocus @endif class="ui-composer-input" data-size="{{ $size }}" data-composer-input ></textarea> <x-ui.button type="submit" :size="$button" :disabled="$disabled" x-bind:aria-disabled="canSend ? 'false' : 'true'" x-bind:aria-busy="sending ? 'true' : null" data-composer-send>{{ $submitLabel }}</x-ui.button> </div> <div class="mt-1 flex flex-wrap items-baseline justify-between gap-x-3"> <p id="{{ $id }}-hint" class="type-caption" x-bind:class="failed && 'text-danger-text'" x-text="failed ? 'Not sent. Try again.' : @js($disabled && $disabledReason ? $disabledReason : $hint)">{{ $disabled && $disabledReason ? $disabledReason : $hint }}</p> @if ($maxlength) <p id="{{ $id }}-count" class="type-caption tabular-nums" x-bind:class="nearLimit && 'text-danger-text'" x-text="counter" data-composer-count>0 / {{ number_format((int) $maxlength) }}</p> @endif </div> @if ($attach) <x-ui.upload-progress.list :for="$attach" :multiple="$multiple" label="Attachments" class="mt-2" /> @endif </form>
