{{--
    Menu anchored to a trigger. Slots: trigger (a button), default (x-ui.dropdown.item,
    x-ui.dropdown.sub, x-ui.dropdown.separator, x-ui.dropdown.label). Props: align
    (right|left, as before), placement (any Alpine anchor placement, e.g. top-start or
    right-end; overrides align). A bottom placement never flips above the trigger: the
    page scrolls to show the whole menu (resources/js/placement.js), width (classes, default min-w-[10rem]).
    Keyboard: Enter, Space or ArrowDown on the trigger opens and focuses the first item;
    ArrowUp/ArrowDown move within the current menu (wrapping), Home/End jump, Escape
    closes every level and returns focus. Submenus (x-ui.dropdown.sub): ArrowRight,
    Enter or Space opens one and focuses its first item, ArrowLeft goes back, hover
    opens on desktop. Below 640px a submenu drills down inside the same panel with a
    Back row. Focus is roving: the focused item has tabindex 0, the rest -1. Focus lost
    because a level was hidden does not close the menu; Tab, Escape or a click outside does.
--}}
@props(['align' => 'right', 'width' => 'min-w-[10rem]', 'placement' => null])

@php
    $placement = $placement ?? ($align === 'right' ? 'bottom-end' : 'bottom-start');
    $placement = in_array($placement, ['top', 'top-start', 'top-end', 'right', 'right-start', 'right-end', 'bottom', 'bottom-start', 'bottom-end', 'left', 'left-start', 'left-end'], true) ? $placement : 'bottom-end';
    // A menu placed below stays below (.noflip); the page scrolls to show it instead.
    $flip = str_starts_with($placement, 'bottom') ? '.noflip' : '';
@endphp

<div
    x-data="{
        open: false,
        menuOf(el) { return el?.closest('[role=menu]') ?? this.$refs.panel },
        itemsIn(menu) {
            return [...menu.querySelectorAll('[role^=menuitem]:not([disabled])')]
                .filter((item) => item.closest('[role=menu]') === menu && item.getClientRects().length > 0);
        },
        items() { return this.itemsIn(this.$refs.panel) },
        focusItem(item) {
            if (! item) return;
            this.$refs.panel.querySelectorAll('[role^=menuitem]').forEach((el) => el.setAttribute('tabindex', el === item ? '0' : '-1'));
            item.focus();
        },
        focusFirst(menu, last = false) {
            const items = this.itemsIn(menu);
            const real = items.filter((item) => ! item.hasAttribute('data-submenu-back'));
            this.focusItem(last ? items.at(-1) : (real[0] ?? items[0]));
        },
        show(focus = 'first') {
            this.open = true;
            if (focus) this.$nextTick(() => this.focusFirst(this.$refs.panel, focus === 'last'));
        },
        close(returnFocus = true) {
            if (! this.open) return;
            this.open = false;
            if (returnFocus) this.$refs.trigger.querySelector('button, a, [tabindex]')?.focus();
        },
        move(step) {
            const items = this.itemsIn(this.menuOf(document.activeElement));
            const index = items.indexOf(document.activeElement);
            this.focusItem(items[(index + step + items.length) % items.length]);
        },
        jump(last) { this.focusFirst(this.menuOf(document.activeElement), last) },
    }"
    x-init="$nextTick(() => { const button = $refs.trigger.querySelector('button, a, [tabindex]'); if (button) { button.id ||= $id('menu-button'); button.setAttribute('aria-haspopup', 'menu'); button.setAttribute('aria-controls', $refs.panel.id); $refs.panel.setAttribute('aria-labelledby', button.id); $watch('open', (value) => button.setAttribute('aria-expanded', value ? 'true' : 'false')); button.setAttribute('aria-expanded', 'false') } })"
    x-on:keydown.escape="if (open) { $event.preventDefault(); $event.stopPropagation(); close() }"
    x-on:focusout="if (open && ! $el.contains($event.relatedTarget) && ! ($event.relatedTarget === null && $event.target.getClientRects().length === 0)) close(false)"
    {{ $attributes->class(['relative']) }}
>
    <div
        x-ref="trigger"
        class="contents"
        x-on:click="open ? close(false) : show(null)"
        x-on:keydown.down.prevent="show('first')"
        x-on:keydown.up.prevent="show('last')"
        x-on:keydown.enter="if ($event.target.matches('button')) { $event.preventDefault(); show('first') }"
        x-on:keydown.space="if ($event.target.matches('button')) { $event.preventDefault(); show('first') }"
    >
        {{ $trigger }}
    </div>

    <div
        x-ref="panel"
        x-bind:id="$id('menu')"
        x-show="open"
        x-cloak
        x-anchor.{{ $placement }}{{ $flip }}.offset.6="$refs.trigger.firstElementChild"
        x-effect="open && $revealBelow($el, $refs.trigger.firstElementChild)"
        x-transition:enter="transition ease-motion-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        x-on:click.outside="close(false)"
        x-on:keydown.down.prevent="move(1)"
        x-on:keydown.up.prevent="move(-1)"
        x-on:keydown.home.prevent="jump(false)"
        x-on:keydown.end.prevent="jump(true)"
        x-on:keydown.tab="close(false)"
        x-on:click="if ($event.target.closest('[role^=menuitem]:not([aria-haspopup]):not([data-submenu-back]):not([data-keep-open])')) close()"
        role="menu"
        @class([
            'z-50 rounded-lg border border-line bg-popover p-1 text-popover-foreground shadow-popover',
            // Phones: an open submenu replaces its siblings (drill-down).
            'max-sm:[&:has(>[data-submenu][data-open])>:not([data-submenu][data-open])]:hidden',
            $width,
        ])
    >
        {{ $slot }}
    </div>
</div>
