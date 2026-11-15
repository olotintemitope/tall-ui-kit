{{--
    Submenu inside x-ui.dropdown: a menuitem with aria-haspopup and a right chevron
    that opens a nested role="menu". Props: label, hint (meta text before the chevron;
    also a named slot for live text), width (panel classes, default min-w-[12rem]).
    Slot: the submenu's items (x-ui.dropdown.item, .separator, .label, nested .sub).
    Desktop: flies out to the right, flipping left when it would cross the viewport
    edge; opens on hover, click, Enter, Space or ArrowRight; ArrowLeft closes it and
    returns focus to its trigger. Below 640px it drills down inside the same panel:
    the siblings hide and a Back row leads up a level.
--}}
@props(['label', 'hint' => null, 'width' => 'min-w-[12rem]'])

<div
    data-submenu
    x-data="{
        sub: false,
        flip: false,
        hoverTimer: null,
        get mobile() { return window.matchMedia('(max-width: 639.98px)').matches },
        openSub(focus = false) {
            clearTimeout(this.hoverTimer);
            [...this.$el.parentElement.children].forEach((sibling) => {
                if (sibling !== this.$el && sibling.matches('[data-submenu]')) window.Alpine.$data(sibling).closeSub(false);
            });
            if (! this.mobile) {
                const rect = this.$el.getBoundingClientRect();
                const width = Math.max(this.$refs.subpanel.offsetWidth, 192);
                this.flip = rect.right + width + 8 > window.innerWidth && rect.left - width - 8 >= 0;
            }
            this.sub = true;
            if (focus) this.$nextTick(() => this.focusFirst(this.$refs.subpanel));
        },
        closeSub(focusTrigger = true) {
            clearTimeout(this.hoverTimer);
            if (! this.sub) return;
            this.$el.querySelectorAll('[data-submenu]').forEach((child) => window.Alpine.$data(child).closeSub(false));
            // Move focus before the panel hides, or the menu's focusout would close every level.
            if (focusTrigger) this.focusItem(this.$refs.subtrigger);
            this.sub = false;
            // Phones: the trigger is hidden while drilled in, so it can only take focus once shown.
            if (focusTrigger) this.$nextTick(() => { if (document.activeElement !== this.$refs.subtrigger) this.focusItem(this.$refs.subtrigger) });
        },
        hover(entering) {
            if (this.mobile || ! window.matchMedia('(hover: hover)').matches) return;
            clearTimeout(this.hoverTimer);
            this.hoverTimer = setTimeout(() => entering ? this.openSub(false) : this.closeSub(false), entering ? 80 : 160);
        },
    }"
    x-effect="if (! open) { sub = false; flip = false }"
    x-bind:data-open="sub ? 'true' : null"
    x-on:mouseenter="hover(true)"
    x-on:mouseleave="hover(false)"
    {{ $attributes->class(['relative']) }}
>
    <button
        type="button"
        role="menuitem"
        tabindex="-1"
        aria-haspopup="menu"
        aria-expanded="false"
        x-bind:aria-expanded="sub ? 'true' : 'false'"
        x-ref="subtrigger"
        data-submenu-trigger
        x-on:click.prevent="sub ? closeSub(false) : openSub(true)"
        x-on:keydown.right.prevent.stop="openSub(true)"
        x-on:keydown.enter.prevent.stop="openSub(true)"
        x-on:keydown.space.prevent.stop="openSub(true)"
        @class([
            'relative flex w-full min-h-8 py-1.5 select-none items-center gap-2 wrap-anywhere rounded-md px-2 font-body text-body text-left text-ink outline-none transition-colors duration-100',
            'hover:bg-surface-subtle focus:bg-surface-subtle focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus',
            'disabled:cursor-not-allowed disabled:bg-transparent disabled:text-ink-muted [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
            'max-sm:[[data-open]>&]:hidden',
        ])
        x-bind:class="sub && 'bg-surface-subtle'"
    >
        <span>{{ $label }}</span>
        <span class="ml-auto flex items-center gap-1 pl-4">
            @if (filled($hint))
                <span class="type-meta">{{ $hint }}</span>
            @endif
            <x-ui.icon name="chevron-right" class="text-ink-muted" />
        </span>
    </button>

    <div
        x-ref="subpanel"
        x-show="sub"
        x-cloak
        role="menu"
        aria-label="{{ $label }}"
        x-on:keydown.left.prevent.stop="closeSub(true)"
        x-bind:class="flip ? 'sm:right-full sm:mr-1' : 'sm:left-full sm:ml-1'"
        @class([
            'z-50 sm:absolute sm:-top-1 sm:w-max sm:rounded-lg sm:border sm:border-line sm:bg-popover sm:p-1 sm:shadow-popover',
            'max-sm:[&:has(>[data-submenu][data-open])>:not([data-submenu][data-open])]:hidden',
            $width,
        ])
    >
        <button
            type="button"
            role="menuitem"
            tabindex="-1"
            data-submenu-back
            x-on:click.prevent="closeSub(true)"
            class="flex w-full min-h-8 py-1.5 select-none items-center gap-2 rounded-md px-2 font-body text-body text-left text-ink-muted outline-none transition-colors duration-100 hover:bg-surface-subtle hover:text-ink focus:bg-surface-subtle focus:text-ink focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus sm:hidden [&_svg]:size-4"
        >
            <x-ui.icon name="chevron-left" />
            Back
        </button>
        <div role="separator" class="-mx-1 my-1 h-px bg-line sm:hidden"></div>
        {{ $slot }}
    </div>
</div>
