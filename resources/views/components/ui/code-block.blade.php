{{--
    Block of code or a payload: mono 13/20, panel-footer fill, 1px line border, 8px
    radius, scrolls sideways (the scroll area takes focus so keyboards can scroll it).
    Props: label (caption above the code, e.g. "Webhook payload"), copy (a copy
    button that toasts). Print the code in the slot with {{ }} so Blade escapes it.
--}}
@props(['label' => null, 'copy' => false])

<div
    @if ($copy)
        x-data="{
            async copy() {
                try {
                    await navigator.clipboard.writeText(this.$refs.code.innerText);
                    $dispatch('toast', { type: 'success', message: 'Copied to clipboard.' });
                } catch (e) {
                    $dispatch('toast', { type: 'error', message: 'Could not copy. Select the text and copy it instead.' });
                }
            },
        }"
    @endif
    {{ $attributes->class(['overflow-hidden rounded-lg border border-line bg-panel-footer']) }}
    data-code-block
>
    @if ($label || $copy)
        <div class="flex h-9 items-center justify-between gap-3 border-b border-line pl-3 pr-1">
            <span class="type-caption wrap-anywhere">{{ $label }}</span>
            @if ($copy)
                <x-ui.button variant="ghost" size="sm" x-on:click="copy()" aria-label="{{ $label ? 'Copy '.$label : 'Copy code' }}">
                    <x-ui.icon name="copy" />
                    Copy
                </x-ui.button>
            @endif
        </div>
    @endif
    <pre
        tabindex="0"
        @if ($label) aria-label="{{ $label }}" @endif
        class="overflow-x-auto p-3 type-numeric leading-5 text-ink focus-visible:outline-2 focus-visible:outline-focus focus-visible:-outline-offset-2"
    ><code x-ref="code">{{ $slot }}</code></pre>
</div>