<?php

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;

beforeEach(fn () => Carbon::setTestNow('2026-11-14 12:00:00'));

it('names the bell with the unread count and caps the pill at 9+', function () {
    expect(Blade::render('<x-ui.notification-bell :count="3" />'))->toContain('aria-label="Notifications, 3 unread"')->toContain('>3<')->toContain('aria-hidden="true"');
    expect(Blade::render('<x-ui.notification-bell :count="27" />'))->toContain('Notifications, 27 unread')->toContain('9+');
    expect(Blade::render('<x-ui.notification-bell />'))->toContain('aria-label="Notifications"')->not->toContain('data-unread-pill');
});

it('shows unread by weight, a dot and screen-reader text, with the full time in a title', function () {
    $html = Blade::render('<x-ui.notification-item id="n1" title="Payout request" message="₦25,000" time="2026-11-14T10:00:00+01:00" type="Wallet" url="/admin/payouts" />');

    expect($html)->toContain('<span class="sr-only">Unread: </span>')->toContain('font-semibold')->toContain('bg-primary-text')
        ->toContain('datetime="2026-11-14T10:00:00+01:00"')->toContain('title="14 Nov 2026, 10:00"')->toContain('2h ago')
        ->toContain('wire:click.prevent="openItem(\'n1\')"')->toContain('href="/admin/payouts"')
        ->toContain('aria-label="Actions for Payout request"')->toContain('Mark as read')->toContain('Delete');

    $read = Blade::render('<x-ui.notification-item id="n2" title="Old" read />');
    expect($read)->not->toContain('Unread:')->toContain('Mark as unread')->toContain('font-medium');
});

it('groups rows under a heading', function () {
    expect(Blade::render('<x-ui.notification-group label="Today"><li>x</li></x-ui.notification-group>'))
        ->toContain('<h3')->toContain('Today')->toContain('aria-labelledby="notification-group-today');
});
