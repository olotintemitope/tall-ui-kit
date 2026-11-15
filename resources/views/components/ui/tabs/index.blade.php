{{--
    Underline tabs. Three forms, one look: text, icon with label (16px icon, 6px gap,
    icon follows the text colour) and either with a count. The active tab is ink text
    with a 1px ink underline over the 1px `line` rule; inactive tabs are ink-muted and
    turn ink on hover (no box), hovering the selected tab changes nothing, keyboard focus
    is a 2px `focus` outline 4px inside the tab (shown with the underline when both apply), disabled
    tabs keep ink-muted text (5.86:1) with a dashed control underline on the label and
    aria-disabled and skipped by the arrow keys, and a plain count
    follows the tab's colour. Never icon-only (use x-ui.segmented), never boxed, filled or orange.

    Props: tabs (key => label, or key => ['label' => required, 'icon' => Lucide name,
    'count' => int, 'badge' => neutral|danger (show the count as a small badge, only
    when it needs attention), 'disabled' => bool, 'href' => url, 'navigate' => bool (wire:navigate on a link tab), 'force' => hover|focus
    (draws that state statically, for the /admin/ui states matrix)]), active (the
    initial key without a binding; for link tabs the current page), size (default
    h-10 | sm h-9 for card headers), label (accessible name of the tab list).

    Without href: an ARIA tablist with roving tabindex, Arrow, Home and End keys and
    automatic activation. Bind with wire:model / x-model (x-modelable) and put
    x-ui.tabs.panel elements in the slot. With href on every tab: navigation links
    in a <nav>, the active one with aria-current="page".

    Either every tab has an icon or none does, and either every tab has an href or
    none does; mixing throws in local and testing. Narrow screens scroll the row
    sideways (no fade, no arrows) and the active tab scrolls into view.
--}}
@props(['tabs' => [], 'active' => null, 'size' => 'default', 'label' => null])

@php
    $normalised = collect($tabs)->map(function ($tab, $key): array {
        $tab = is_array($tab) ? $tab : ['label' => (string) $tab];

        return [
            'key' => (string) $key,
            'label' => (string) ($tab['label'] ?? $tab[0] ?? $key),
            'icon' => $tab['icon'] ?? null,
            'count' => $tab['count'] ?? null,
            'badge' => in_array($tab['badge'] ?? null, ['neutral', 'danger'], true) ? $tab['badge'] : null,
            'disabled' => (bool) ($tab['disabled'] ?? false),
            'href' => $tab['href'] ?? null,
            'navigate' => (bool) ($tab['navigate'] ?? false),
            'force' => in_array($tab['force'] ?? null, ['hover', 'active', 'focus'], true) ? $tab['force'] : null,
        ];
    })->values();

    if (app()->environment('local', 'testing')) {
        $withIcons = $normalised->filter(fn (array $tab): bool => filled($tab['icon']))->count();
        if ($withIcons > 0 && $withIcons < $normalised->count()) {
            throw new InvalidArgumentException('x-ui.tabs: give every tab an icon or none. Mixed tabs: '.$normalised->pluck('key')->implode(', '));
        }

        $withHref = $normalised->filter(fn (array $tab): bool => filled($tab['href']))->count();
        if ($withHref > 0 && $withHref < $normalised->count()) {
            throw new InvalidArgumentException('x-ui.tabs: give every tab an href or none. Mixed tabs: '.$normalised->pluck('key')->implode(', '));
        }
    }

    $links = $normalised->contains(fn (array $tab): bool => filled($tab['href']));
    $enabled = $normalised->reject(fn (array $tab): bool => $tab['disabled']);
    $initial = $active !== null
        ? (string) $active
        : ($links
            ? ($normalised->first(fn (array $tab): bool => filled($tab['href']) && url($tab['href']) === request()->url())['key'] ?? null)
            : ($enabled->first()['key'] ?? $normalised->first()['key'] ?? null));

    $tabClasses = [
        'relative -mb-px inline-flex shrink-0 items-center gap-1.5 border-b border-transparent px-2 font-body text-body-strong text-ink-muted transition-colors duration-100 motion-reduce:transition-none',
        'focus-ring-inset',
        $size === 'sm' ? 'h-9' : 'h-10',
    ];
@endphp

@if ($links)
    <nav
        x-data
        x-init="requestAnimationFrame(() => { const current = $el.querySelector('[aria-current=page]'); if (current) $el.scrollLeft = Math.max(0, current.offsetLeft - ($el.clientWidth - current.offsetWidth) / 2) })"
        @if ($label) aria-label="{{ $label }}" @endif
        data-tabs="links"
        {{ $attributes->class(['relative overflow-x-auto no-scrollbar']) }}
    >
        {{-- The rule sits on an inner row, not the scroll container, so the active underline (-1px over it) is never clipped. --}}
        <div class="flex w-max min-w-full gap-1 border-b border-line">
