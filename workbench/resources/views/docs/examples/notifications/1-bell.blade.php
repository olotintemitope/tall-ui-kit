{{-- The bell: none, a few and more than nine unread --}}
<div class="flex flex-wrap items-center gap-4">
    <x-ui.notification-bell :count="0" />
    <x-ui.notification-bell :count="3" />
    <x-ui.notification-bell :count="27" />
</div>
