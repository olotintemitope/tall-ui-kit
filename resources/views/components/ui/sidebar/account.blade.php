{{--
    The account block pinned at the foot of the sidebar: avatar, name, role and a menu
    (Settings, Log out). With more than one area the same menu switches area, the
    current one marked. In the icon rail only the avatar shows.
    Props: name, email, role (meta line), initials (null: from the name), user (any
    object with name or first_name/last_name, email and initials(); fills the props
    above when they are not given), areas (list of key, label, href), current (area
    key; defaults to the sidebar's area), settingsHref (null hides Settings),
    logoutAction (null: route('logout') when it exists, else no Log out item).
--}}
@aware(['variant' => 'light', 'area' => null])
@props(['user' => null, 'name' => null, 'email' => null, 'role' => null, 'initials' => null, 'settingsHref' => null, 'areas' => [], 'current' => null, 'logoutAction' => null])

@php
    use TallUiKit\Support\SidebarTones;

    $tone = SidebarTones::tones($variant);
    $dark = $variant === 'dark';
    $name ??= $user ? trim((string) ($user->name ?? trim(($user->first_name ?? '').' '.($user->last_name ?? '')))) : '';
    $email ??= $user?->email;
    $role ??= is_object($user?->role ?? null) && method_exists($user->role, 'label') ? $user->role->label() : (is_string($user?->role ?? null) ? $user->role : null);
    $initials ??= $user && method_exists($user, 'initials') ? $user->initials() : null;
    $current ??= $area;
    $logoutAction ??= \Illuminate\Support\Facades\Route::has('logout') ? route('logout') : null;
@endphp

<x-ui.dropdown placement="top-start" width="w-56 max-w-[calc(100vw-2rem)]" class="w-full">
    <x-slot:trigger>
        <button
            type="button"
            aria-label="Account menu for {{ $name }}"
            data-account-block
            @class([
                'flex w-full items-center gap-2.5 rounded-md p-1.5 text-left transition-colors duration-100 focus-ring rail:justify-center',
                'hover:bg-white/[0.06]' => $dark,
                'hover:bg-black/[0.04]' => ! $dark,
            ])
        >
            <x-ui.avatar :name="$name" :initials="$initials" :tone="$dark ? 'dark' : 'light'" />
            <span class="flex min-w-0 flex-1 flex-col rail:hidden">
                <span @class(['wrap-anywhere font-body text-body-strong', $tone['name']])>{{ $name }}</span>
                <span @class(['wrap-anywhere font-body text-caption', $tone['meta']])>{{ $role }}</span>
            </span>
            <x-ui.icon name="chevrons-up-down" :class="\Illuminate\Support\Arr::toCssClasses(['size-4 shrink-0 rail:hidden', $tone['chevron']])" />
        </button>
    </x-slot:trigger>

    <div class="px-2 pt-1.5 pb-2">
        <p class="type-body-strong wrap-anywhere">{{ $name }}</p>
        <p class="type-caption wrap-anywhere">{{ $email }}</p>
    </div>

    @if (count($areas) > 1)
        <x-ui.dropdown.separator />
        <x-ui.dropdown.label>Switch area</x-ui.dropdown.label>
        @foreach ($areas as $option)
            <x-ui.dropdown.item :href="$option['href']" :aria-current="$option['key'] === $current ? 'page' : null" data-area-option="{{ $option['key'] }}">
                <x-ui.icon :name="$option['key'] === $current ? 'check' : 'arrow-left-right'" />
                {{ $option['label'] }}
            </x-ui.dropdown.item>
        @endforeach
    @endif

    @if ($settingsHref || $logoutAction)
        <x-ui.dropdown.separator />
    @endif
    @if ($settingsHref)
        <x-ui.dropdown.item :href="$settingsHref">
            <x-ui.icon name="settings" />
            Settings
        </x-ui.dropdown.item>
    @endif
    @if ($logoutAction)
        <form method="POST" action="{{ $logoutAction }}">
            @csrf
            <x-ui.dropdown.item type="submit">
                <x-ui.icon name="log-out" />
                Log out
            </x-ui.dropdown.item>
        </form>
    @endif
</x-ui.dropdown>
