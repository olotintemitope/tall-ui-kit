{{--
    Docs site shell: header (name, search, theme and mode), a section nav (a sidebar
    from lg, a native <details> menu below it, so it works without JavaScript), and
    the page. Theme: a stylesheet swap stored in localStorage (applied before paint by
    the inline script); mode: x-ui.theme-toggle. Search needs JavaScript and stays
    hidden without it.
--}}
@props(['title' => 'Docs', 'description' => null])
@php
    use Workbench\App\Docs\Catalog;
    use Workbench\App\Docs\Site;

    $static = Site::$static;
    $sections = [
        'Guides' => collect(Catalog::GUIDES)->mapWithKeys(fn ($t, $s) => [$s => $t])->all(),
        'Patterns' => collect(Catalog::PATTERNS)->mapWithKeys(fn ($t, $s) => ['patterns/'.$s => $t])->all(),
    ];
    $sections['Guides'] = array_slice($sections['Guides'], 0, 1, true) + ['components/index' => 'All components'] + array_slice($sections['Guides'], 1, null, true);
    foreach (Catalog::FAMILIES as $family => $members) {
        $sections[$family] = collect($members)->mapWithKeys(fn ($m, $s) => ['components/'.$s => $m[0]])->all();
    }
    $base = $static ? str_repeat('../', substr_count(Site::$current, '/')) : url('/docs').'/';
@endphp
<!DOCTYPE html>
<html lang="en" data-docs-base="{{ $base }}" data-docs-ext="{{ $static ? '.html' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title === 'Getting started' ? 'TALL UI kit docs' : $title.' · TALL UI kit docs' }}</title>
    @if ($description)<meta name="description" content="{{ $description }}">@endif
    <x-ui.head />
    <link id="docs-theme" rel="stylesheet" href="{{ Site::asset('neutral.css') }}" data-neutral="{{ Site::asset('neutral.css') }}" data-newbanger="{{ Site::asset('newbanger.css') }}">
    <script>
        (function () {
            try {
                if (localStorage.getItem('tallui_docs_theme') === 'newbanger') {
                    var link = document.getElementById('docs-theme');
                    link.href = link.dataset.newbanger;
                    document.documentElement.dataset.docsTheme = 'newbanger';
                }
            } catch (e) {}
        })();
    </script>
    <script type="module" src="{{ Site::asset('gallery.js') }}"></script>
    <script type="module" src="{{ Site::asset('docs.js') }}"></script>
    <script defer src="{{ Site::asset('docs-search.js') }}"></script>
</head>
<body class="min-h-screen bg-surface font-body text-ink">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-3 focus:py-2 focus-ring">Skip to content</a>
    <header class="border-b border-line">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-4 gap-y-3 px-4 py-3 sm:px-6">
            <a href="{{ Site::url('index') }}" class="type-heading rounded-sm focus-ring">TALL UI kit</a>
            <span class="type-meta">v{{ \Workbench\App\Docs\Changelog::currentVersion() }}</span>
            <div class="order-last w-full sm:order-none sm:ml-auto sm:w-72" data-docs-search hidden>
                <label for="docs-search" class="sr-only">Search the docs</label>
                <div class="relative">
                    <x-ui.input id="docs-search" type="search" size="sm" width="full" placeholder="Search components and guides" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="docs-search-results" aria-autocomplete="list" />
                    <ul id="docs-search-results" role="listbox" aria-label="Search results" class="absolute left-0 right-0 top-full z-40 mt-1 hidden max-h-96 overflow-y-auto rounded-lg border border-panel-border bg-popover p-1 shadow-popover"></ul>
                </div>
                <p id="docs-search-status" class="sr-only" role="status" aria-live="polite"></p>
            </div>
            <div class="flex flex-wrap items-center gap-x-3 gap-y-2 sm:ml-0">
                <div x-data="docsTheme" class="flex items-center gap-2">
                    <x-ui.segmented size="sm" label="Theme" :options="['neutral' => 'Neutral', 'newbanger' => 'NewBanger']" value="neutral" x-model="theme" />
                </div>
                <x-ui.theme-toggle size="sm" label="Mode" />
            </div>
        </div>
    </header>

    <div class="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-6 sm:px-6 lg:flex-row lg:gap-10">
        <details class="group/menu rounded-lg border border-line lg:hidden">
            <summary class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2 type-body-strong focus-ring">Menu <x-ui.icon name="chevron-down" class="size-4 text-ink-muted group-open/menu:rotate-180" /></summary>
            <div class="border-t border-line px-3 pb-3">
                @include('docs.partials.nav', ['sections' => $sections, 'label' => 'Docs sections, menu'])
            </div>
        </details>
        <aside class="hidden w-56 shrink-0 lg:block">
            <div class="sticky top-4 max-h-[calc(100vh-2rem)] overflow-y-auto pr-2">
                @include('docs.partials.nav', ['sections' => $sections, 'label' => 'Docs sections'])
            </div>
        </aside>
        <main id="main" tabindex="-1" class="min-w-0 flex-1 outline-none">
            {{ $slot }}
        </main>
    </div>
    <footer class="border-t border-line">
        <p class="mx-auto max-w-7xl px-4 py-4 type-caption sm:px-6">Built from the kit's own components by <code class="type-numeric">composer docs:build</code>. Proprietary, Temitope Olotin.</p>
    </footer>
    <x-ui.toaster />
    @livewireScripts
</body>
</html>
