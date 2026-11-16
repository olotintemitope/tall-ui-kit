<?php

return [
    'intro' => 'A conversation: a scrolling list of messages over a composer. Pass `messages` as data (day separators and grouping are worked out for you) or write `x-ui.chat.day` and `x-ui.chat.message` rows in the slot. One controller per chat, none per message; the scrolling logic is a lazy chunk (`chat-core.js`, about 1.2 KB gzip).',
    'keyboard' => [
        ['Tab', 'Reaches the message list (it scrolls with the arrow keys and Page Up/Down), "Load older messages", the "N new messages" button and the composer.'],
        ['Enter', 'In the composer, sends (with `enterSends`, the default). With an input method open it never sends.'],
        ['Shift+Enter', 'Adds a line. With `enterSends` off, Enter adds a line and Ctrl or Cmd+Enter sends.'],
        ['Escape', 'In the composer, dispatches `ui-composer-cancel` (a comment reply closes).'],
    ],
    'a11y' => [
        'The list is `role="log"` named by `label`, but not live itself: a separate polite status region reads each message that arrives from someone else, once. History loaded at the top, the first render and your own sends are never announced again.',
        'Grouped messages hide the author and time on screen but keep them for screen readers ("Ada Obi, 10:42:").',
        'States are words: "Sending", "Not sent." with Retry, "Edited", "This message was deleted.", "Seen". Never an icon or colour alone.',
        'The typing line is its own status region; the dots are hidden from assistive tech and hold still under reduced motion.',
        'After "N new messages" jumps down, focus moves to the composer (or the list), never to the page.',
        'Message text is escaped first; URLs, @mentions and #hashtags become links afterwards (`TallUiKit\\Support\\RichText`), so user markup can never run.',
    ],
    'do' => [
        'Give every message a stable `key`.',
        'Make the send method idempotent: it receives an idempotency key with each draft; ignore a key you have already stored.',
        'Return `false` from the send method to keep the draft and show "Not sent. Try again."',
        'Use `olderAction` with `hasOlder` to page history from the server.',
    ],
    'dont' => [
        'Bind the composer with `wire:model`: it sends one call with the text, not a request per keystroke.',
        'Put an Alpine component on each message.',
        'Poll faster than every 30 seconds for new messages; prefer broadcasting.',
    ],
    'events' => [
        '`sendAction(text, key)` on the composer: one Livewire call per message; without it a bubbling `ui-chat-send` event, detail `{ body, key, params }`.',
        '`olderAction()`: loads earlier messages; without Livewire a `ui-chat-older` event.',
        '`retryAction(key)` and `reactAction(key, reaction)` on messages; without a retry action a `ui-chat-retry` event, detail `{ key }`.',
        '`ui-composer-sent` follows a successful send.',
    ],
];
