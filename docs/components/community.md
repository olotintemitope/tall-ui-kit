# Chat and community components

Conversations, an inbox, posts, reactions, comment threads, polls, people, channels and rankings. Every one takes its content as data, knows nothing about the app's models or routes, and talks to Livewire with one call per action (or a bubbling browser event when there is no Livewire method). Gallery: workbench `/chat` and `/community`, both themes, light and dark. Tests: `tests/Feature/CommunityComponentsTest.php`. Contrast: the "Chat and community" rows in `TallUiKit\Support\KitContrast` (`composer contrast`).

JavaScript: `resources/js/chat.js` (the composer, the thread, the inbox keys and a stub for the chat) and `resources/js/community.js` (a few delegated listeners), both imported by `index.js`. The heavy parts load lazily with `import()`: `chat-core.js` (1.2 KB gzip) when the first chat starts, `community-core.js` (2.1 KB gzip) when a page has a post, reaction row, poll or follow button. One controller per chat or thread, one listener for the page; never an Alpine component per message, post or row.

User text is always escaped first: `TallUiKit\Support\RichText::render($text, $mentionUrl, $hashtagUrl)` splits the text into URLs, `@mentions` and `#hashtags`, escapes every piece, and links only http(s) URLs (`rel="nofollow ugc noopener"`, underlined). Line breaks are kept with `whitespace-pre-wrap`. Nothing a user types can become markup.

Payloads: every workbench demo response stays under 40 KB (tested). Repeated controls use lean classes (`.ui-act`, `.ui-reaction`, `.ui-chat-bubble`, `.ui-composer-input`) and messages and comments are built without per-row Blade conditionals, because each one adds two morph markers to every Livewire response.

| Need | Use |
|---|---|
| A conversation with a composer | `x-ui.chat` (+ `.message`, `.day`, `.typing`, `.composer`) |
| A box to write a message, comment or reply | `x-ui.chat.composer` |
| A list of conversations | `x-ui.chat.inbox` |
| A feed item | `x-ui.post` |
| Replies under a post or record | `x-ui.comment-thread` |
| Reactions with counts and a picker | `x-ui.reactions` |
| A person in a list; the top of a profile | `x-ui.member-card`; `x-ui.profile-header` (both use `x-ui.follow-button`) |
| A question with options and results | `x-ui.poll` |
| Groups and channels | `x-ui.channel-list` |
| A ranking with movement | `x-ui.leaderboard` (on `x-ui.ranked-list`) |
| An activity log (who did what) | `x-ui.timeline`, not a chat |

## Chat

```blade
<x-ui.chat
    label="Conversation with Bola Ade"
    :messages="$messages"
    older-action="loadOlder"
    :has-older="$hasOlder"
    :typing="$typing"
    send-action="send"
    :maxlength="2000"
    retry-action="retry"
    react-action="react"
    framed
/>
```

```php
public function send(string $body, string $key): bool
{
    // The key is the same for every attempt at one draft: store it and ignore repeats.
    if (Message::where('idempotency_key', $key)->exists()) {
        return true;
    }
    // ... validate, create, broadcast ...
    return true; // false keeps the draft and shows "Not sent. Try again."
}
```

Each message: `key` (stable), `author`, `authorKey` (grouping; the author by default), `avatar`, `mine`, `time`, `body`, `status` (`sent`, `sending`, `failed`), `edited`, `deleted`, `attachments` (`type` image or file, `name`, `src` or `href`, `thumb`, `alt`, `meta`, `width`, `height`), `reactions` (as `x-ui.reactions`), `seen` (true or "Seen by Ada"; shown under the last sent message that has it).

