@php use Workbench\App\Docs\Catalog; use Workbench\App\Docs\Site; @endphp
<x-workbench::docs.layout :title="$title" description="Every x-ui component, grouped by family.">
    <div class="flex max-w-5xl flex-col gap-8">
        <header class="flex flex-col gap-2">
            <p class="type-label">Reference</p>
            <h1 class="type-title">Components</h1>
            <p class="type-body max-w-prose">{{ count(Catalog::files()) }} Blade components on {{ count(Catalog::components()) }} pages. Each page has live examples, the props generated from the component's source, keyboard and accessibility notes.</p>
        </header>
        @foreach (Catalog::FAMILIES as $family => $members)
            <section aria-labelledby="family-{{ \Illuminate\Support\Str::slug($family) }}" class="flex flex-col gap-2">
                <h2 id="family-{{ \Illuminate\Support\Str::slug($family) }}" class="type-heading">{{ $family }}</h2>
                <ul class="grid grid-cols-1 border-t border-line sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($members as $slug => [$name, $components])
                        <li class="border-b border-line">
                            <a href="{{ Site::url('components/'.$slug) }}" class="flex flex-col gap-0.5 rounded-sm px-2 py-2.5 hover:bg-panel-footer focus-ring">
                                <span class="type-body-strong">{{ $name }}</span>
                                <span class="type-caption wrap-anywhere">{{ implode(', ', array_map(fn ($c) => Catalog::tag($c), $components)) }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
    </div>
</x-workbench::docs.layout>
