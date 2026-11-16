<nav aria-label="{{ $label }}" class="docs-nav flex flex-col gap-5 pt-3 lg:pt-0">
    @foreach ($sections as $section => $links)
        <div>
            <p class="type-label mb-1.5">{{ $section }}</p>
            <ul>
                @foreach ($links as $path => $text)
<li><a href="{{ \Workbench\App\Docs\Site::url($path) }}"@if (\Workbench\App\Docs\Site::is($path)) aria-current="page"@endif>{{ $text }}</a></li>
                @endforeach
            </ul>
        </div>
    @endforeach
</nav>
