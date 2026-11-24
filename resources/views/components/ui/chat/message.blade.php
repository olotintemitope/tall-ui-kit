{{--
    One message of x-ui.chat (docs/components/community.md, "Chat"). Received messages sit
    on the left with the author's avatar (or initials), sent ones on the right. The author
    and time (x-ui.time, H:i, the full date in `title`) head the first message of a group;
    `grouped` continues a group: no avatar or name on screen, both kept for screen readers
    ("Ada Obi, 10:42:"). The body keeps line breaks and turns URLs, @mentions and #hashtags
    into links after escaping everything (TallUiKit\Support\RichText).

    Bubbles: received on `surface` with a 1px `line` edge, sent on `surface-subtle`; 8px
    radius (rounded-lg, the kit's largest), no tails, no shadows. A deleted message is a
    dashed placeholder in meta. States are words, never colour alone: "Sending", "Not
    sent." with Retry (danger text), "Edited", and "Seen" under a sent message.

    Images (`attachments` with type image) are thumbnails that open the chat's one image
    dialog (the x-ui.lightbox look: x-ui.dialog with the image and "Open full size");
    files are x-ui.file-card compact chips (type label, name, meta), each a link. No Alpine per message: the chat's
    controller (resources/js/chat-core.js) handles the image buttons.

    Props: key (required, stable: wire:key and the scroll logic use it), author, avatar
    (photo URL), mine (false: sent by the viewer), time, body, status (sent | sending |
    failed), edited (false), deleted (false), attachments (list of [type image | file, name,
    src or href, thumb, alt, meta, width, height]), reactions (x-ui.reactions summary data),
    reactAction (Livewire method, called with the key and the reaction), reactionPicker
    (false), seen (true for "Seen", or a string such as "Seen by Ada"), grouped (false),
    retryAction (Livewire method, called with the key; without it Retry dispatches a
    bubbling `ui-chat-retry` event, detail { key }), mentionUrl and hashtagUrl (":name" and
    ":tag" patterns), tz (Africa/Lagos), now.
    Replies: replyTo (['author' => ..., 'body' => ...]: the message this one answers, quoted
    above the bubble; pass the excerpt you want shown), replyAction (shows a Reply button
    under the message; the chat's controller calls the chat's replyAction with the key, or
    dispatches a bubbling `ui-chat-reply` event, detail { key }), replyLabel ("Reply").
--}}
@props([
    'key', 'author' => null, 'avatar' => null, 'mine' => false, 'time' => null, 'body' => '',
    'status' => 'sent', 'edited' => false, 'deleted' => false, 'attachments' => [], 'reactions' => [],
    'reactAction' => null, 'reactionPicker' => false, 'seen' => null, 'grouped' => false, 'retryAction' => null,
    'mentionUrl' => null, 'hashtagUrl' => null, 'tz' => 'Africa/Lagos', 'now' => null,
    'replyTo' => null, 'replyAction' => null, 'replyLabel' => 'Reply',
])

@php
    use Illuminate\Support\Carbon;
    use TallUiKit\Support\RichText;

    /*
     * Built as strings rather than @if blocks: a chat re-renders every message, and each
     * Blade conditional adds two morph markers to the Livewire response.
     */
    $status = in_array($status, ['sent', 'sending', 'failed'], true) ? $status : 'sent';
    $who = $mine ? 'You' : ($author ?? 'Someone');
    $images = collect($attachments)->filter(fn ($a) => ($a['type'] ?? 'file') === 'image')->values();
    $files = collect($attachments)->reject(fn ($a) => ($a['type'] ?? 'file') === 'image')->values();
    $at = $time ? Carbon::parse($time)->setTimezone($tz) : null;
    $clock = $at ? '<time datetime="'.e($at->toIso8601String()).'" title="'.e($at->format('j M Y, H:i')).'">'.$at->format('H:i').'</time>' : '';
    $jsKey = e(\Illuminate\Support\Js::from((string) $key));

    $avatarHtml = '';
    if (! $mine) {
        $face = '';
        if (! $grouped) {
            $face = $avatar
                ? \Illuminate\Support\Facades\Blade::render('<x-ui.avatar :name="$n" :src="$s" alt="" />', ['n' => $who, 's' => $avatar])
                : '<span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-surface-muted font-body text-[12px] font-semibold leading-none text-ink ring-1 ring-inset ring-line select-none">'.e(collect(preg_split('/\s+/', trim($who)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?').'</span>';
        }
        $avatarHtml = '<span class="ui-chat-face" aria-hidden="true">'.$face.'</span>';
    }

    $head = $grouped
        ? '<span class="sr-only">'.e($who).($clock ? ', '.$clock : '').':</span>'
        : '<p class="type-meta flex flex-wrap items-baseline gap-x-2'.($mine ? ' justify-end' : '').'">'
            .($mine ? '<span class="sr-only">You</span>' : '<span class="type-body-strong wrap-anywhere text-ink">'.e($who).'</span>')
            .$clock.'<span class="sr-only">:</span></p>';

    $retry = $retryAction
        ? 'wire:click="'.e($retryAction).'('.$jsKey.')" wire:loading.attr="aria-busy" wire:target="'.e($retryAction).'"'
        : 'x-on:click="$dispatch(\'ui-chat-retry\', { key: '.$jsKey.' })"';
    $state = match (true) {
        $status === 'sending' => '<p class="type-caption" data-chat-state="sending">Sending</p>',
        $status === 'failed' => '<p class="type-caption flex flex-wrap items-center gap-x-2 text-danger-text" data-chat-state="failed"><span>Not sent.</span><button type="button" class="ui-act" data-link '.$retry.' data-chat-retry>Retry</button></p>',
        $edited && ! $deleted => '<p class="type-caption" data-chat-state="edited">Edited</p>',
        default => '',
    };
    $seenHtml = $seen && $mine && $status === 'sent' ? '<p class="type-caption" data-chat-seen>'.e(is_string($seen) ? $seen : 'Seen').'</p>' : '';
    $bubble = $deleted
        ? '<p class="ui-chat-bubble type-meta" data-deleted>This message was deleted.</p>'
        : (filled($body) ? '<div class="ui-chat-bubble"'.($mine ? ' data-mine' : '').' data-chat-body>'.RichText::render($body, $mentionUrl, $hashtagUrl).'</div>' : '');
    $hasReactions = ! $deleted && (collect($reactions)->sum('count') > 0 || $reactionPicker);
    // A reply quotes the message it answers above the bubble; Reply starts one.
    $quote = is_array($replyTo) && filled($replyTo['body'] ?? $replyTo['author'] ?? null)
        ? '<p class="type-caption max-w-full border-l border-line pl-2 text-ink-muted wrap-anywhere" data-chat-reply-to>'
            .(filled($replyTo['author'] ?? null) ? '<span class="type-meta text-ink-body">'.e($replyTo['author']).'</span> ' : '')
            .e((string) ($replyTo['body'] ?? '')).'</p>'
        : '';
    $replyButton = $replyAction && ! $deleted
        ? '<button type="button" class="ui-act" data-chat-reply>'.e($replyLabel).'</button>'
        : '';
    $hasAttachments = ! $deleted && $attachments;
@endphp

{{-- Compact markup: the list can be long, and every whitespace run is a node each morph walks. --}}
<div {{ $attributes->class(['ui-chat-message', 'mt-0.5' => $grouped, 'mt-4 first:mt-0' => ! $grouped]) }} data-chat-message data-key="{{ $key }}" {!! $mine ? 'data-mine' : '' !!} data-author="{{ $who }}" {!! $status !== 'sent' ? 'data-status="'.$status.'"' : '' !!} {!! $deleted ? 'data-deleted' : '' !!}>{!! $avatarHtml !!}<div class="ui-chat-col">{!! $head !!}{!! $quote !!}{!! $bubble !!}@if ($hasAttachments)<div class="flex flex-col gap-2 {{ $mine ? 'items-end' : 'items-start' }}">@foreach ($images as $image)<button type="button" class="block overflow-hidden rounded-md border border-line bg-surface-muted hover:border-control-hover focus-ring" aria-label="View image: {{ $image['alt'] ?? $image['name'] ?? 'attachment' }}" data-chat-image data-src="{{ $image['src'] ?? $image['href'] ?? '' }}" data-alt="{{ $image['alt'] ?? $image['name'] ?? '' }}"><img src="{{ $image['thumb'] ?? $image['src'] ?? $image['href'] ?? '' }}" alt="" @if (! empty($image['width'])) width="{{ (int) $image['width'] }}" height="{{ (int) ($image['height'] ?? $image['width']) }}" @endif loading="lazy" decoding="async" class="block h-auto max-h-48 w-auto max-w-full object-cover sm:max-h-60"></button>@endforeach @foreach ($files as $file)<x-ui.file-card variant="compact" :name="$file['name'] ?? 'Attachment'" :href="$file['href'] ?? $file['src'] ?? '#'" :meta="$file['meta'] ?? null" :size="$file['size'] ?? null" :type="$file['mime'] ?? null" data-chat-file />@endforeach</div>@endif @if ($hasReactions)<x-ui.reactions :reactions="$reactions" :action="$reactAction" :subject="$key" :picker="$reactionPicker" size="sm" :label="'Reactions to the message from '.$who" />@endif{!! $replyButton !!}{!! $state !!}{!! $seenHtml !!}</div></div>
