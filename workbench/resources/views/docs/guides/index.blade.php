@php use Workbench\App\Docs\Markdown; use Workbench\App\Docs\Site; @endphp
<x-workbench::docs.layout :title="$title" description="Install the TALL UI kit, pick a theme and turn on dark mode.">
    <div class="flex max-w-3xl flex-col gap-8">
        <header class="flex flex-col gap-2">
            <p class="type-label">Guide</p>
            <h1 class="type-title">TALL UI kit</h1>
            <p class="type-body max-w-prose">A flat, dense, accessible kit for Laravel 12 and 13, Livewire 4.4, Alpine and Tailwind 4: anonymous Blade components under <code class="type-numeric">x-ui.*</code> and <code class="type-numeric">x-data-table.*</code>, two themes, each with light and dark colour schemes. Every example on this site is the kit itself, rendered by <code class="type-numeric">composer docs:build</code>.</p>
            <p class="type-meta"><a class="underline underline-offset-2 rounded-sm focus-ring" href="{{ Site::url('components/index') }}">Browse the components</a>, read the <a class="underline underline-offset-2 rounded-sm focus-ring" href="{{ Site::url('rules') }}">rules</a>, or start with a <a class="underline underline-offset-2 rounded-sm focus-ring" href="{{ Site::url('patterns/forms') }}">pattern</a>.</p>
        </header>

        <section aria-labelledby="install" class="flex flex-col gap-3">
            <h2 id="install" class="type-heading">Install</h2>
            <x-ui.prose>{!! Markdown::file('README.md', 'Install') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="first-page" class="flex flex-col gap-3">
            <h2 id="first-page" class="type-heading">A page with the kit</h2>
            <x-ui.prose>{!! Markdown::file('README.md', 'A page with the kit') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="themes" class="flex flex-col gap-3">
            <h2 id="themes" class="type-heading">Themes</h2>
            <x-ui.prose>{!! Markdown::file('docs/theming.md', 'Shipped themes') !!}</x-ui.prose>
            <p class="type-meta">Switch Theme in the header to see every page of this site in either theme.</p>
        </section>

        <section aria-labelledby="dark-mode" class="flex flex-col gap-3">
            <h2 id="dark-mode" class="type-heading">Dark mode</h2>
            <div class="flex flex-wrap items-center gap-3 rounded-lg border border-line p-4">
                <x-ui.theme-toggle label="Colour scheme" />
                <p class="type-meta">The same toggle as in the header: Light, Dark or System, stored before first paint by <code class="type-numeric">x-ui.head</code>.</p>
            </div>
            <x-ui.prose>{!! Markdown::file('docs/theming.md', 'Dark mode') !!}</x-ui.prose>
        </section>

        <section aria-labelledby="checks" class="flex flex-col gap-3">
            <h2 id="checks" class="type-heading">Checks</h2>
            <x-ui.prose>{!! Markdown::file('README.md', 'Checks') !!}</x-ui.prose>
        </section>
    </div>
</x-workbench::docs.layout>
