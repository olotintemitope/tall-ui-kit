{{--
    Loading state for a person: a 36px avatar circle beside a name (body-strong, 20px)
    and a meta line (18px), the same block as x-ui.card.profile's header.
    Props: label.
--}}
@props(['label' => 'Loading'])

<div {{ $attributes->class(['flex items-start gap-3']) }} role="status" data-skeleton="avatar">
    <span class="sr-only">{{ $label }}</span>
    <x-ui.skeleton class="size-9 shrink-0 rounded-full" aria-hidden="true" />
    <div class="min-w-0 flex-1">
        <x-ui.skeleton.line role="body" width="w-32" />
        <x-ui.skeleton.line role="meta" width="w-24" />
    </div>
</div>
