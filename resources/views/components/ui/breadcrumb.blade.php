{{--
    Breadcrumb trail: <nav aria-label="Breadcrumb"> around an <ol>. Earlier steps are
    ink-muted links (5.86:1) that darken on hover; the last item is the current page
    (body-strong, aria-current="page", not a link). Separators are a "/" in ink-muted,
    hidden from assistive tech. Below sm only the parent and the current page show
    (`collapse`). The shell top bar draws the same trail from its `breadcrumbs`.
    Props: items (list of ['label', 'href']; the last is current), label ("Breadcrumb";
    name it differently when a page has two), collapse (true).
--}}
@props(['items' => [], 'label' => 'Breadcrumb', 'collapse' => true])

@php
    $items = array_values($items);
    $count = count($items);
@endphp

<nav aria-label="{{ $label }}" {{ $attributes->class(['min-w-0']) }} data-breadcrumb>
    <ol class="flex min-w-0 flex-wrap items-center gap-x-1.5 gap-y-1 type-body">
        @foreach ($items as $i => $crumb)
            @php($hide = $collapse && $i < $count - 2)
            {{-- Items wrap between each other, never inside a word; the current page wraps normally. --}}
            <li @class(['flex items-center gap-1.5', 'min-w-0' => $i === $count - 1, 'shrink-0 whitespace-nowrap' => $i < $count - 1, 'max-sm:hidden' => $hide])>
                @if ($i > 0)
                    <span aria-hidden="true" class="select-none text-ink-muted">/</span>
                @endif
                @if ($i === $count - 1)
                    <span class="type-body-strong min-w-0 wrap-break-word" aria-current="page">{{ $crumb['label'] }}</span>
                @elseif (! empty($crumb['href']))
                    <a href="{{ $crumb['href'] }}" class="rounded-sm text-ink-muted transition-colors duration-100 hover:text-ink focus-ring">{{ $crumb['label'] }}</a>
                @else
                    <span class="text-ink-muted">{{ $crumb['label'] }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
