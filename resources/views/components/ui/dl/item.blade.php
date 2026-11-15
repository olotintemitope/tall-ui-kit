{{-- One pair in x-ui.dl: <div><dt>label</dt><dd>value</dd></div>. Props: label, numeric. --}}
@props(['label', 'numeric' => false, 'layout' => null, 'dense' => null])
@aware(['layout' => 'stacked', 'dense' => false])

<div @class([
    'flex min-w-0 flex-col gap-0.5' => $layout !== 'inline',
    'flex items-baseline justify-between gap-4' => $layout === 'inline',
    'py-2' => $layout === 'inline' && $dense,
    'py-2.5' => $layout === 'inline' && ! $dense,
])>
    <dt class="type-meta">{{ $label }}</dt>
    <dd {{ $attributes->class(['min-w-0', 'type-numeric text-ink' => $numeric, 'type-body' => ! $numeric, 'text-right' => $layout === 'inline']) }}>{{ $slot }}</dd>
</div>
