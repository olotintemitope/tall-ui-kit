<?php

namespace TallUiKit\Support;

/**
 * Resolves a song or video link to an embeddable player for x-ui.embed. A port of
 * the React resolvers (src/components/ui/TrackEmbed.tsx and
 * src/components/admin/SocialVideoEmbed.tsx) with one change: the host is parsed and
 * checked against an allow-list first, so a URL that merely contains
 * "open.spotify.com" in its path or query never produces an iframe.
 *
 * Providers with an iframe: Spotify, YouTube, Apple Music, SoundCloud, TikTok.
 * Audiomack and Boomplay are recognised (name and link) but have no verified public
 * embed URL, so, as in React, they render a link that opens the platform.
 */
class Embed
{
    /** Host (lower case, exact or a subdomain of it) => provider key. */
    public const HOSTS = [
        'open.spotify.com' => 'spotify',
        'youtube.com' => 'youtube',
        'www.youtube.com' => 'youtube',
        'm.youtube.com' => 'youtube',
        'music.youtube.com' => 'youtube',
        'youtu.be' => 'youtube',
        'music.apple.com' => 'apple-music',
        'soundcloud.com' => 'soundcloud',
        'www.soundcloud.com' => 'soundcloud',
        'm.soundcloud.com' => 'soundcloud',
        'tiktok.com' => 'tiktok',
        'www.tiktok.com' => 'tiktok',
        'm.tiktok.com' => 'tiktok',
        'vm.tiktok.com' => 'tiktok',
        'audiomack.com' => 'audiomack',
        'www.audiomack.com' => 'audiomack',
        'boomplay.com' => 'boomplay',
        'www.boomplay.com' => 'boomplay',
    ];

    public const NAMES = [
        'spotify' => 'Spotify',
        'youtube' => 'YouTube',
        'apple-music' => 'Apple Music',
        'soundcloud' => 'SoundCloud',
        'tiktok' => 'TikTok',
        'audiomack' => 'Audiomack',
        'boomplay' => 'Boomplay',
    ];

    /**
     * Minimal sandbox and permissions per provider. Scripts and same-origin are what
     * every player needs to run; popups let "Open in Spotify" and similar links work.
     */
    private const SANDBOX = 'allow-scripts allow-same-origin allow-popups allow-popups-to-escape-sandbox';

    /**
     * @return array{provider: string, name: string, url: string, src: ?string, height: ?int, aspect: ?string, allow: ?string, sandbox: ?string}|null
     *                                                                                                                                                null when the link is not an http(s) URL on an allow-listed host.
     */
    public static function resolve(?string $url): ?array
    {
        $url = trim((string) $url);
        $parts = parse_url($url);
        if ($url === '' || $parts === false || ! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $provider = self::HOSTS[$host] ?? null;
        if ($provider === null || isset($parts['user']) || isset($parts['port'])) {
            return null;
        }

        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);
        $base = ['provider' => $provider, 'name' => self::NAMES[$provider], 'url' => $url, 'src' => null, 'height' => null, 'aspect' => null, 'allow' => null, 'sandbox' => null];

        $player = match ($provider) {
            'spotify' => self::spotify($path),
            'youtube' => self::youtube($host, $path, $query),
            'apple-music' => self::appleMusic($path, $query),
            'soundcloud' => self::soundcloud($host, $path),
            'tiktok' => self::tiktok($path),
            default => null,
        };

        return $player ? [...$base, ...$player] : $base;
    }

    private static function spotify(string $path): ?array
    {
        if (! preg_match('#^/(?:intl-[a-z]{2}(?:-[a-z]{2})?/)?(?:embed/)?(track|album|playlist|episode|show|artist)/([A-Za-z0-9]{10,40})/?$#i', $path, $m)) {
            return null;
        }
        $type = strtolower($m[1]);

        return [
            'src' => "https://open.spotify.com/embed/{$type}/{$m[2]}",
            'height' => in_array($type, ['album', 'playlist', 'artist', 'show'], true) ? 352 : 152,
            'allow' => 'autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture',
            'sandbox' => self::SANDBOX,
        ];
    }

    /** @param array<string, mixed> $query */
    private static function youtube(string $host, string $path, array $query): ?array
    {
        $id = null;
        if ($host === 'youtu.be' && preg_match('#^/([A-Za-z0-9_-]{11})/?$#', $path, $m)) {
            $id = $m[1];
        } elseif ($path === '/watch' && is_string($query['v'] ?? null) && preg_match('/^[A-Za-z0-9_-]{11}$/', $query['v'])) {
            $id = $query['v'];
        } elseif (preg_match('#^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})/?$#', $path, $m)) {
            $id = $m[1];
        }

        return $id === null ? null : [
            // The privacy-enhanced host sets no cookies until the viewer plays.
            'src' => "https://www.youtube-nocookie.com/embed/{$id}",
            'aspect' => '16/9',
            'allow' => 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen',
            'sandbox' => self::SANDBOX.' allow-presentation',
        ];
    }

    /** @param array<string, mixed> $query */
    private static function appleMusic(string $path, array $query): ?array
    {
        if (! preg_match('#^/[a-z]{2}/(album|playlist|song|music-video)/[^?]*$#i', $path)) {
            return null;
        }
        $song = (is_string($query['i'] ?? null) && ctype_digit($query['i'])) || preg_match('#^/[a-z]{2}/song/#i', $path);
        $keep = array_filter(['i' => is_string($query['i'] ?? null) && ctype_digit($query['i']) ? $query['i'] : null]);

        return [
            'src' => 'https://embed.music.apple.com'.$path.($keep ? '?'.http_build_query($keep) : ''),
            'height' => $song ? 175 : 450,
            'allow' => 'autoplay *; encrypted-media *; clipboard-write',
            'sandbox' => self::SANDBOX.' allow-storage-access-by-user-activation allow-top-navigation-by-user-activation',
        ];
    }

    private static function soundcloud(string $host, string $path): ?array
    {
        if (! preg_match('#^/[A-Za-z0-9_-]+/[A-Za-z0-9_-]+(?:/sets/[A-Za-z0-9_-]+)?/?$|^/[A-Za-z0-9_-]+/sets/[A-Za-z0-9_-]+/?$#', $path)) {
            return null;
        }
        $canonical = 'https://soundcloud.com'.rtrim($path, '/');

        return [
            'src' => 'https://w.soundcloud.com/player/?'.http_build_query(['url' => $canonical, 'auto_play' => 'false', 'hide_related' => 'true', 'show_comments' => 'false', 'show_reposts' => 'false', 'show_teaser' => 'false', 'visual' => 'false']),
            'height' => 166,
            'allow' => 'autoplay',
            'sandbox' => self::SANDBOX,
        ];
    }

    /** Full video links only; vm.tiktok.com short links carry no id and link out instead. */
    private static function tiktok(string $path): ?array
    {
        if (! preg_match('#^/@[A-Za-z0-9._-]+/video/(\d{8,25})/?$#', $path, $m)) {
            return null;
        }

        return [
            'src' => "https://www.tiktok.com/embed/v2/{$m[1]}",
            'aspect' => '9/16',
            'allow' => 'encrypted-media; fullscreen',
            'sandbox' => self::SANDBOX,
        ];
    }
}
