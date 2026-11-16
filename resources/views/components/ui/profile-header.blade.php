{{--
    The top of a profile page (docs/components/community.md, "Profile header"): a 48px
    avatar, the name as the page's heading (`level`, 1), an optional "Verified" pill, the
    handle and role or tagline, an optional bio, stats in an x-ui.stat-strip (values
    compact: "Followers 12.4k"), and Follow and Message (the same optimistic toggle as
    x-ui.member-card). Flat, like x-ui.page-header: no card, no cover image, no glow.
    Stacks below 640px; the buttons wrap under the name.

    Props: key (required), name, avatar, handle, role, bio, verified (false), stats (list
    of [label, value]), following (false), followAction, messageHref, messageAction,
    level (1), size (md: the buttons' control size).
    Slot: actions (more buttons after Message, e.g. an x-ui.dropdown).
--}}
@props([
    'key', 'name' => '', 'avatar' => null, 'handle' => null, 'role' => null, 'bio' => null, 'verified' => false,
    'stats' => [], 'following' => false, 'followAction' => null, 'messageHref' => null, 'messageAction' => null,
    'level' => 1, 'size' => 'md',
])

@php
    $tag = 'h'.max(1, min(6, (int) $level));
    $button = $size === 'md' ? 'default' : $size;
@endphp

{{-- Markup kept on few lines: whitespace runs are nodes that every morph walks and bytes in every response. --}}
<header {{ $attributes->class(['ui-profile-header min-w-0']) }} data-profile data-key="{{ $key }}" @if ($followAction) data-follow-action="{{ $followAction }}" @endif> <div class="flex min-w-0 flex-col gap-4 sm:flex-row sm:items-start"> <x-ui.avatar :name="$name" :src="$avatar" alt="" size="xl" /> <div class="min-w-0 flex-1"> <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1"> <{{ $tag }} class="type-title wrap-anywhere">{{ $name }}</{{ $tag }}> @if ($verified)<x-ui.badge tone="info" size="sm">Verified</x-ui.badge>@endif </div> @if ($handle || $role) <p class="type-meta mt-0.5 flex flex-wrap gap-x-1.5"> @if ($handle)<span class="wrap-anywhere">{{ '@'.ltrim($handle, '@') }}</span>@endif @if ($handle && $role)<span aria-hidden="true">·</span>@endif @if ($role)<span class="wrap-anywhere">{{ $role }}</span>@endif </p> @endif @if ($bio)<p class="type-body mt-2 max-w-prose whitespace-pre-line wrap-anywhere text-ink-body">{{ $bio }}</p>@endif </div> <div class="flex flex-wrap items-center gap-2 sm:justify-end"> <x-ui.follow-button :key="$key" :following="$following" :name="$name" :size="$size" /> @if ($messageHref) <x-ui.button variant="outline" :size="$button" :href="$messageHref">Message<span class="sr-only"> {{ $name }}</span></x-ui.button> @elseif ($messageAction) <x-ui.button variant="outline" :size="$button" wire:click="{{ $messageAction }}({{ \Illuminate\Support\Js::from((string) $key) }})">Message<span class="sr-only"> {{ $name }}</span></x-ui.button> @endif {{ $actions ?? '' }} </div> </div> @if ($stats) <x-ui.stat-strip :columns="min(6, max(2, count($stats)))" class="mt-5"> @foreach ($stats as $stat) <x-ui.stat :label="$stat['label'] ?? $stat[0] ?? ''"><span title="{{ is_numeric($stat['value'] ?? $stat[1] ?? null) ? number_format((float) ($stat['value'] ?? $stat[1])) : '' }}">{{ is_numeric($stat['value'] ?? $stat[1] ?? null) ? \TallUiKit\Support\Format::compact($stat['value'] ?? $stat[1]) : ($stat['value'] ?? $stat[1] ?? '') }}</span></x-ui.stat> @endforeach </x-ui.stat-strip> @endif </header>
