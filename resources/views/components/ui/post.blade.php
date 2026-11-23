{{--
    A feed item (docs/components/community.md, "Post"): an <article> with the author
    header (avatar, name, an optional "Verified" pill, handle, time), the body, a media
    slot and an action bar: Like with its count, Comment, Share and a More menu
    (x-ui.dropdown). Flat: posts are divided by 1px `line` rules in the feed, not cards.

    The body keeps line breaks; URLs, @mentions and #hashtags become links after the
    text is escaped (TallUiKit\Support\RichText). Counts are compact ("1.2k", the full
    number is spoken: "Like, 1,240 likes" is the button's name and "1.2k" its visible
    count, so the name starts with the visible "Like").

    Like is optimistic: the button turns pressed and the count moves at once, then
    `likeAction` is called with the key and the new state, e.g. like('post-12', true); a
    false return or a failed request rolls both back and shows a toast. One delegated
    listener for the page (resources/js/community-core.js), no Alpine per post. Without
    `likeAction` a bubbling `ui-like` event, detail { key, on }. Share uses the system
    share sheet where there is one, else copies `shareUrl` and shows "Link copied."

    Props: key (required), author, avatar, handle, verified (false), time, body, href
    (the permalink: the time links to it), likes (0), liked (false), comments (0),
    likeAction, commentHref, commentAction (Livewire method, called with the key),
    shareUrl, mentionUrl, hashtagUrl, headingLevel (3: the author's name is the
    article's heading), tz (Africa/Lagos), now.
    Threads and questions: title (then the title is the heading and names the article;
    linked to titleHref, else href), authorBadge (a role beside the name, "Instructor"),
    status ("Solved": a badge beside the title, statusVariant success), likeLabel
    ("Like"), likeIcon ("heart"), commentLabel ("Comment").
    Slots: default (extra content under the body, e.g. x-ui.poll), media (images, a
    video, an embed), more (x-ui.dropdown.item rows; no More button without it), footer
    (e.g. x-ui.comment-thread).
--}}
@props([
    'key', 'author' => null, 'avatar' => null, 'handle' => null, 'verified' => false, 'time' => null, 'body' => '',
    'href' => null, 'likes' => 0, 'liked' => false, 'comments' => 0, 'likeAction' => null, 'commentHref' => null,
    'commentAction' => null, 'shareUrl' => null, 'mentionUrl' => null, 'hashtagUrl' => null, 'headingLevel' => 3,
    'tz' => 'Africa/Lagos', 'now' => null,
    'title' => null, 'titleHref' => null, 'authorBadge' => null, 'status' => null, 'statusVariant' => 'success',
    'likeLabel' => 'Like', 'likeIcon' => 'heart', 'commentLabel' => 'Comment',
])

@php
    use TallUiKit\Support\Format;
    use TallUiKit\Support\RichText;

    $tag = 'h'.max(2, min(6, (int) $headingLevel));
    $id = 'post-'.\Illuminate\Support\Str::slug((string) $key);
    $likes = (int) $likes;
    $comments = (int) $comments;
    $who = $author ?? 'Someone';
    $bar = 'ui-act';
    // With a title (a question, a thread) the title is the heading and names the article.
    $titled = filled($title);
    $authorEl = $titled ? 'p' : $tag;
    $titleHref ??= $href;
    // Built as strings: a Blade conditional adds two morph markers to every post in every response.
    $authorBadgeHtml = filled($authorBadge) ? \Illuminate\Support\Facades\Blade::render('<x-ui.badge variant="neutral" size="sm">{{ $b }}</x-ui.badge>', ['b' => $authorBadge]) : '';
    $titleHtml = '';
    if ($titled) {
        $text = e($title);
        $link = $titleHref ? '<a href="'.e($titleHref).'" wire:navigate class="rounded-sm hover:underline underline-offset-2 focus-ring" data-post-title>'.$text.'</a>' : $text;
        $badge = filled($status) ? \Illuminate\Support\Facades\Blade::render('<x-ui.badge :variant="$v" size="sm">{{ $s }}</x-ui.badge>', ['v' => $statusVariant, 's' => $status]) : '';
        $titleHtml = '<div class="mb-1 flex min-w-0 flex-wrap items-baseline gap-x-2 gap-y-1"><'.$tag.' id="'.e($id).'-title" class="type-heading min-w-0 wrap-anywhere text-ink">'.$link.'</'.$tag.'>'.$badge.'</div>';
    }
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<article {{ $attributes->class(['ui-post min-w-0 py-4']) }} aria-labelledby="{{ $id }}-{{ $titled ? 'title' : 'author' }}" id="{{ $id }}" data-post data-key="{{ $key }}" @if ($likeAction) data-like-action="{{ $likeAction }}" @endif> <header class="flex min-w-0 items-start gap-3"> <x-ui.avatar :name="$who" :src="$avatar" alt="" size="lg" /> <div class="flex min-w-0 flex-1 flex-col"> <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-0.5"> <{{ $authorEl }} id="{{ $id }}-author" class="type-body-strong wrap-anywhere text-ink">{{ $who }}</{{ $authorEl }}> @if ($verified)<x-ui.badge tone="info" size="sm">Verified</x-ui.badge>@endif {!! $authorBadgeHtml !!} </div> <p class="type-meta flex min-w-0 flex-wrap gap-x-1.5"> @if ($handle)<span class="wrap-anywhere">{{ '@'.ltrim($handle, '@') }}</span><span aria-hidden="true">·</span>@endif @if ($time) @if ($href)<a href="{{ $href }}" class="rounded-sm hover:text-ink hover:underline underline-offset-2 focus-ring"><x-ui.time :value="$time" mode="relative" :tz="$tz" :now="$now" /></a>@else<x-ui.time :value="$time" mode="relative" :tz="$tz" :now="$now" />@endif @endif </p> </div> @isset($more) <x-ui.dropdown align="right"> <x-slot:trigger> <x-ui.button variant="ghost" size="icon-sm" aria-label="More actions for the post by {{ $who }}"><x-ui.icon name="ellipsis" /></x-ui.button> </x-slot:trigger> {{ $more }} </x-ui.dropdown> @endisset </header> <div class="mt-3 min-w-0 pl-12 max-sm:pl-0"> {!! $titleHtml !!} @if (filled($body)) <p class="ui-post-body type-body min-w-0 whitespace-pre-wrap wrap-anywhere text-ink" data-post-body>{{ RichText::render($body, $mentionUrl, $hashtagUrl) }}</p> @endif @if ($slot->isNotEmpty()) <div class="mt-3 min-w-0">{{ $slot }}</div> @endif @isset($media) <div class="mt-3 min-w-0 overflow-hidden rounded-lg" data-post-media>{{ $media }}</div> @endisset <div class="-ml-2 mt-2 flex flex-wrap items-center gap-1" role="group" aria-label="Actions for the post by {{ $who }}"> <button type="button" class="{{ $bar }}" aria-pressed="{{ $liked ? 'true' : 'false' }}" data-post-like data-count="{{ $likes }}"> <x-ui.icon :name="$likeIcon" class="ui-like-icon" /> <span>{{ $likeLabel }}</span><span class="sr-only">,</span> <span class="type-numeric text-ink-muted" data-post-like-count aria-hidden="true">{{ Format::compact($likes) }}</span><span class="sr-only" data-post-like-spoken>{{ number_format($likes) }} {{ $likes === 1 ? 'like' : 'likes' }}</span> </button> @if ($commentHref) <a href="{{ $commentHref }}" class="{{ $bar }}" data-post-comment> @else <button type="button" class="{{ $bar }}" @if ($commentAction) wire:click="{{ $commentAction }}({{ \Illuminate\Support\Js::from((string) $key) }})" @endif data-post-comment> @endif <x-ui.icon name="message-square" /> <span>{{ $commentLabel }}</span><span class="sr-only">,</span> <span class="type-numeric text-ink-muted" aria-hidden="true">{{ Format::compact($comments) }}</span><span class="sr-only">{{ number_format($comments) }} {{ $comments === 1 ? 'comment' : 'comments' }}</span> @if ($commentHref)</a>@else</button>@endif @if ($shareUrl) <button type="button" class="{{ $bar }}" data-post-share data-url="{{ $shareUrl }}" data-title="Post by {{ $who }}"> <x-ui.icon name="share" /> <span>Share</span> </button> @endif </div> @isset($footer) <div class="mt-3 min-w-0 border-t border-line pt-3">{{ $footer }}</div> @endisset </div> </article>
