{{--
    Two columns: the form on the left and a companion on the right, from lg. Below lg it
    is one column and the companion moves under the form.
      variant="summary": the right column is 360px for x-pricing.summary-rail (sticky
        from lg; below lg the rail is its own bottom bar, so the column collapses).
      variant="preview": the right column is 400px, sticky under the top bar, holding a
        live preview (e.g. a smart link). Name it with previewLabel; it is a region.
    Props: variant (summary | preview), previewLabel ("Preview"). Slots: default (the
    form), aside (the rail or the preview).
--}}
@props(['variant' => 'summary', 'previewLabel' => 'Preview'])

<div {{ $attributes->class([
    'grid grid-cols-1 items-start gap-6',
    'lg:grid-cols-[minmax(0,1fr)_360px]' => $variant === 'summary',
    'lg:grid-cols-[minmax(0,1fr)_400px]' => $variant === 'preview',
]) }} data-form-layout="split-{{ $variant }}">
    <div class="flex min-w-0 flex-col gap-6">{{ $slot }}</div>
    @isset($aside)
        @if ($variant === 'preview')
            <section aria-label="{{ $previewLabel }}" class="min-w-0 lg:sticky lg:top-20" data-form-preview>{{ $aside }}</section>
        @else
            <div class="min-w-0">{{ $aside }}</div>
        @endif
    @endisset
</div>
