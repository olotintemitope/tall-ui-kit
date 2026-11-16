{{-- Shared body for a pattern page: intro, live examples, then the recipe text from docs/. --}}
<x-workbench::docs.layout :title="$title" :description="$intro">
    <div class="flex max-w-4xl flex-col gap-8">
        <header class="flex flex-col gap-2">
            <p class="type-label">Pattern</p>
            <h1 class="type-title">{{ $title }}</h1>
            <p class="type-body max-w-prose">{{ $intro }}</p>
        </header>
        <section aria-labelledby="examples" class="flex flex-col gap-6">
            <h2 id="examples" class="type-heading">Example</h2>
            @foreach ($examples as $example)
                <x-workbench::docs.example :example="$example" />
            @endforeach
        </section>
        @foreach ($sections as [$file, $heading])
            <section class="flex flex-col gap-3">
                <h2 class="type-heading" id="{{ \Illuminate\Support\Str::slug($heading) }}">{{ $heading }}</h2>
                <x-ui.prose>{!! \Workbench\App\Docs\Markdown::file($file, $heading) !!}</x-ui.prose>
            </section>
        @endforeach
    </div>
</x-workbench::docs.layout>
