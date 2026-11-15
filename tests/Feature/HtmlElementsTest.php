<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use TallUiKit\Support\Format;

/*
 * HTML5 form, interactive, text and media components (docs/ui-kit-html.md). Behaviour
 * (keyboard, popover, audio) is checked in the browser; these pin the markup contract.
 */

describe('interactive', function () {
    it('builds disclosures and accordions on details and summary', function () {
        $html = Blade::render('<x-ui.accordion exclusive name="faq"><x-ui.accordion.item summary="One" open>A</x-ui.accordion.item><x-ui.accordion.item summary="Two">B</x-ui.accordion.item></x-ui.accordion>');

        expect($html)->toContain('<details')->toContain('<summary')->toContain('ui-summary')->toContain('ui-chevron')->toContain('x-collapse')
            ->toContain('data-group="faq"')->toContain('border-b border-line')->toContain('uiDisclosure(true)')->toContain('uiDisclosure(false)');
        expect(substr_count($html, ' open '))->toBeGreaterThanOrEqual(1);
    });

    it('uses the Popover API with an Alpine fallback', function () {
        $html = Blade::render('<x-ui.popover label="Curator" title="Tunde">Body</x-ui.popover>');
        expect($html)->toContain('x-data="uiPopover"')->toContain(' popover')->toContain('ui-popover')->toContain('role="dialog"')->toContain('x-anchor.bottom-start');
        expect(file_get_contents(kit_path('resources/js/forms.js')))->toContain("setAttribute('popovertarget'")->toContain("hasOwnProperty.call(HTMLElement.prototype, 'popover')");
    });

    it('styles native progress and meter with text beside them', function () {
        expect(Blade::render('<x-ui.progress label="Upload" :value="62" />'))->toContain('<progress')->toContain('value="62"')->toContain('62%')->toContain('<label for=');
        expect(Blade::render('<x-ui.progress label="Checking" />'))->not->toContain('value=')->toContain('ui-progress-segment')->toContain('data-progress="indeterminate"');

        expect(Blade::render('<x-ui.meter label="Acceptance" :value="72" :low="30" :high="60" :optimum="100" />'))
            ->toContain('<meter')->toContain('low="30"')->toContain('optimum="100"')->toContain('72%')->toContain('Good')->toContain('data-meter-level="optimum"');
        expect(Blade::render('<x-ui.meter label="Budget" :value="92" :low="50" :high="85" :optimum="0" />'))->toContain('Poor')->toContain('even-less-good');
    });
});

describe('text and structure', function () {
    it('prints dates in time elements, absolute or relative', function () {
        Carbon::setTestNow('2026-11-14 12:00:00');
        $now = '2026-11-14T12:00:00+01:00';

        expect(Blade::render('<x-ui.time value="2026-11-28" />'))->toContain('<time datetime="2026-11-28T')->toContain('>28 Nov 2026</time>');
        expect(Blade::render('<x-ui.time value="2026-11-14T09:40:00+01:00" mode="relative" now="'.$now.'" />'))->toContain('2h ago')->toContain('title="14 Nov 2026, 09:40"');
        expect(Blade::render('<x-ui.time :value="null" />'))->toContain('&ndash;');
        expect(Format::relative(Carbon::parse('2026-11-16 12:00', 'Africa/Lagos'), Carbon::parse('2026-11-14 12:00', 'Africa/Lagos')))->toBe('in 2 days');
    });

    it('renders description lists and the breadcrumb', function () {
        expect(Blade::render('<x-ui.dl layout="inline" :items="[[\'label\' => \'Paid\', \'value\' => \'₦5\', \'numeric\' => true]]" />'))
            ->toContain('<dl')->toContain('<dt class="type-meta">Paid</dt>')->toContain('type-numeric');

        $crumbs = Blade::render('<x-ui.breadcrumb :items="[[\'label\' => \'Admin\', \'href\' => \'/admin\'], [\'label\' => \'Campaigns\', \'href\' => \'/c\'], [\'label\' => \'Second Wind\']]" />');
        expect($crumbs)->toContain('<nav aria-label="Breadcrumb"')->toContain('<ol')->toContain('aria-current="page"')->toContain('max-sm:hidden')->toContain('aria-hidden="true"');
    });

    it('gives x-ui.table a caption, colgroup, row headers, a tfoot and a sticky head', function () {
        $html = Blade::render('<x-ui.table caption="Placements" caption-visible sticky><x-slot:colgroup><col class="w-28"></x-slot:colgroup><x-slot:head><x-ui.table.th>Song</x-ui.table.th></x-slot:head><tr><x-ui.table.th row>Second Wind</x-ui.table.th></tr><x-slot:foot><tr><x-ui.table.th row>Total</x-ui.table.th></tr></x-slot:foot></x-ui.table>');

        expect($html)->toContain('<caption class="type-label')->toContain('<colgroup')->toContain('scope="col"')->toContain('scope="row"')
            ->toContain('<tfoot')->toContain('sticky z-10')->toContain('max-h-[32rem]')->toContain('data-table-sticky');
        expect(Blade::render('<x-ui.table.th scope="colgroup">G</x-ui.table.th>'))->toContain('scope="colgroup"')->not->toContain('scope="col"');
    });
});

