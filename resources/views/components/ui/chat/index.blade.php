{{--
    A conversation (docs/components/community.md, "Chat"): a scrolling list of messages
    over a composer. Data in, Livewire-friendly: pass `messages` as data (day separators
    and grouping are worked out here) or write x-ui.chat.day and x-ui.chat.message rows
    in the slot.

    Scrolling (resources/js/chat-core.js, loaded lazily, one controller per chat, none per
    message): it opens at the newest message and sticks to the bottom as messages arrive,
    unless the reader has scrolled up; then a "3 new messages" button appears over the
    list and jumps down (focus moves to the composer, or the list). Your own new message
    always scrolls down. `olderAction` adds "Load older messages" at the top: one
    Livewire call, and the reading position stays where it was when older messages are
    put above. Images in messages open one shared image dialog (the x-ui.lightbox look).

    Accessibility: the list is role="log", named by `label`, focusable so it scrolls by
    keyboard. It is not itself live (aria-live="off"): a separate polite status region
    speaks each message that arrives at the end from someone else ("Ada Obi: Are we still
    on for 3pm?"), so loading older messages, your own sends and the first render are
    never read out again. The typing line (x-ui.chat.typing) is its own status region.

    Props: messages (list of x-ui.chat.message data: key, author, authorKey (groups by it;
    the author by default), avatar, mine, time, body, status, edited, deleted, attachments,
    reactions, seen), label ("Messages"), olderAction (Livewire method), hasOlder (false),
    typing (list of names), sendAction (Livewire method: renders x-ui.chat.composer with
    it), maxlength (the default composer's limit), attach (the default composer's upload
    property), retryAction, reactAction, groupMinutes (5: consecutive messages from one
    author within this many minutes form a group), mentionUrl, hashtagUrl, framed (false:
    a 1px `line` border and 8px radius around the chat), height (classes, "h-[36rem]
    max-h-[85vh]"), tz (Africa/Lagos), now, empty ("No messages yet."), id.
    Slots: default (slot mode rows), header (above the list, e.g. the other person's
    name), composer (replaces the default composer).
--}}
@props([
    'messages' => null, 'label' => 'Messages', 'olderAction' => null, 'hasOlder' => false, 'typing' => [],
    'sendAction' => null, 'maxlength' => null, 'attach' => null, 'retryAction' => null, 'reactAction' => null,
    'groupMinutes' => 5, 'mentionUrl' => null, 'hashtagUrl' => null, 'framed' => false,
    'height' => 'h-[36rem] max-h-[85vh]', 'tz' => 'Africa/Lagos', 'now' => null, 'empty' => 'No messages yet.', 'id' => null,
])

@php
    use Illuminate\Support\Carbon;

    $id ??= 'chat-'.substr(md5($label), 0, 6);
    $rows = null;
    $hasImages = false;

    if ($messages !== null) {
        $list = collect($messages)->values();
        $lastSeen = $list->keys()->filter(fn ($i) => ! empty($list[$i]['mine']) && ! empty($list[$i]['seen']))->last();
        $rows = [];
        $prev = null;
        foreach ($list as $i => $m) {
            $at = empty($m['time']) ? null : Carbon::parse($m['time'])->setTimezone($tz);
            $authorKey = (string) ($m['authorKey'] ?? (! empty($m['mine']) ? '__me' : ($m['author'] ?? '')));
            $newDay = $at && (! $prev || ! $prev['at'] || $prev['at']->format('Y-m-d') !== $at->format('Y-m-d'));
            if ($newDay) {
                $rows[] = ['day' => $at];
            }
            $grouped = ! $newDay && $prev && $prev['authorKey'] === $authorKey && empty($prev['deleted'])
                && $at && $prev['at'] && abs($prev['at']->diffInMinutes($at)) <= (int) $groupMinutes;
            $hasImages = $hasImages || collect($m['attachments'] ?? [])->contains(fn ($a) => ($a['type'] ?? '') === 'image');
            $rows[] = ['message' => $m, 'grouped' => $grouped, 'seen' => $i === $lastSeen ? $m['seen'] : null];
            $prev = ['at' => $at, 'authorKey' => $authorKey, 'deleted' => ! empty($m['deleted'])];
        }
    }

    $options = ['olderAction' => $olderAction, 'image' => $id.'-image'];
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<section x-data="uiChat(@js($options))" {{ $attributes->class(['ui-chat relative flex min-h-0 min-w-0 flex-col bg-surface', $height, 'rounded-lg border border-line' => $framed]) }} aria-label="{{ $label }}" id="{{ $id }}" data-chat > @isset($header) <div class="shrink-0 border-b border-line px-4 py-3">{{ $header }}</div> @endisset <div class="relative min-h-0 flex-1"> <div x-ref="scroller" role="log" aria-live="off" aria-label="{{ $label }}" tabindex="0" class="ui-chat-scroll h-full overflow-y-auto overscroll-contain px-3 py-4 focus-ring-inset sm:px-4" data-chat-scroll > @if ($olderAction && $hasOlder) <div class="flex justify-center pb-4" data-chat-older-row> <x-ui.button variant="outline" size="sm" x-on:click="loadOlder()" x-bind:aria-busy="loadingOlder ? 'true' : null" data-chat-older>Load older messages</x-ui.button> </div> @endif <div class="min-w-0" data-chat-list> @if ($rows !== null && $rows === []) <p class="type-meta py-2" data-chat-empty>{{ $empty }}</p> @elseif ($rows !== null) @foreach ($rows as $row) @if (isset($row['day'])) <x-ui.chat.day :date="$row['day']" :tz="$tz" :now="$now" wire:key="{{ $id }}-day-{{ $row['day']->format('Ymd') }}" /> @else @php($m = $row['message']) <x-ui.chat.message :key="$m['key']" :author="$m['author'] ?? null" :avatar="$m['avatar'] ?? null" :mine="! empty($m['mine'])" :time="$m['time'] ?? null" :body="$m['body'] ?? ''" :status="$m['status'] ?? 'sent'" :edited="! empty($m['edited'])" :deleted="! empty($m['deleted'])" :attachments="$m['attachments'] ?? []" :reactions="$m['reactions'] ?? []" :react-action="$reactAction" :seen="$row['seen']" :grouped="$row['grouped']" :retry-action="$retryAction" :mention-url="$mentionUrl" :hashtag-url="$hashtagUrl" :tz="$tz" :now="$now" wire:key="{{ $id }}-m-{{ $m['key'] }}" /> @endif @endforeach @else {{ $slot }} @endif </div> </div> <div class="pointer-events-none absolute inset-x-0 bottom-3 flex justify-center px-3" x-show="unread > 0" x-cloak> <x-ui.button variant="secondary" size="sm" class="pointer-events-auto shadow-popover" x-on:click="jump()" data-chat-jump> <span x-text="unread === 1 ? '1 new message' : unread + ' new messages'">New messages</span> <x-ui.icon name="arrow-down" /> </x-ui.button> </div> </div> <p class="sr-only" role="status" aria-live="polite" aria-atomic="true" x-text="announcement" data-chat-announcer></p> <div class="shrink-0 px-3 sm:px-4" @if ($messages !== null) wire:key="{{ $id }}-typing" @endif> <x-ui.chat.typing :names="$typing" /> </div> @isset($composer) <div class="shrink-0 border-t border-line px-3 pt-3 pb-2 sm:px-4">{{ $composer }}</div> @elseif ($sendAction) <div class="shrink-0 border-t border-line px-3 pt-3 pb-2 sm:px-4"> <x-ui.chat.composer :action="$sendAction" :maxlength="$maxlength" :attach="$attach" :id="$id.'-composer'" /> </div> @endif @if ($hasImages || $attributes->has('data-chat-images')) <x-ui.dialog :name="$id.'-image'" title="Image" size="xl" data-chat-image-dialog> <template x-if="viewer.src"> <figure class="flex flex-col items-center gap-2"> <img x-bind:src="viewer.src" x-bind:alt="viewer.alt" class="block h-auto max-h-[55vh] w-auto max-w-full rounded-md object-contain"> <figcaption class="type-meta wrap-anywhere" x-text="viewer.alt"></figcaption> </figure> </template> <x-slot:footer> <x-ui.button variant="outline" size="sm" href="#" x-bind:href="viewer.src" target="_blank" rel="noopener">Open full size<span class="sr-only"> (opens in new tab)</span></x-ui.button> <x-ui.button size="sm" x-on:click="close()" autofocus>Close</x-ui.button> </x-slot:footer> </x-ui.dialog> @endif </section>
