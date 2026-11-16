<?php

return [
    'intro' => 'A list of conversations: avatar (or an avatar group for a group chat), name, last-message time, a preview that wraps (never cut), and an unread count. Search and filter slots sit above it.',
    'keyboard' => [
        ['Tab', 'The list is one Tab stop: the open conversation, or the first.'],
        ['Arrow Up / Down', 'Move between conversations.'],
        ['Home / End', 'First and last conversation.'],
        ['Enter', 'Opens the conversation (its link, or the Livewire `action`).'],
    ],
    'a11y' => [
        'A `<nav>` named by `label`; the open conversation has `aria-current`.',
        'Unread is bold text plus a count pill whose spoken text is "3 unread", never colour alone.',
        'Avatars are decorative: the name is always printed.',
    ],
    'do' => [
        'Pass a short preview: the kit wraps text and never clips it.',
        'Use `href` with `navigate` for conversation pages, or `action` for a single-page inbox.',
    ],
    'dont' => [
        'Truncate previews with an ellipsis or line clamp.',
        'Show the count pill when nothing is unread.',
    ],
    'events' => [
        '`action(key)` when a row has no `href`; otherwise a bubbling `ui-inbox-open` event, detail `{ key }`.',
    ],
];
