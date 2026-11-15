{{--
    Song preview player around <audio preload="none"> (nothing downloads until play).
    One compact 48px row: a 32px play/pause toggle (aria-pressed, named "Play <title>"),
    the title (body-strong) and artist (meta), a seek bar on the kit range (value text
    "0:12 of 0:30"), elapsed and total in numeric, and a mute toggle (aria-pressed).
    No waveform art. Only one player on the page plays at a time.
    Keyboard: Tab reaches play, seek and mute; Space or Enter on play toggles; Left and
    Right on play or the seek bar move 5 seconds (Home/End on the bar jump); Space on
    the seek bar plays or pauses too.
    States: loading (after play, until audio arrives: the button keeps its name and
    shows the kit spinner, aria-busy), error ("Preview unavailable." in red-700 text,
    the button aria-disabled), playing, paused.
    Props: src (required), title (required), artist, duration (seconds, shown before
    metadata loads), type (audio/mpeg), compact (hides the artist line).
--}}
@props(['src', 'title', 'artist' => null, 'duration' => 0, 'type' => 'audio/mpeg', 'compact' => false])

@php
    $id = 'audio-'.substr(md5($src.$title), 0, 8);
    $total = sprintf('%d:%02d', intdiv((int) $duration, 60), (int) $duration % 60);
@endphp

<div
    x-data="uiAudio({ duration: {{ (float) $duration }}, title: @js($title) })"
    role="group"
    aria-label="{{ $title }} preview"
    {{ $attributes->class(['flex min-h-12 min-w-0 items-center gap-3 rounded-lg border border-line bg-card px-2 py-1.5']) }}
    data-audio
    x-bind:data-state="failed ? 'error' : (loading ? 'loading' : (playing ? 'playing' : 'paused'))"
    data-state="paused"
>
    {{-- src on the element itself, so a missing or unplayable file fires `error` on <audio> (with a <source> child it fires on the source and the player would wait forever). --}}
    <audio x-ref="audio" preload="none" src="{{ $src }}" data-type="{{ $type }}"></audio>

    <button
        type="button"
        class="relative flex size-8 shrink-0 items-center justify-center rounded-md border border-control bg-surface text-ink transition-colors duration-100 focus-ring state-hover:bg-surface-subtle state-active:bg-surface-muted aria-pressed:bg-surface-muted aria-disabled:cursor-not-allowed aria-disabled:border-dashed aria-disabled:bg-surface-subtle aria-disabled:text-ink-muted"
        aria-label="Play {{ $title }}"
        aria-pressed="false"
        x-bind:aria-pressed="playing ? 'true' : 'false'"
        x-bind:aria-busy="loading ? 'true' : null"
        x-bind:aria-disabled="failed ? 'true' : null"
        x-on:click="toggle()"
        x-on:keydown.right.prevent="nudge(5)"
        x-on:keydown.left.prevent="nudge(-5)"
        data-audio-toggle
    >
        <x-ui.icon name="play" class="size-4" x-show="! playing && ! loading" />
        <x-ui.icon name="pause" class="size-4" x-show="playing && ! loading" x-cloak />
        <span x-show="loading" x-cloak class="flex"><x-ui.spinner size="sm" :announce="false" /></span>
    </button>

    <div class="flex min-w-0 flex-1 flex-col gap-0.5">
        <div class="flex min-w-0 items-baseline justify-between gap-3">
            <p class="min-w-0 wrap-anywhere">
                <span class="type-body-strong">{{ $title }}</span>
                @if ($artist && ! $compact)
                    <span class="type-meta"> · {{ $artist }}</span>
                @endif
            </p>
            <p class="type-numeric shrink-0 text-ink-muted" aria-hidden="true"><span x-text="elapsed">0:00</span> / <span x-text="total">{{ $total }}</span></p>
        </div>
        <p x-show="failed" x-cloak class="type-caption text-danger-text!" role="status">Preview unavailable.</p>
        <div x-show="! failed" class="relative -my-1 h-6" data-audio-seek-track>
            <div class="pointer-events-none absolute inset-x-0 top-1/2 h-1 -translate-y-1/2 rounded-sm bg-control" aria-hidden="true"></div>
            <div class="pointer-events-none absolute left-0 top-1/2 h-1 -translate-y-1/2 rounded-sm bg-secondary" aria-hidden="true" x-bind:style="`width: ${duration ? Math.min(100, current / duration * 100) : 0}%`" style="width: 0"></div>
            <input
                type="range"
                class="ui-range absolute inset-0"
                min="0"
                x-bind:max="Math.max(duration, 0.1)"
                max="{{ max((float) $duration, 0.1) }}"
                step="0.1"
                value="0"
                x-bind:value="current"
                x-on:input="seek($event.target.value)"
                x-on:keydown.right.prevent="nudge(5)"
                x-on:keydown.left.prevent="nudge(-5)"
                x-on:keydown.space.prevent="toggle()"
                aria-label="Seek {{ $title }}"
                x-bind:aria-valuetext="seekText"
                aria-valuetext="0:00 of {{ $total }}"
                data-ui="range"
                data-audio-seek
            />
        </div>
    </div>

    <button
        type="button"
        class="flex size-8 shrink-0 items-center justify-center rounded-md text-ink-muted transition-colors duration-100 focus-ring state-hover:bg-surface-subtle state-hover:text-ink aria-pressed:bg-surface-muted aria-pressed:text-ink"
        aria-label="Mute {{ $title }}"
        aria-pressed="false"
        x-bind:aria-pressed="muted ? 'true' : 'false'"
        x-on:click="mute()"
        data-audio-mute
    >
        <x-ui.icon name="volume-2" class="size-4" x-show="! muted" />
        <x-ui.icon name="volume-x" class="size-4" x-show="muted" x-cloak />
    </button>
</div>
