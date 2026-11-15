{{--
    The top bar's notifications button: a 32px icon button with the unread count in a
    pill (capped at "9+"); the accessible name carries the number ("Notifications, 3
    unread"). The pill is drawn for sight only (aria-hidden). Props: count. Other
    attributes (x-on:click, x-bind:aria-expanded) go on the button. Data: the
    Livewire bell (app/Livewire/Notifications/Bell.php) polls the count every 30s
    while the tab is visible.
--}}
@props(['count' => 0])

@php
    $count = max(0, (int) $count);
    $label = $count > 0 ? 'Notifications, '.$count.' unread' : 'Notifications';
@endphp

<button
    type="button"
    aria-label="{{ $label }}"
    aria-haspopup="dialog"
    data-notification-bell
    data-unread="{{ $count }}"
    {{ $attributes->class(['relative flex size-8 items-center justify-center rounded-md text-ink-muted hover:bg-surface-subtle hover:text-ink transition-colors duration-100 focus-ring']) }}
>
    <x-ui.icon name="bell" class="size-4" />
    @if ($count > 0)
        <span aria-hidden="true" class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full border border-primary-hover bg-primary px-1 font-body text-caption font-medium leading-none tabular-nums text-on-primary" data-unread-pill>{{ $count > 9 ? '9+' : $count }}</span>
    @endif
</button>