| Prop | Meaning |
|---|---|
| `messages` | The list, oldest first; or write rows in the slot |
| `olderAction`, `hasOlder` | "Load older messages" at the top: one call; the reading position is kept |
| `typing` | Names for `x-ui.chat.typing` ("Ada is typing") |
| `sendAction`, `maxlength`, `attach` | The default composer; or a `composer` slot |
| `retryAction`, `reactAction` | Per message: called with the key (and the reaction) |
| `groupMinutes` | 5: one author's messages within this many minutes form a group |
| `framed`, `height` | A 1px border and 8px radius; the height classes (`h-[36rem] max-h-[85vh]`) |
| `label`, `empty`, `tz`, `now`, `id`, `mentionUrl`, `hashtagUrl` | Name, empty text, time zone, fixed "now", id, link patterns |

Behaviour: opens at the newest message; stays at the bottom while you are there; scrolled up, incoming messages count into a "3 new messages" button that jumps down (focus moves to the composer). Your own new message always scrolls down. Images open the chat's one image dialog (the `x-ui.lightbox` look).

Accessibility: the list is `role="log"` (named, focusable for keyboard scrolling) with `aria-live="off"`; a separate polite status region reads each incoming message once ("Ada Obi: Are we still on for 3pm?"). Loading history, the first render and your own sends are never announced. Grouped messages keep the author and time for screen readers. States are words. The typing line is its own status region; its dots fade with the motion tokens and hold still under reduced motion.

### Composer

`x-ui.chat.composer`: a textarea that grows to 6 rows, then scrolls; Enter sends and Shift+Enter adds a line (`enterSends` false: Ctrl or Cmd+Enter sends); an input method (IME) never sends early. `action` is called once with `params`, the text and an idempotency key; Send is blocked while it runs, so a double click or double Enter cannot post twice. `maxlength` adds a counter ("120 / 2,000", danger text in the last 10%); `attach` (a `wire:model` upload property) adds Attach and `x-ui.upload-progress.list`; `disabled` with `disabledReason`; `size` sm, md, lg (32, 36, 40px). Events: `ui-chat-send` (no action), `ui-composer-sent`, `ui-composer-cancel` (Escape).

Mentions: `mentions` (a list of `['key' => ..., 'name' => ...]`) makes the textarea a combobox. Typing "@" at the start of the text or after a space opens a list below the box (never above it); typing narrows it (name start first, then word start, then anywhere; at most 6); Up and Down move, Enter or Tab inserts "@Name ", Escape closes the list and keeps the draft, and an email address never opens it. Each pick dispatches a bubbling `ui-composer-mention`, detail `{ key, name, params }`, so the app can record who was mentioned. `mentionsLabel` ("People") names the list. `typingAction` (a Livewire method) is called with `params` while someone types, at most once every 3 seconds, to drive `x-ui.chat.typing` for the others.

Slots: `tools` holds extra icon buttons beside Attach (`x-ui.button size="icon"`, outline or ghost, each with an `aria-label`: emoji, record, insert code); `context` is one line above the box, such as "Replying to Ada" with a cancel button.

```blade
<x-ui.chat.composer action="send" typing-action="typing" :mentions="$members" mentions-label="Course members" id="room-composer">
    <x-slot:context>Replying to Ada Obi <x-ui.button variant="ghost" size="icon-sm" aria-label="Cancel reply" wire:click="cancelReply"><x-ui.icon name="x" /></x-ui.button></x-slot:context>
    <x-slot:tools><x-ui.button variant="ghost" size="icon" aria-label="Insert code" wire:click="insertCode"><x-ui.icon name="code" /></x-ui.button></x-slot:tools>
</x-ui.chat.composer>
```

## Inbox

`x-ui.chat.inbox`: `conversations` (`key`, `name`, `avatar`, `people` for a group, `preview`, `time`, `unread`, `active`, `href`, `muted`), `action` (when rows have no `href`), `navigate`, slots `search` and `filters`. One Tab stop; arrows, Home and End move. Previews wrap (never clipped or ellipsed); pass a short one. Unread is bold text plus an info pill read as "3 unread".

## Post

