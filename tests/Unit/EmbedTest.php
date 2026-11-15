<?php

use TallUiKit\Support\Embed;

it('resolves the providers React supports to their players', function (string $url, string $provider, string $src) {
    $embed = Embed::resolve($url);

    expect($embed['provider'])->toBe($provider)->and($embed['src'])->toBe($src)->and($embed['sandbox'])->toContain('allow-scripts');
})->with([
    'spotify track' => ['https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC?si=abc', 'spotify', 'https://open.spotify.com/embed/track/4uLU6hMCjMI75M1A2tKUQC'],
    'spotify intl playlist' => ['https://open.spotify.com/intl-fr/playlist/37i9dQZF1DXcBWIGoYBM5M', 'spotify', 'https://open.spotify.com/embed/playlist/37i9dQZF1DXcBWIGoYBM5M'],
    'youtube watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ&t=10', 'youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    'youtu.be' => ['https://youtu.be/dQw4w9WgXcQ', 'youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    'youtube shorts' => ['https://youtube.com/shorts/dQw4w9WgXcQ', 'youtube', 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ'],
    'apple music song' => ['https://music.apple.com/ng/album/second-wind/1234567890?i=1234567891', 'apple-music', 'https://embed.music.apple.com/ng/album/second-wind/1234567890?i=1234567891'],
    'soundcloud' => ['https://soundcloud.com/lagos-nights/second-wind', 'soundcloud', 'https://w.soundcloud.com/player/?url=https%3A%2F%2Fsoundcloud.com%2Flagos-nights%2Fsecond-wind&auto_play=false&hide_related=true&show_comments=false&show_reposts=false&show_teaser=false&visual=false'],
    'tiktok video' => ['https://www.tiktok.com/@lagosnights/video/7312345678901234567', 'tiktok', 'https://www.tiktok.com/embed/v2/7312345678901234567'],
]);

it('sizes players as React does', function () {
    expect(Embed::resolve('https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC')['height'])->toBe(152)
        ->and(Embed::resolve('https://open.spotify.com/album/4uLU6hMCjMI75M1A2tKUQC')['height'])->toBe(352)
        ->and(Embed::resolve('https://music.apple.com/ng/album/x/123')['height'])->toBe(450)
        ->and(Embed::resolve('https://youtu.be/dQw4w9WgXcQ')['aspect'])->toBe('16/9')
        ->and(Embed::resolve('https://www.tiktok.com/@a/video/7312345678901234567')['aspect'])->toBe('9/16');
});

it('recognises Audiomack, Boomplay and short links without a player', function (string $url, string $provider) {
    $embed = Embed::resolve($url);

    expect($embed['provider'])->toBe($provider)->and($embed['src'])->toBeNull()->and($embed['url'])->toBe($url);
})->with([
    ['https://audiomack.com/lagos-nights/song/second-wind', 'audiomack'],
    ['https://www.boomplay.com/songs/123456789', 'boomplay'],
    ['https://vm.tiktok.com/ZMabc123/', 'tiktok'],
    ['https://open.spotify.com/user/abc', 'spotify'],
]);

it('refuses anything off the allow-list, including lookalikes', function (?string $url) {
    expect(Embed::resolve($url))->toBeNull();
})->with([
    'other host' => ['https://evil.example/open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC'],
    'host in query' => ['https://evil.example/?u=https://youtu.be/dQw4w9WgXcQ'],
    'suffix lookalike' => ['https://open.spotify.com.evil.example/track/4uLU6hMCjMI75M1A2tKUQC'],
    'userinfo' => ['https://open.spotify.com@evil.example/track/4uLU6hMCjMI75M1A2tKUQC'],
    'javascript' => ['javascript:alert(1)'],
    'ftp' => ['ftp://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC'],
    'empty' => [''],
    'null' => [null],
]);
