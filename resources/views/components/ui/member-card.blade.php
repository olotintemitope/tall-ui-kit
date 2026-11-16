{{--
    A person in a directory or a "Who to follow" list (docs/components/community.md,
    "Member card"): avatar, name (a link with `href`), role or tagline, a stat strip
    (x-ui.stat-strip: "Followers 1.2k"), and Follow and Message. A 1px `line` border and
    8px radius; never inside another card.

    Follow is a toggle: "Follow" (primary) becomes "Following" (outline, aria-pressed).
    It is optimistic: the button changes at once, then `followAction` is called with the
    key and the new state, e.g. follow('u-12', true); a false return or a failed request
    puts it back and shows a toast (resources/js/community-core.js, one listener for the
    page). Without `followAction` a bubbling `ui-follow` event, detail { key, on }.
    Message is a link (`messageHref`) or a Livewire call (`messageAction`, with the key).

    Props: key (required), name, avatar, role, href, stats (list of [label, value];
    values are shown compact), following (false), followAction, messageHref,
    messageAction, followLabel ("Follow"), size (sm | md: the buttons' control size, sm),
    headingLevel (3).
--}}
@props([
    'key', 'name' => '', 'avatar' => null, 'role' => null, 'href' => null, 'stats' => [], 'following' => false,
    'followAction' => null, 'messageHref' => null, 'messageAction' => null, 'followLabel' => 'Follow', 'size' => 'sm', 'headingLevel' => 3,
])

@php
    $tag = 'h'.max(2, min(6, (int) $headingLevel));
    $id = 'member-'.\Illuminate\Support\Str::slug((string) $key);
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<article {{ $attributes->class(['ui-member-card flex min-w-0 flex-col rounded-lg border border-line bg-card']) }} aria-labelledby="{{ $id }}-name" data-member data-key="{{ $key }}" @if ($followAction) data-follow-action="{{ $followAction }}" @endif> <div class="flex min-w-0 items-start gap-3 p-4"> <x-ui.avatar :name="$name" :src="$avatar" alt="" size="lg" /> <div class="min-w-0 flex-1"> <{{ $tag }} id="{{ $id }}-name" class="type-body-strong wrap-anywhere text-ink">@if ($href)<a href="{{ $href }}" class="rounded-sm hover:underline underline-offset-2 focus-ring">{{ $name }}</a>@else{{ $name }}@endif</{{ $tag }}> @if ($role)<p class="type-meta wrap-anywhere">{{ $role }}</p>@endif </div> </div> @if ($stats) <x-ui.stat-strip :columns="min(3, max(2, count($stats)))" class="ui-member-stats"> @foreach ($stats as $stat) <x-ui.stat :label="$stat['label'] ?? $stat[0] ?? ''" class="px-4 py-2"><span class="type-numeric text-ink" title="{{ number_format((float) ($stat['value'] ?? $stat[1] ?? 0)) }}">{{ is_numeric($stat['value'] ?? $stat[1] ?? null) ? \TallUiKit\Support\Format::compact($stat['value'] ?? $stat[1]) : ($stat['value'] ?? $stat[1] ?? '') }}</span></x-ui.stat> @endforeach </x-ui.stat-strip> @endif <div class="flex flex-wrap items-center gap-2 p-4 @if ($stats) pt-3 @else pt-0 @endif"> <x-ui.follow-button :key="$key" :following="$following" :label="$followLabel" :name="$name" :size="$size" /> @if ($messageHref) <x-ui.button variant="outline" :size="$size" :href="$messageHref">Message<span class="sr-only"> {{ $name }}</span></x-ui.button> @elseif ($messageAction) <x-ui.button variant="outline" :size="$size" wire:click="{{ $messageAction }}({{ \Illuminate\Support\Js::from((string) $key) }})">Message<span class="sr-only"> {{ $name }}</span></x-ui.button> @endif </div> </article>
