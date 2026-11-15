{{--
    x-data-table.row-menu: one action menu shared by every row (a per-row x-ui.dropdown
    would add about 1.5 KB of markup to each row). Each row's trigger carries the keys of
    the actions allowed for it (ability and visible() checked on the server) and the
    hrefs of link actions; this menu shows those items under the trigger. Same look and
    keyboard as x-ui.dropdown: arrows, Home, End, Escape (focus back to the trigger), Tab
    closes. Actions with a confirm open an x-ui.confirm-dialog. Props: table (TableView).
--}}
@props(['table'])

<div
    x-ref="rowMenu"
    x-show="menu.open"
    x-cloak
    x-bind:style="menu.style"
    x-on:click.outside="if (! menu.trigger || ! menu.trigger.contains($event.target)) closeMenu(false)"
    x-on:keydown.escape.prevent.stop="closeMenu(true)"
    x-on:keydown.down.prevent="menuMove(1)"
    x-on:keydown.up.prevent="menuMove(-1)"
    x-on:keydown.home.prevent="menuItems()[0]?.focus()"
    x-on:keydown.end.prevent="menuItems().at(-1)?.focus()"
    x-on:keydown.tab="closeMenu(false)"
    x-transition:enter="transition ease-out duration-100 motion-reduce:transition-none"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    role="menu"
    x-bind:aria-label="menu.label"
    class="fixed z-50 w-max min-w-48 max-w-[calc(100vw-1rem)] rounded-lg border border-line bg-popover p-1 shadow-popover"
    wire:key="{{ $table->name }}-row-menu"
    data-dt-row-menu
>
    @foreach ($table->rowActions as $action)
        @php
            $classes = [
                'flex min-h-8 w-full items-center gap-2 rounded-md px-2 py-1.5 text-left font-body text-body outline-none transition-colors duration-100 focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-focus [&_svg]:size-4',
                'text-danger-text hover:bg-danger-tint focus:bg-danger-tint' => $action->destructive,
                'text-ink hover:bg-surface-subtle focus:bg-surface-subtle [&_svg]:text-ink-muted' => ! $action->destructive,
            ];
        @endphp
        @if ($action->url)
            <a role="menuitem" tabindex="-1" x-show="menu.actions.includes(@js($action->key))" x-bind:href="menu.hrefs[@js($action->key)]" @class($classes) data-dt-row-action="{{ $action->key }}">
                @if ($action->icon)<x-ui.icon :name="$action->icon" />@endif
                {{ $action->label }}
            </a>
        @else
            <button type="button" role="menuitem" tabindex="-1" x-show="menu.actions.includes(@js($action->key))" x-on:click="rowAction(@js($action->key), @js($action->needsConfirmation()))" @class($classes) data-dt-row-action="{{ $action->key }}">
                @if ($action->icon)<x-ui.icon :name="$action->icon" />@endif
                {{ $action->label }}
            </button>
        @endif
    @endforeach
</div>
