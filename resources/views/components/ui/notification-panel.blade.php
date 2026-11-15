{{--
    The notifications dropdown body (about 380px): a heading, "Mark all as read", All
    and Unread tabs, the grouped list (loaded on first open, a skeleton until then),
    and a "View all" link. A non-modal popover: Escape closes it and returns focus to
    the bell. Props: tab (all | unread, bind with wire:model.live), loaded, unread
    (count, enables Mark all), viewAll (href; hidden when null), markAll (Livewire
    method), model (the Livewire property the tabs bind to; "" for a static preview).
    Slot: x-ui.notification-group elements, or an empty line.
--}}
@props(['tab' => 'all', 'loaded' => true, 'unread' => 0, 'viewAll' => null, 'markAll' => 'markAllRead', 'model' => 'tab'])

<div {{ $attributes->class(['flex max-h-[min(560px,calc(100dvh-5rem))] w-full flex-col']) }} data-notification-panel>
    <div class="flex items-center justify-between gap-3 px-4 pb-1 pt-3">
        <h2 class="type-heading" tabindex="-1" data-notification-heading>Notifications</h2>
        <x-ui.button variant="link" size="sm" wire:click="{{ $markAll }}" :disabled="$unread === 0" data-mark-all>Mark all as read</x-ui.button>
    </div>

    <x-ui.tabs :tabs="['all' => 'All', 'unread' => ['label' => 'Unread', 'count' => $unread]]" :active="$tab" size="sm" label="Show" class="px-2" :attributes="new \Illuminate\View\ComponentAttributeBag($model ? ['wire:model.live' => $model] : [])">
        <x-ui.tabs.panel :name="$tab" class="pt-0! focus-visible:outline-offset-[-2px]">
            <div class="min-h-0 overflow-y-auto rounded-sm focus-ring" tabindex="0" style="max-height: min(420px, calc(100dvh - 12rem))">
                @if (! $loaded)
                    <x-ui.skeleton.list :rows="4" label="Loading notifications" flush />
                @else
                    {{ $slot }}
                @endif
            </div>
        </x-ui.tabs.panel>
    </x-ui.tabs>

    @if ($viewAll)
        <div class="border-t border-line px-4 py-2.5">
            <x-ui.link-arrow :href="$viewAll">View all</x-ui.link-arrow>
        </div>
    @endif
</div>
