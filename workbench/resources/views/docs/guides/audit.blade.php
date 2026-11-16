@php use Workbench\App\Docs\Markdown; @endphp
<x-workbench::docs.layout :title="$title" description="How every component is checked: consistency, the kit rules and accessibility, plus the latest audit.">
    <div class="flex max-w-4xl flex-col gap-6">
        <header class="flex flex-col gap-2">
            <p class="type-label">Guide</p>
            <h1 class="type-title">Audit rules</h1>
            <p class="type-body max-w-prose">The checklist each component is held to, the accessibility target and the latest audit with its findings and fixes. Rendered from <code class="type-numeric">docs/audit-2026-11.md</code> and <code class="type-numeric">docs/accessibility.md</code>.</p>
        </header>
        <x-ui.prose>{!! Markdown::file('docs/audit-2026-11.md') !!}</x-ui.prose>
        <h2 class="type-heading" id="accessibility">Accessibility and focus</h2>
        <x-ui.prose>{!! Markdown::file('docs/accessibility.md') !!}</x-ui.prose>
        <h2 class="type-heading" id="performance">Performance budgets</h2>
        <x-ui.prose>{!! Markdown::file('docs/performance.md') !!}</x-ui.prose>
    </div>
</x-workbench::docs.layout>
