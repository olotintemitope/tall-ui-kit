{{--
    End-of-form action bar: top border, right-aligned, secondary then primary; full-width
    buttons side by side on mobile. With `sticky`, it pins to the bottom of the viewport
    and only shows while the surrounding x-ui.form is dirty.
--}}
@props(['sticky' => false])

<div
    @if ($sticky) x-show="dirty" x-cloak x-transition.opacity.duration.150ms @endif
    {{ $attributes->class([
        'flex items-center justify-end gap-2 border-t border-panel-border max-sm:[&>*]:flex-1',
        'sticky bottom-0 z-20 -mx-4 bg-white px-4 py-3 shadow-bar md:-mx-6 md:px-6 lg:-mx-8 lg:px-8' => $sticky,
        'pt-4' => ! $sticky,
    ]) }}
    data-form-actions
>{{ $slot }}</div>
