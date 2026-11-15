{{--
    The toast stack, placed once per layout (layouts/app). Fed by the `toasts` store
    (resources/js/toasts.js): Livewire $this->dispatch('toast', ...), Alpine $toast(...),
    or a `toast` session flash on the next page.
    Props: position (bottom-right | bottom-left | top-right; always bottom-centre on
    phones), max (3; the rest collapse behind an "N more" button), duration (4000ms;
    errors and loading toasts are sticky). Success, info, warning, neutral and loading
    toasts sit in a polite live region, errors in an assertive one. Toasts never take
    focus; Alt+T moves to the newest one and focus returns when it closes (ui.js).
    Enter: a 4px rise and fade (150ms, ease-motion-out); leave: fade (100ms).
    Over modals: while a modal <dialog> is open, uiToaster (resources/js/toaster-layer.js)
    moves this node into the topmost one, so toasts show above its backdrop, stay
    clickable and are announced (the page behind is inert), and steer clear of the
    dialog's footer buttons. It goes back here when the last modal closes.
--}}
@props(['position' => 'bottom-right', 'max' => 3, 'duration' => 4000])

@php
    $placement = [
        'bottom-right' => 'sm:right-4 sm:left-auto sm:translate-x-0',
        'bottom-left' => 'sm:left-4 sm:translate-x-0',
        'top-right' => 'sm:right-4 sm:left-auto sm:translate-x-0 sm:top-4 sm:bottom-auto',
    ][$position] ?? 'sm:right-4 sm:left-auto sm:translate-x-0';
    $flash = session('toast');
@endphp

<div
    x-data="uiToaster"
    x-init="$store.toasts.configure({ max: @js((int) $max), duration: @js((int) $duration) }); @if ($flash) $store.toasts.push(@js($flash)) @endif"
    data-toast-region
    data-toaster="{{ $position }}"
    role="region"
    aria-label="Messages"
    {{ $attributes->class(['fixed bottom-4 left-1/2 z-[100] flex w-[calc(100%-2rem)] max-w-[380px] -translate-x-1/2 flex-col gap-2 pointer-events-none', $placement]) }}
>
    <button
        type="button"
        x-show="$store.toasts.hiddenCount > 0"
        x-cloak
        x-on:click="$store.toasts.expanded = true; $nextTick(() => $root.querySelector('[data-toast] button')?.focus())"
        x-bind:aria-expanded="$store.toasts.expanded ? 'true' : 'false'"
        aria-expanded="false"
        class="pointer-events-auto self-end rounded-md border border-line bg-popover px-2.5 py-1 font-body text-meta text-ink shadow-popover hover:bg-surface-subtle focus-ring"
        x-text="$store.toasts.hiddenCount + ' more'"
        data-toast-more
    ></button>

    @foreach (['polite' => "toast.variant !== 'error'", 'assertive' => "toast.variant === 'error'"] as $politeness => $filter)
        <div aria-live="{{ $politeness }}" aria-relevant="additions text" class="flex flex-col gap-2" data-toast-live="{{ $politeness }}">
            <template x-for="toast in $store.toasts.visible.filter((toast) => {!! $filter !!})" :key="toast.id">
                <div
                    x-transition:enter="transition duration-150 ease-motion-out"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition duration-100 ease-motion-out"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                >
                    <x-ui.toast bind="toast" />
                </div>
            </template>
        </div>
    @endforeach
</div>