describe('media', function () {
    it('wraps audio in a preview player with toggles, a seek range and preload none', function () {
        $html = Blade::render('<x-ui.audio src="/samples/preview.mp3" title="Second Wind" artist="Lagos Nights" :duration="12" />');

        expect($html)->toContain('preload="none" src="/samples/preview.mp3"')->toContain('aria-label="Play Second Wind"')->toContain('aria-pressed="false"')
            ->toContain('aria-label="Seek Second Wind"')->toContain('aria-valuetext="0:00 of 0:12"')->toContain('aria-label="Mute Second Wind"')
            ->toContain('Preview unavailable.')->toContain('x-bind:aria-busy')->not->toContain('gradient');
        expect(file_get_contents(kit_path('resources/js/forms.js')))->toContain("const AUDIO_EVENT = 'ui-audio-play'");
    });

    it('renders video with controls, a fixed aspect and tracks', function () {
        expect(Blade::render('<x-ui.video src="/v.mp4" poster="/p.jpg" title="Teaser"><track kind="captions" src="/c.vtt" srclang="en" label="English"></x-ui.video>'))
            ->toContain('<video')->toContain('controls')->toContain('poster="/p.jpg"')->toContain('aspect-video')->toContain('rounded-lg')->toContain('<track kind="captions"');
    });

    it('requires alt text on figures unless decorative, and lazy loads them', function () {
        $html = Blade::render('<x-ui.figure src="/a.jpg" alt="Cover" caption="Cap" srcset="/a.jpg 640w" :sources="[[\'srcset\' => \'/a.webp\', \'type\' => \'image/webp\']]" />');
        expect($html)->toContain('<figure')->toContain('<picture>')->toContain('type="image/webp"')->toContain('loading="lazy"')->toContain('decoding="async"')
            ->toContain('alt="Cover"')->toContain('<figcaption')->toContain('aspect-video');
        expect(Blade::render('<x-ui.figure src="/a.jpg" decorative />'))->toContain('alt=""');
        expect(fn () => Blade::render('<x-ui.figure src="/a.jpg" />'))->toThrow(Exception::class);
    });

    it('puts embeds behind a facade with title, lazy loading, allow and sandbox', function () {
        $html = Blade::render('<x-ui.embed url="https://open.spotify.com/track/4uLU6hMCjMI75M1A2tKUQC" title="Second Wind" />');
        expect($html)->toContain('x-data="uiEmbed"')->toContain('Load Spotify player')->toContain('<template x-if="loaded">')
            ->toContain('src="https://open.spotify.com/embed/track/4uLU6hMCjMI75M1A2tKUQC"')->toContain('title="Second Wind on Spotify"')
            ->toContain('loading="lazy"')->toContain('sandbox="allow-scripts allow-same-origin allow-popups')->toContain('height: 152px');

        expect(Blade::render('<x-ui.embed url="https://audiomack.com/lagos-nights/song/second-wind" />'))->toContain('data-embed-link')->toContain('Open on Audiomack')->not->toContain('<iframe');
        expect(Blade::render('<x-ui.embed url="javascript:alert(1)" />'))->not->toContain('javascript:')->not->toContain('href=');
    });
});

it('styles the native states and print in app.css', function () {
    $css = file_get_contents(kit_path('resources/css/tall-ui-kit.css'));

    expect($css)->toContain(':user-invalid')->toContain(':read-only')->toContain('::selection')->toContain('accent-color')
        ->toContain('input:autofill')->toContain('::-webkit-inner-spin-button')->toContain('::-webkit-search-cancel-button')
        ->toContain('--picker-icon-calendar')->toContain('@media print')->toContain("content: ' (' attr(href) ')'")
        ->toContain('.ui-range::-webkit-slider-thumb')->toContain('width: 24px')->toContain('.ui-meter::-webkit-meter-optimum-value');
});
