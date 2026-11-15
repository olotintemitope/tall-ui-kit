{{--
    App sidebar (docs/ui-kit.md "Sidebar"). In a shell (`fixed`, the default) it is a
    fixed 240px rail that collapses to a 56px icon rail from lg, plus a mobile drawer
    (a native <dialog>: focus trap, Escape, scrim; open it with the
    `open-sidebar-drawer` window event). With :fixed="false" it is a static block for
    previews, collapsing at every width.
    Props: variant (dark | light | inset), collapsible (true), area (persists the
    collapse as localStorage.<prefix>sidebar_<area>, applied before paint by
    partials/shell/collapse-script; sections persist their open state under the same
    prefix), fixed (true), collapsed (initial state for static previews), label.
    Slots: header (x-ui.sidebar.header), nav (x-ui.sidebar.section, .item, .group),
    footer (x-ui.sidebar.account). Header and footer stay pinned while the nav scrolls.
    Keyboard: Up and Down move between nav items, Home and End jump. Collapse animates
    the width (150ms, ease-motion-in-out; instant under reduced motion), a measured
    exception to the transform-and-opacity rule. Focus mode hides it from lg.
--}}
@props(['variant' => 'light', 'collapsible' => true, 'area' => null, 'fixed' => true, 'collapsed' => false, 'label' => 'Sidebar'])

@php
    $tone = \TallUiKit\Support\SidebarTones::tones($variant);
    $storageKey = $area ? config('tall-ui-kit.storage_prefix', 'tallui_').'sidebar_'.$area : null;
    $state = $fixed && $storageKey
        ? 'collapsed: $persist(false).as('.\Illuminate\Support\Js::from($storageKey).')'
        : 'collapsed: '.\Illuminate\Support\Js::from((bool) $collapsed);
    $edge = $variant === 'light' ? 'border-r border-line' : '';
@endphp

<div
    x-data="{
        {!! $state !!},
        static: @js(! $fixed),
        collapsible: @js((bool) $collapsible),
        railed(el) {
            if (el && el.closest('[data-sidebar-root=drawer]')) return false;
            return this.static ? this.collapsed : this.collapsed && window.matchMedia('(min-width: 1024px)').matches;
        },
        toggle() { if (this.collapsible) this.collapsed = ! this.collapsed },
        expand() { this.collapsed = false },
        openDrawer() { const d = this.$root.querySelector('[data-sidebar-drawer]'); if (d && ! d.open) { d.showModal(); d.querySelector('[aria-current=page], [data-nav-item]')?.focus() } },
        closeDrawer() { this.$root.querySelector('[data-sidebar-drawer]')?.close() },
        moveNav(event, step) {
            const nav = event.currentTarget;
            const items = [...nav.querySelectorAll('[data-nav-item]')].filter((el) => el.getClientRects().length > 0);
            const index = items.indexOf(document.activeElement);
            if (index === -1) return;
            event.preventDefault();
            const next = step === 'first' ? items[0] : step === 'last' ? items.at(-1) : items[(index + step + items.length) % items.length];
            next?.focus();
        },
    }"
    @if ($fixed)
        x-effect="document.documentElement.dataset.sidebar = collapsed ? 'collapsed' : 'expanded'"
        x-on:open-sidebar-drawer.window="openDrawer()"
        x-on:resize.window.debounce.150ms="if (window.matchMedia('(min-width: 1024px)').matches) closeDrawer()"
        class="contents"
    @else
        {{ $attributes->class(['flex']) }}
    @endif
    data-sidebar="{{ $variant }}"
>
    @foreach ($fixed ? ['shell', 'drawer'] : ['static'] as $copy)
        @php
            $drawer = $copy === 'drawer';
            // Previews are not landmarks: a complementary landmark inside <main> is invalid.
            $tag = $drawer ? 'dialog' : ($copy === 'static' ? 'div' : 'aside');
        @endphp
        <{{ $tag }}
            data-sidebar-root="{{ $copy }}"
            @if ($copy === 'static') x-bind:data-collapsed="collapsed ? '' : null" @if ($collapsed) data-collapsed @endif @endif
            @if ($copy === 'shell') data-sidebar-rail @endif
            @if ($drawer)
                data-sidebar-drawer
                aria-label="Menu"
                x-on:click="if ($event.target === $el) $el.close()"
                x-on:click.capture="if ($event.target.closest('a[href]')) $el.close()"
            @elseif ($copy === 'static')
                role="group"
                aria-label="{{ $label }}"
            @else
                aria-label="{{ $label }}"
            @endif
            @class([
                'group/sidebar flex-col',
                $tone['surface'],
                'hidden lg:flex fixed inset-y-0 left-0 z-40 w-60 rail:w-14 transition-[width] duration-150 ease-motion-in-out motion-reduce:transition-none lg:focus-mode:invisible lg:focus-mode:w-0 lg:focus-mode:overflow-hidden lg:focus-mode:border-r-0' => $copy === 'shell',
                'ui-drawer m-0 h-dvh max-h-dvh w-[272px] max-w-[85vw] p-0 open:flex lg:hidden' => $drawer,
                'flex h-[480px] w-60 rail:w-14 shrink-0 overflow-hidden transition-[width] duration-150 ease-motion-in-out motion-reduce:transition-none' => $copy === 'static',
                $edge => $copy !== 'drawer' || $variant === 'light',
            ])
        >
            {{-- The drawer copy is a closed dialog on load: its Alpine starts later (data-ui-defer), at the latest when opening it moves focus inside. --}}
            <div class="flex h-full min-h-0 w-full flex-col" @if ($drawer) data-ui-defer @endif>
                @isset($header)
                    <div class="shrink-0">{{ $header }}</div>
                @endisset

                <nav
                    aria-label="{{ $label }} navigation"
                    x-on:keydown.down="moveNav($event, 1)"
                    x-on:keydown.up="moveNav($event, -1)"
                    x-on:keydown.home="moveNav($event, 'first')"
                    x-on:keydown.end="moveNav($event, 'last')"
                    class="min-h-0 flex-1 overflow-y-auto overscroll-contain no-scrollbar px-3 pb-4 rail:px-2"
                    data-sidebar-nav
                >
                    {{ $nav ?? $slot }}
                </nav>

                @isset($footer)
                    <div @class(['shrink-0 border-t p-2', $tone['line']])>{{ $footer }}</div>
                @endisset
            </div>
        </{{ $tag }}>
    @endforeach
</div>
