{{--
    A third-party player behind a click-to-load facade, for song and video links
    (TallUiKit\Support\Embed: Spotify, YouTube, Apple Music, SoundCloud and TikTok players;
    Audiomack and Boomplay, which publish no verified embed URL, and any short or
    unrecognised link render a link that opens the platform in a new tab). Nothing from
    the provider loads, and no cookie is set, until the user presses "Load <Provider>
    player"; the iframe then replaces the facade at the same size (no layout shift) and
    takes focus. Only allow-listed hosts are parsed (a URL that merely mentions one in
    its path or query is refused), and the iframe has a title, loading="lazy",
    referrerpolicy, the provider's minimal `allow` list and a `sandbox`.
    Facade look: the provider and the item's title in body text, a "Load player" outline
    button, a caption saying the provider will set cookies; 1px `line` edge, 8px
    radius, `panel-footer` fill. No logo tile, no gradient, no cover blur.
    Props: url (required), title (what is playing, for the iframe title and button),
    artwork (optional square image, decorative), autoload (skip the facade, for a page
    whose only purpose is the player).
--}}
@props(['url', 'title' => null, 'artwork' => null, 'autoload' => false])

@php
    $embed = \TallUiKit\Support\Embed::resolve($url);
    $name = $embed['name'] ?? 'the platform';
    $label = $title ? $title.' on '.$name : $name.' player';
    $frameStyle = $embed && $embed['src'] ? ($embed['aspect'] ? 'aspect-ratio: '.$embed['aspect'] : 'height: '.$embed['height'].'px') : null;
    $safeUrl = $embed['url'] ?? (preg_match('#^https?://#i', (string) $url) ? $url : null);
@endphp

@if ($embed && $embed['src'])
    <div
        x-data="uiEmbed"
        @if ($autoload) x-init="loaded = true" @endif
        {{ $attributes->class(['relative w-full overflow-hidden rounded-lg border border-line bg-panel-footer', 'max-w-sm' => $embed['aspect'] === '9/16']) }}
        style="{{ $frameStyle }}"
        data-embed="{{ $embed['provider'] }}"
    >
        <div x-show="! loaded" class="absolute inset-0 flex items-center gap-3 p-4" data-embed-facade>
            @if ($artwork)
                <img src="{{ $artwork }}" alt="" class="size-16 shrink-0 rounded-md border border-line object-cover" loading="lazy" decoding="async">
            @endif
            <div class="flex min-w-0 flex-1 flex-col gap-1">
                <p class="type-body-strong wrap-anywhere">{{ $title ?? $name }}</p>
                <p class="type-meta">{{ $name }}. Loading the player lets {{ $name }} set cookies.</p>
                <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1">
                    <x-ui.button variant="outline" size="sm" x-on:click="load()" data-embed-load>
                        <x-ui.icon name="play" class="size-4" />
                        Load {{ $name }} player<span class="sr-only">: {{ $title ?? $name }}</span>
                    </x-ui.button>
                    <x-ui.link :href="$embed['url']" target="_blank" class="type-meta inline-flex min-h-6 items-center">Open on {{ $name }}</x-ui.link>
                </div>
            </div>
        </div>
        <template x-if="loaded">
            <iframe
                x-ref="frame"
                src="{{ $embed['src'] }}"
                title="{{ $label }}"
                class="absolute inset-0 size-full border-0"
                loading="lazy"
                referrerpolicy="strict-origin-when-cross-origin"
                allow="{{ $embed['allow'] }}"
                sandbox="{{ $embed['sandbox'] }}"
                @if (str_contains((string) $embed['allow'], 'fullscreen')) allowfullscreen @endif
            ></iframe>
        </template>
    </div>
@else
    <div {{ $attributes->class(['flex items-center gap-3 rounded-lg border border-line bg-panel-footer p-4']) }} data-embed="{{ $embed['provider'] ?? 'link' }}" data-embed-link>
        <div class="flex min-w-0 flex-1 flex-col gap-0.5">
            <p class="type-body-strong wrap-anywhere">{{ $title ?? ($embed ? 'Listen on '.$name : 'Listen to the track') }}</p>
            <p class="type-meta">{{ $embed ? 'No in-app player for '.$name.' links.' : 'No in-app player for this link.' }}</p>
        </div>
        @if ($safeUrl)
            <x-ui.button variant="outline" size="sm" :href="$safeUrl" target="_blank" rel="noopener noreferrer" class="shrink-0">
                Open on {{ $embed ? $name : 'the platform' }}<span class="sr-only"> (opens in new tab)</span>
                <x-ui.icon name="external-link" class="size-4" />
            </x-ui.button>
        @endif
    </div>
@endif
