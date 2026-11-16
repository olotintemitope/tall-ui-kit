{{--
    One comment of x-ui.comment-thread, with its replies (rendered by itself, one level
    deeper). Built by the thread from its `comments` data; not used alone.
    Props: comment ([key, author, avatar, time, body, edited, replies, more]), level (1),
    shared (the thread's settings).
--}}
@props(['comment', 'level' => 1, 'shared' => []])

@php
    use Illuminate\Support\Carbon;
    use TallUiKit\Support\Format;
    use TallUiKit\Support\RichText;

    // Built as strings where possible: each Blade conditional adds two morph markers per comment.
    $key = (string) $comment['key'];
    $js = \Illuminate\Support\Js::from($key);
    $jsAttr = e($js);
    $who = $comment['author'] ?? 'Someone';
    $replies = collect($comment['replies'] ?? [])->values();
    $more = (int) ($comment['more'] ?? 0);
    $count = $replies->count() + $more;
    $deeper = $level >= ($shared['maxDepth'] ?? 3);
    $domId = ($shared['thread'] ?? 'thread').'-c-'.\Illuminate\Support\Str::slug($key);
    $tz = $shared['tz'] ?? 'Africa/Lagos';
    $at = empty($comment['time']) ? null : Carbon::parse($comment['time'])->setTimezone($tz);
    $now = ! empty($shared['now']) ? Carbon::parse($shared['now'])->setTimezone($tz) : null;
    $face = ! empty($comment['avatar'])
        ? \Illuminate\Support\Facades\Blade::render('<x-ui.avatar :name="$n" :src="$s" alt="" />', ['n' => $who, 's' => $comment['avatar']])
        : '<span class="inline-flex size-7 shrink-0 items-center justify-center rounded-full bg-surface-muted font-body text-[12px] font-semibold leading-none text-ink ring-1 ring-inset ring-line select-none">'.e(collect(preg_split('/\s+/', trim($who)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?').'</span>';
    $meta = ($at ? '<span aria-hidden="true">·</span><time datetime="'.e($at->toIso8601String()).'" title="'.e($at->format('j M Y, H:i')).'">'.e(Format::relative($at, $now)).'</time>' : '')
        .(! empty($comment['edited']) ? '<span aria-hidden="true">·</span><span>Edited</span>' : '');
    $replyButton = ! empty($shared['replyAction'])
        ? '<button type="button" class="ui-act" x-on:click="reply('.$jsAttr.')" x-bind:aria-expanded="replyTo === '.$jsAttr.' ? \'true\' : \'false\'" aria-expanded="false" aria-controls="'.e($domId).'-reply" data-reply-to="'.e($key).'">Reply<span class="sr-only"> to '.e($who).'</span></button>'
        : '';
    $showLabel = e(\Illuminate\Support\Js::from('Show '.$count.' '.($count === 1 ? 'reply' : 'replies')));
    $toggle = $replies->isNotEmpty() && ! $deeper
        ? '<button type="button" class="ui-act" x-on:click="toggle('.$jsAttr.')" x-bind:aria-expanded="collapsed['.$jsAttr.'] ? \'false\' : \'true\'" aria-expanded="true" aria-controls="'.e($domId).'-replies" data-thread-toggle><span x-text="collapsed['.$jsAttr.'] ? '.$showLabel.' : \'Hide replies\'">Hide replies</span></button>'
        : '';
    $words = $count.' '.($count === 1 ? 'reply' : 'replies');
    $continue = $count > 0 && $deeper
        ? '<p class="mt-2">'.(! empty($shared['continueHref'])
            ? '<a href="'.e(str_replace(':key', rawurlencode($key), $shared['continueHref'])).'" class="ui-act" data-link>Continue thread ('.$words.')</a>'
            : '<span class="type-meta">'.$count.' more '.($count === 1 ? 'reply' : 'replies').'</span>').'</p>'
        : '';
    $moreWords = 'Show '.$more.' more '.($more === 1 ? 'reply' : 'replies');
    $moreButton = $more > 0 && ! $deeper
        ? '<div class="mt-2" x-show="! collapsed['.$jsAttr.']">'.(! empty($shared['moreAction'])
            ? '<button type="button" class="ui-act" data-link wire:click="'.e($shared['moreAction']).'('.$jsAttr.')" wire:loading.attr="aria-busy" wire:target="'.e($shared['moreAction']).'">'.$moreWords.'</button>'
            : '<button type="button" class="ui-act" data-link x-on:click="$dispatch(\'ui-thread-more\', { key: '.$jsAttr.' })">'.$moreWords.'</button>').'</div>'
        : '';
@endphp

<li {{ $attributes->class(['ui-comment relative flex min-w-0 gap-3 pb-4 last:pb-0']) }} data-comment data-key="{{ $key }}"><span class="absolute top-8 bottom-0 left-3.5 w-px -translate-x-1/2 bg-line" aria-hidden="true" data-thread-rail></span><span class="flex h-7 w-7 shrink-0 items-center justify-center" aria-hidden="true">{!! $face !!}</span><div class="min-w-0 flex-1"><p class="type-meta flex flex-wrap items-baseline gap-x-1.5"><span class="type-body-strong wrap-anywhere text-ink">{{ $who }}</span>{!! $meta !!}</p><p class="type-body mt-0.5 min-w-0 whitespace-pre-wrap wrap-anywhere text-ink-body" data-comment-body>{{ RichText::render($comment['body'] ?? '', $shared['mentionUrl'] ?? null, $shared['hashtagUrl'] ?? null) }}</p><div class="-ml-2 mt-1 flex flex-wrap items-center gap-1">{!! $replyButton !!}{!! $toggle !!}</div><div id="{{ $domId }}-reply" wire:ignore data-reply-slot="{{ $key }}" data-reply-name="{{ $who }}"></div>{!! $continue !!}@if ($replies->isNotEmpty() && ! $deeper)<ol id="{{ $domId }}-replies" aria-label="Replies to {{ $who }}" class="mt-3 min-w-0" x-show="! collapsed[{{ $js }}]">@foreach ($replies as $reply)<x-ui.comment-thread.comment :comment="$reply" :level="$level + 1" :shared="$shared" wire:key="{{ $domId }}-{{ $reply['key'] }}" />@endforeach</ol>@endif{!! $moreButton !!}</div></li>
