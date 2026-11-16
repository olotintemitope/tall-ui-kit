@php use Workbench\App\Docs\Markdown; @endphp
<x-workbench::docs.layout :title="$title" description="The kit's hard rules: no AI look, 1px borders, focus, contrast, below-only popups, no native select or date inputs, tables, one-line pills.">
    <div class="flex max-w-3xl flex-col gap-6">
        <header class="flex flex-col gap-2">
            <p class="type-label">Guide</p>
            <h1 class="type-title">Rules</h1>
            <p class="type-body max-w-prose">A screen built from kit components that breaks one of these is still wrong. Rendered from <code class="type-numeric">docs/rules.md</code>.</p>
        </header>
        <x-ui.prose>{!! Markdown::file('docs/rules.md') !!}</x-ui.prose>
    </div>
</x-workbench::docs.layout>
