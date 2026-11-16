@php $releases = \Workbench\App\Docs\Changelog::releases(); @endphp
<x-workbench::docs.layout :title="$title" description="Every release of the kit, generated from git tags and conventional commits.">
    <div class="flex max-w-3xl flex-col gap-8">
        <header class="flex flex-col gap-2">
            <p class="type-label">Guide</p>
            <h1 class="type-title">Changelog</h1>
            <p class="type-body max-w-prose">Generated at build time from the git tags and their conventional commit subjects.</p>
        </header>
        @forelse ($releases as $release)
            <section aria-labelledby="{{ \Illuminate\Support\Str::slug($release['version']) }}" class="flex flex-col gap-3 border-t border-line pt-4">
                <div class="flex flex-wrap items-baseline gap-x-3 gap-y-1">
                    <h2 id="{{ \Illuminate\Support\Str::slug($release['version']) }}" class="type-heading">{{ $release['version'] }}</h2>
                    @if ($release['date'])<span class="type-meta">{{ \Carbon\Carbon::parse($release['date'])->format('j M Y') }}</span>@endif
                </div>
                @foreach ($release['groups'] as $group => $items)
                    <div class="flex flex-col gap-1">
                        <h3 class="type-subheading">{{ $group }}</h3>
                        <ul class="flex list-disc flex-col gap-1 pl-5 type-body">
                            @foreach ($items as $item)<li class="wrap-anywhere">{{ $item }}</li>@endforeach
                        </ul>
                    </div>
                @endforeach
            </section>
        @empty
            <p class="type-meta">No git history available at build time.</p>
        @endforelse
    </div>
</x-workbench::docs.layout>
