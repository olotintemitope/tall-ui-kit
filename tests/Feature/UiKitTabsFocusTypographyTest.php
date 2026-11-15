<?php

use TallUiKit\Support\Format;

beforeEach(function () {
    $this->withoutVite();
});

describe('tabs', function () {
    it('renders a tablist with roles, roving tabindex, keyboard handlers and panels', function () {
        $html = (string) $this->blade('<x-ui.tabs label="Campaign" :tabs="[\'a\' => \'General\', \'b\' => \'Queue\']" active="b"><x-ui.tabs.panel name="a">A</x-ui.tabs.panel><x-ui.tabs.panel name="b">B</x-ui.tabs.panel></x-ui.tabs>');

        expect($html)->toContain('data-tabs="tablist"')
            ->toContain('role="tablist"')
            ->toContain('aria-label="Campaign"')
            ->toContain('role="tabpanel"')
            ->toContain('x-bind:aria-controls=')
            ->toContain('x-bind:aria-labelledby=')
            ->toContain('x-on:keydown.right.prevent="focusTab(1)"')
            ->toContain('x-on:keydown.left.prevent="focusTab(-1)"')
            ->toContain('x-on:keydown.home.prevent="focusTab(\'first\')"')
            ->toContain('x-on:keydown.end.prevent="focusTab(\'last\')"')
            ->toContain('[role=tab]:not(:disabled)')
            ->toContain('h-10')
            ->not->toContain('aria-current');

        expect($html)->toMatch('/data-tab="a"(?:(?!>).)*aria-selected="false"(?:(?!>).)*tabindex="-1"/s')
            ->toMatch('/data-tab="b"(?:(?!>).)*aria-selected="true"(?:(?!>).)*tabindex="0"/s')
            ->and(substr_count($html, 'role="tab"'))->toBe(2);
    });

    it('renders icon tabs with a 16px icon before the label, counts and attention badges', function () {
        $html = (string) $this->blade('<x-ui.tabs size="sm" :tabs="[\'inbox\' => [\'label\' => \'Inbox\', \'icon\' => \'inbox\', \'count\' => 1204], \'late\' => [\'label\' => \'Late\', \'icon\' => \'bell\', \'count\' => 3, \'badge\' => \'danger\'], \'old\' => [\'label\' => \'Archived\', \'icon\' => \'file\', \'disabled\' => true]]" />');

        expect($html)->toContain('h-9')
            ->toContain('gap-1.5')
            ->toContain('class="shrink-0 size-4"')
            ->toContain('<span class="type-numeric" data-tab-count>1,204</span>')
            ->toContain('bg-danger-tint text-danger-text')
            ->toMatch('/data-tab="old"[^>]*disabled/s')
            ->not->toContain('rounded-full px-3')
            ->not->toContain('bg-primary')
            ->not->toContain('border-b-2');

        expect(strpos($html, '<svg'))->toBeLessThan(strpos($html, 'Inbox'));
    });

    it('renders link tabs as navigation with aria-current on the active page', function () {
        $html = (string) $this->blade('<x-ui.tabs label="Queue" active="mine" :tabs="[\'all\' => [\'label\' => \'All\', \'href\' => \'/admin/q\'], \'mine\' => [\'label\' => \'Mine\', \'href\' => \'/admin/q?mine=1\']]" />');

        expect($html)->toContain('<nav')
            ->toContain('data-tabs="links"')
            ->toContain('aria-label="Queue"')
            ->toMatch('/href="\/admin\/q\?mine=1"\s+aria-current="page"/')
            ->not->toContain('role="tab"')
            ->not->toContain('role="tablist"')
            ->and(substr_count($html, 'aria-current="page"'))->toBe(1);
    });

    it('only points aria-controls at a panel that exists', function () {
        $html = (string) $this->blade('<x-ui.tabs :tabs="[\'a\' => \'A\']"><x-ui.tabs.panel name="a">A</x-ui.tabs.panel></x-ui.tabs>');

        expect($html)->toContain('data-tab-panel="a"')
            ->toContain('hasPanel(')
            ->toContain('x-bind:aria-controls="hasPanel(');
    });

    it('builds stat skeleton cells as dt and dd pairs inside the strip', function () {
        $html = (string) $this->blade('<x-ui.skeleton.stats :count="2" />');

        expect(substr_count($html, '<dt class="sr-only">Loading</dt>'))->toBe(2)
            ->and(substr_count($html, '<dd aria-hidden="true">'))->toBe(2);
    });

    it('throws in testing when icon and plain tabs are mixed', function () {
        $this->blade('<x-ui.tabs :tabs="[\'a\' => [\'label\' => \'Inbox\', \'icon\' => \'inbox\'], \'b\' => \'Sent\']" />');
    })->throws(Exception::class, 'give every tab an icon or none');

    it('throws in testing when link and plain tabs are mixed', function () {
        $this->blade('<x-ui.tabs :tabs="[\'a\' => [\'label\' => \'All\', \'href\' => \'/a\'], \'b\' => \'Mine\']" />');
    })->throws(Exception::class, 'give every tab an href or none');
});

describe('Format', function () {
    it('compacts counts', function (int|float|null $value, string $expected) {
        expect(Format::compact($value))->toBe($expected);
    })->with([
        [0, '0'],
        [950, '950'],
        [1000, '1k'],
        [1240, '1.2k'],
        [12000, '12k'],
        [999950, '1M'],
        [3412000, '3.4M'],
        [2100000000, '2.1B'],
        [-1500, "\u{2212}1.5k"],
        [null, "\u{2013}"],
    ]);

    it('formats percentages and grouped numbers', function () {
        expect(Format::percent(0.1234))->toBe('12.3%')
            ->and(Format::percent(0.5))->toBe('50%')
            ->and(Format::percent(1))->toBe('100%')
            ->and(Format::percent(-0.021))->toBe("\u{2212}2.1%")
            ->and(Format::percent(0.12345, 2))->toBe('12.35%')
            ->and(Format::number(1204))->toBe('1,204')
            ->and(Format::number(1204.5, 1))->toBe('1,204.5')
            ->and(Format::number('n/a'))->toBe("\u{2013}");
    });

    it('formats money exactly as x-ui.money does', function (int|float $value, string $currency, string $expected) {
        expect(Format::money($value, $currency))->toBe($expected);

        $this->blade('<x-ui.money :value="$value" :currency="$currency" />', ['value' => $value, 'currency' => $currency])->assertSee($expected);
    })->with([
        [4281500, 'NGN', '₦4,281,500'],
        [1250.5, 'NGN', '₦1,250.5'],
        [1250, 'USD', '$1,250.00'],
        [-12.5, 'USD', '-$12.50'],
    ]);
});
