{{-- Keyboard key, e.g. <x-ui.kbd>⌘K</x-ui.kbd>. Mono, hairline border, no shadow. Props: tone (light|dark). --}}
@props(['tone' => 'light'])

<kbd {{ $attributes->class([
    'inline-flex h-5 min-w-5 items-center justify-center rounded border px-1 font-mono text-[11px] leading-none font-medium',
    'border-white/15 text-white/70' => $tone === 'dark',
    'border-line-strong bg-surface text-ink-muted' => $tone !== 'dark',
]) }}>{{ $slot }}</kbd>
