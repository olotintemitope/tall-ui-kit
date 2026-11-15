{{--
    Lucide icon (mallardduck/blade-lucide-icons through TallUiKit\Support\LucideIcons, React
    names). Inline SVG in currentColor, 24px box sized by class (size-4 for 16px),
    aria-hidden by default: the control or text next to it carries the name.
    Props: name, strokeWidth (2). Other attributes (class, wire:*, x-*) pass through.
--}}
@props(['name', 'strokeWidth' => 2])

<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $strokeWidth }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" {{ $attributes->class(['shrink-0']) }}>{!! \TallUiKit\Support\LucideIcons::inner($name) !!}</svg>
