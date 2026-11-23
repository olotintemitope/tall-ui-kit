{{--
    Nested comments (docs/components/community.md, "Comment thread"), in the x-ui.timeline
    look: each comment has the author's avatar on a 1px `line` rail, "Author · time" and
    the body (escaped, with links, @mentions and #hashtags: TallUiKit\Support\RichText).
    Replies nest under their comment up to `depth` levels; deeper replies become a
    "Continue thread" link (`continueHref`, ":key" is replaced). "Show 4 more replies"
    calls `moreAction` with the comment's key (or dispatches `ui-thread-more`, detail
    { key }). A comment with replies has "Hide replies" / "Show 3 replies"
    (aria-expanded) to collapse its thread.

    Reply opens one x-ui.chat.composer inline under that comment (only one at a time,
    copied from a single template, so the page carries one reply box, not one per
    comment; focus moves into it, Escape or a sent reply closes it and returns focus to
    Reply; Livewire leaves it alone while it is open). It
    calls `replyAction` with the parent's key, the text and an idempotency key, e.g.
    reply('c-12', 'Agreed.', '9b2e...'); the box at the top calls it with null as the
    parent. One Alpine component per thread (uiThread), none per comment.

    Props: comments (list of [key, author, avatar, time, body, edited, replies (the same
    shape), more (replies not loaded yet)]), label ("Comments": the list's name), depth
    (3), replyAction, moreAction, continueHref, composer (true when replyAction is set:
    the "Add a comment" box at the top), maxlength, mentionUrl, hashtagUrl, empty ("No
    comments yet."), tz (Africa/Lagos), now, id.

    Questions and answers (flat replies, votes, an accepted answer):
      nested (true; false: no Reply under comments, only the box at the top)
      likeAction (a Livewire method called with the comment's key and the new state,
        optimistic like x-ui.post's like; false rolls back), likeLabel ("Like"),
        likeIcon ("heart"); a comment carries likes and liked
      acceptAction (called with the comment's key: "Mark as answer" on comments that are
        not accepted), acceptLabel, acceptedLabel ("Accepted answer": the badge on a
        comment with accepted true)
      addLabel, addPlaceholder ("Add a comment"), addSubmitLabel ("Send"): the top box
--}}
@props([
    'comments' => [], 'label' => 'Comments', 'depth' => 3, 'replyAction' => null, 'moreAction' => null,
    'continueHref' => null, 'composer' => null, 'maxlength' => null, 'mentionUrl' => null, 'hashtagUrl' => null,
    'empty' => 'No comments yet.', 'tz' => 'Africa/Lagos', 'now' => null, 'id' => null,
    'nested' => true, 'likeAction' => null, 'likeLabel' => 'Like', 'likeIcon' => 'heart',
    'acceptAction' => null, 'acceptLabel' => 'Mark as answer', 'acceptedLabel' => 'Accepted answer',
    'addLabel' => 'Add a comment', 'addPlaceholder' => 'Add a comment', 'addSubmitLabel' => 'Send',
])

@php
    $id ??= 'thread-'.substr(md5($label.json_encode(collect($comments)->pluck('key'))), 0, 6);
    $composer ??= (bool) $replyAction;
    $list = collect($comments)->values();
    $shared = [
        'maxDepth' => max(1, (int) $depth), 'replyAction' => $replyAction, 'moreAction' => $moreAction,
        'continueHref' => $continueHref, 'maxlength' => $maxlength, 'mentionUrl' => $mentionUrl,
        'hashtagUrl' => $hashtagUrl, 'tz' => $tz, 'now' => $now, 'thread' => $id,
        'nested' => (bool) $nested, 'likeAction' => $likeAction, 'likeLabel' => $likeLabel,
        'acceptAction' => $acceptAction, 'acceptLabel' => $acceptLabel,
        // Rendered once for the thread, not once per comment.
        'likeIconHtml' => \Illuminate\Support\Facades\Blade::render('<x-ui.icon :name="$n" class="ui-like-icon" />', ['n' => $likeIcon]),
        'acceptedHtml' => \Illuminate\Support\Facades\Blade::render('<x-ui.badge variant="success" size="sm">{{ $l }}</x-ui.badge>', ['l' => $acceptedLabel]),
    ];
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<div x-data="uiThread" {{ $attributes->class(['ui-thread min-w-0']) }} id="{{ $id }}" data-comment-thread> @if ($composer && $replyAction) <x-ui.chat.composer :action="$replyAction" :params="[null]" :label="$addLabel" :submit-label="$addSubmitLabel" :placeholder="$addPlaceholder" :maxlength="$maxlength" size="sm" class="mb-4" :id="$id.'-new'" /> @endif @if ($replyAction && $nested) {{-- The one reply box, copied under the comment being answered (uiThread); never one per comment. --}} <template data-reply-template><div class="mt-2"><x-ui.chat.composer :action="$replyAction" reply-slot label="Reply" placeholder="Write a reply" :maxlength="$maxlength" size="sm" autofocus :id="$id.'-reply'" /></div></template> @endif @if ($list->isEmpty()) <p class="type-meta py-2">{{ $empty }}</p> @else <ol aria-label="{{ $label }}" class="min-w-0"> @foreach ($list as $comment) <x-ui.comment-thread.comment :comment="$comment" :level="1" :shared="$shared" wire:key="{{ $id }}-{{ $comment['key'] }}" /> @endforeach </ol> @endif </div>