@else
    <div
        x-data="{
            tab: @js($initial),
            tabs() { return [...this.$refs.list.querySelectorAll('[role=tab]:not(:disabled)')] },
            hasPanel(key) { return [...this.$root.querySelectorAll('[data-tab-panel]')].some((panel) => panel.dataset.tabPanel === key) },
            focusTab(step) {
                const tabs = this.tabs();
                if (! tabs.length) return;
                const index = tabs.indexOf(document.activeElement);
                const next = step === 'first' ? tabs[0] : step === 'last' ? tabs[tabs.length - 1] : tabs[(index + step + tabs.length) % tabs.length];
                next.focus();
                this.tab = next.dataset.tab;
            },
            reveal() {
                const list = this.$refs.list;
                const current = list.querySelector('[aria-selected=true]');
                if (! current) return;
                if (current.offsetLeft < list.scrollLeft) list.scrollLeft = current.offsetLeft;
                else if (current.offsetLeft + current.offsetWidth > list.scrollLeft + list.clientWidth) list.scrollLeft = current.offsetLeft + current.offsetWidth - list.clientWidth;
            },
        }"
        x-modelable="tab"
        x-id="['tabs']"
        x-init="requestAnimationFrame(() => reveal()); $watch('tab', () => $nextTick(() => reveal()))"
        data-tabs="tablist"
        {{ $attributes }}
    >
        <div x-ref="list" class="relative overflow-x-auto no-scrollbar">
        <div
            role="tablist"
            @if ($label) aria-label="{{ $label }}" @endif
            class="flex w-max min-w-full gap-1 border-b border-line"
            x-on:keydown.right.prevent="focusTab(1)"
            x-on:keydown.left.prevent="focusTab(-1)"
            x-on:keydown.home.prevent="focusTab('first')"
            x-on:keydown.end.prevent="focusTab('last')"
        >
@endif
    @foreach ($normalised as $item)
        @php
            $selected = $initial === $item['key'];
            $tag = $links ? ($item['disabled'] ? 'span' : 'a') : 'button';
        @endphp
        <{{ $tag }}
            @if ($links)
                @if ($item['disabled'])
                    aria-disabled="true"
                @else
                    href="{{ $item['href'] }}"
                    @if ($item['navigate']) wire:navigate @endif
                    @if ($selected) aria-current="page" @endif
                @endif
            @else
                type="button"
                role="tab"
                data-tab="{{ $item['key'] }}"
                x-bind:id="$id('tabs', @js($item['key']))"
                x-bind:aria-selected="tab === @js($item['key']) ? 'true' : 'false'"
                x-bind:tabindex="tab === @js($item['key']) ? 0 : -1"
                x-bind:aria-controls="hasPanel(@js($item['key'])) ? $id('tabs', @js($item['key']) + '-panel') : null"
                x-on:click="tab = @js($item['key'])"
                aria-selected="{{ $selected ? 'true' : 'false' }}"
                tabindex="{{ $selected ? 0 : -1 }}"
                @if ($item['disabled']) disabled aria-disabled="true" @endif
            @endif
            @if ($item['force']) data-force="{{ $item['force'] }}" @endif
            @class([
                ...$tabClasses,
                'state-hover:text-ink aria-[current=page]:border-secondary aria-[current=page]:text-ink' => $links && ! $item['disabled'],
                'state-hover:text-ink aria-selected:border-secondary aria-selected:text-ink disabled:cursor-not-allowed disabled:underline disabled:decoration-control disabled:decoration-dashed disabled:underline-offset-4' => ! $links,
                'cursor-not-allowed underline decoration-control decoration-dashed underline-offset-4' => $links && $item['disabled'],
            ])
        >
            @if ($item['icon'])
                <x-ui.icon :name="$item['icon']" class="size-4" />
            @endif
            {{ $item['label'] }}
            @if ($item['count'] !== null)
                @if ($item['badge'])
                    <x-ui.badge :variant="$item['badge']" size="sm" class="tabular-nums" data-tab-count>{{ number_format($item['count']) }}</x-ui.badge>
                @else
                    <span class="type-numeric" data-tab-count>{{ number_format($item['count']) }}</span>
                @endif
            @endif
        </{{ $tag }}>
    @endforeach
@if ($links)
        </div>
    </nav>
@else
        </div>
        </div>

        {{ $slot }}
    </div>
@endif
