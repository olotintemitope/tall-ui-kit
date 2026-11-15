{{-- "All transactions ↗" style link to a fuller view. Props: href. Meta size, ink, accent text on hover. --}}
@props(['href'])

<a href="{{ $href }}" {{ $attributes->class(['group inline-flex items-center gap-1 rounded-sm font-body text-meta font-medium text-ink transition-colors duration-100 hover:text-primary-text focus-ring']) }}>
    {{ $slot }}
    <x-ui.icon name="arrow-up-right" class="size-3.5 text-ink-muted transition-colors duration-100 group-hover:text-primary-text" />
</a>