`x-ui.post`: `key`, `author`, `avatar`, `handle`, `verified`, `time`, `body`, `href` (permalink on the time), `likes`, `liked`, `comments`, `likeAction`, `commentHref` or `commentAction`, `shareUrl`, `mentionUrl`, `hashtagUrl`, `headingLevel`. Slots: default (under the body, e.g. reactions or a poll), `media`, `more` (`x-ui.dropdown.item` rows), `footer` (e.g. a comment thread). Like is optimistic: pressed and counted at once, then `likeAction(key, on)`; `false` or a failed request rolls back and shows a toast. Counts are compact ("1.2k"); the full number is spoken. Share uses the system share sheet, or copies the link with "Link copied."

## Comment thread

`x-ui.comment-thread`: `comments` (`key`, `author`, `avatar`, `time`, `body`, `edited`, `replies`, `more`), `depth` (3; deeper replies become "Continue thread" via `continueHref` with `:key`), `replyAction(parent, text, key)` (null parent for the "Add a comment" box), `moreAction(key)` ("Show 3 more replies"), `maxlength`. Timeline look: avatar markers on a 1px rail. One reply box for the thread, copied under the comment being answered (focus moves in; Escape or a sent reply returns focus to Reply). "Hide replies" collapses a thread (`aria-expanded`).

## Reactions

`x-ui.reactions`: `reactions` (`key`, `count`, `mine`), `options` (key => [symbol, label]; Like, Love, Funny, Wow, Sad, Celebrate by default), `action(subject, reaction)`, `subject`, `picker`, `size` (sm, md). Toggles are buttons named "Like 12" with `aria-pressed`. The picker is a Popover API panel, placed below its button (never flipped), arrow keys move, Escape returns focus. Optimistic with rollback.

## Member card and profile header

`x-ui.member-card` (a 1px bordered card) and `x-ui.profile-header` (flat, the page's heading at `level` 1): `key`, `name`, `avatar`, `role`, `handle`, `bio`, `verified`, `stats` (label and value; shown compact in an `x-ui.stat-strip`), `following`, `followAction(key, on)`, `messageHref` or `messageAction`. `x-ui.follow-button` reads "Follow" or "Following" (`aria-pressed`), optimistic with rollback. The avatar has an `xl` (48px) size for the header.

## Poll

`x-ui.poll`: `key`, `question`, `options` (`key`, `label`, `votes`), `multiple`, `voted` (the viewer's keys), `closed`, `endsAt`, `results` (`after-vote` or `always`), `action(key, choices)`. Results are `x-ui.progress` bars with "42%, 120 votes"; the viewer's choice reads "(your vote)"; "Poll ended" and the total. Voting with nothing chosen says "Choose an option."; after voting, focus moves to the results.

## Channel list

`x-ui.channel-list`: `sections` (`key`, `label`, `collapsible`, `open`, `channels`: `key`, `name`, `href`, `unread`, `mentions`, `active`, `private`, `muted`), `action`, `navigate`. The sidebar section look; a collapsed section says "2 unread". Unread is bold text and a dot (", unread" spoken); mentions are an info pill ("3 mentions").

## Leaderboard

`x-ui.leaderboard`: `rows` (`key`, `rank`, `label`, `sub`, `value`, `change` in places, `new`, `current`, `href`), `valueLabel` (spoken after values), `format` (number, compact, none). Change is an arrow plus words ("Up 2", "Down 1", "Same", "New"); your row has a `surface-muted` fill, "You" and `aria-current`. No medals, avatars or podiums.

## Do and don't

- Do make every send, reply, vote and follow idempotent on the server, and validate keys and choices there.
- Do return `false` from an action that could not be saved; the kit rolls the optimistic state back.
- Do page history and replies from the server (`olderAction`, `moreAction`, `continueHref`).
- Don't bind the composer with `wire:model` or poll faster than every 30 seconds; prefer broadcasting.
- Don't wrap posts or comments in cards, add shadows, or use emoji as decoration (reaction symbols are content with labels).
- Don't truncate previews or names; they wrap.
