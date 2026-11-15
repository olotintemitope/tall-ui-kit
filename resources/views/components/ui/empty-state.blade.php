{{--
    Plain, left-aligned empty state: a short statement of fact and an optional
    next step. No icon tile, no centred hero; it reads like a row in the page.
    Props: title, description, actionLabel + actionHref, or an `action` slot.
--}}
@props(['title', 'description' => null, 'actionLabel' => null, 'actionHref' => null])

<div {{ $attributes->class(['border-t border-line py-8']) }}>
    <p class="type-body-strong">{{ $title }}</p>

    @if ($description)
        <p class="type-meta mt-1 max-w-prose">{{ $description }}</p>
    @endif

    @if ($actionLabel && $actionHref)
        <a href="{{ $actionHref }}" class="inline-flex items-center mt-3 font-body text-body-strong text-primary-text hover:underline underline-offset-4 rounded-sm focus-ring">
            {{ $actionLabel }}
        </a>
    @endif

    @isset($action)
        <div class="mt-3">{{ $action }}</div>
    @endisset
</div>
