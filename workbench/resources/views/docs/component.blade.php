{{-- One component page, generated from the component sources plus resources/docs/<slug>.php notes. --}}
@php
    $first = $sources[0];
    $intro = $notes['intro'] ?? $first->summary();
@endphp
<x-workbench::docs.layout :title="$title" :description="$intro">
    <div class="flex max-w-4xl flex-col gap-8">
        <header class="flex flex-col gap-2">
            <p class="type-label">{{ $page['family'] }}</p>
            <h1 class="type-title wrap-anywhere">{{ $title }}</h1>
            <p class="type-body max-w-prose">{{ $intro }}</p>
            <p class="type-meta">
                @foreach ($sources as $source)
                    <code class="type-numeric text-ink">&lt;{{ \Workbench\App\Docs\Catalog::tag($source->name) }}&gt;</code>@if (! $loop->last), @endif
                @endforeach
            </p>
        </header>

        <section aria-labelledby="examples" class="flex flex-col gap-6">
            <div class="flex flex-col gap-1">
                <h2 id="examples" class="type-heading">Examples</h2>
                <p class="type-meta">Live: switch Theme and Mode in the header to see each example in all four theme and mode sets.</p>
            </div>
            @forelse ($examples as $example)
                <x-workbench::docs.example :example="$example" />
            @empty
                <p class="type-meta">No live example: see the API below.</p>
            @endforelse
        </section>

        <section aria-labelledby="api" class="flex flex-col gap-6">
            <h2 id="api" class="type-heading">API</h2>
            @foreach ($sources as $source)
                @php($tag = \Workbench\App\Docs\Catalog::tag($source->name))
                <div class="flex min-w-0 flex-col gap-3">
                    <h3 class="type-subheading" id="api-{{ \Illuminate\Support\Str::slug($source->name) }}"><code class="type-numeric font-semibold">{{ $tag }}</code></h3>
                    @if ($source->summary() && $source !== $first)
                        <p class="type-body max-w-prose">{{ $source->summary() }}</p>
                    @endif
                    @if ($source->props)
                        <x-ui.table :caption="'Props of '.$tag">
                            <x-slot:head>
                                <x-ui.table.th>Prop</x-ui.table.th>
                                <x-ui.table.th>Type</x-ui.table.th>
                                <x-ui.table.th>Default</x-ui.table.th>
                                <x-ui.table.th>Description</x-ui.table.th>
                            </x-slot:head>
                            @foreach ($source->props as $prop)
                                <x-ui.table.row>
                                    <x-ui.table.td mono strong>{{ $prop['name'] }}</x-ui.table.td>
                                    <x-ui.table.td mono muted>{{ $prop['type'] }}</x-ui.table.td>
                                    <x-ui.table.td mono>{{ $prop['default'] }}</x-ui.table.td>
                                    <x-ui.table.td>{{ $prop['description'] ?: 'See the notes below.' }}</x-ui.table.td>
                                </x-ui.table.row>
                            @endforeach
                        </x-ui.table>
                    @else
                        <p class="type-meta">No props; attributes pass through.</p>
                    @endif
                    <dl class="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-[8rem_minmax(0,1fr)]">
                        <dt class="type-meta">Slots</dt>
                        <dd class="type-body wrap-anywhere">{{ $source->slots ? implode(', ', $source->slots) : 'None' }}</dd>
                        <dt class="type-meta">Events</dt>
                        <dd class="type-body wrap-anywhere">
                            @if ($source->dispatches || $source->listens)
                                @if ($source->dispatches) Dispatches {{ implode(', ', $source->dispatches) }}. @endif
                                @if ($source->listens) Listens for {{ implode(', ', $source->listens) }} on window. @endif
                            @else
                                None in the view
                            @endif
                        </dd>
                        <dt class="type-meta">Source</dt>
                        <dd class="type-numeric wrap-anywhere">{{ $source->path }}</dd>
                    </dl>
                    <x-ui.disclosure :summary="'Notes from the source of '.$tag">
                        <pre class="mt-2 whitespace-pre-wrap wrap-anywhere rounded-md border border-line bg-panel-footer p-3 type-numeric text-ink-body">{{ $source->comment ?: 'No comment.' }}</pre>
                    </x-ui.disclosure>
                </div>
            @endforeach
            @if ($notes['events'])
                <div class="flex flex-col gap-2">
                    <h3 class="type-subheading">Events and Livewire</h3>
                    <ul class="list-disc pl-5 type-body">
                        @foreach ($notes['events'] as $line)<li>{!! \Illuminate\Support\Str::inlineMarkdown($line) !!}</li>@endforeach
                    </ul>
                </div>
            @endif
        </section>

        @if ($notes['keyboard'])
            <section aria-labelledby="keyboard" class="flex flex-col gap-3">
                <h2 id="keyboard" class="type-heading">Keyboard</h2>
                <x-ui.table caption="Keyboard map">
                    <x-slot:head>
                        <x-ui.table.th>Key</x-ui.table.th>
                        <x-ui.table.th>Action</x-ui.table.th>
                    </x-slot:head>
                    @foreach ($notes['keyboard'] as [$key, $action])
                        <x-ui.table.row>
                            <x-ui.table.td><span class="flex flex-wrap gap-1">@foreach (explode(' / ', $key) as $k)<x-ui.kbd>{{ $k }}</x-ui.kbd>@endforeach</span></x-ui.table.td>
                            <x-ui.table.td>{{ $action }}</x-ui.table.td>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table>
            </section>
        @endif

        @if ($notes['a11y'])
            <section aria-labelledby="a11y" class="flex flex-col gap-3">
                <h2 id="a11y" class="type-heading">Accessibility</h2>
                <ul class="flex list-disc flex-col gap-1 pl-5 type-body max-w-prose">
                    @foreach ($notes['a11y'] as $line)<li>{!! \Illuminate\Support\Str::inlineMarkdown($line) !!}</li>@endforeach
                </ul>
            </section>
        @endif

        @if ($notes['do'] || $notes['dont'])
            <section aria-labelledby="usage" class="flex flex-col gap-3">
                <h2 id="usage" class="type-heading">Do and don't</h2>
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div class="flex flex-col gap-2">
                        <h3 class="type-subheading">Do</h3>
                        <ul class="flex list-disc flex-col gap-1 pl-5 type-body">
                            @foreach ($notes['do'] as $line)<li>{!! \Illuminate\Support\Str::inlineMarkdown($line) !!}</li>@endforeach
                        </ul>
                    </div>
                    <div class="flex flex-col gap-2">
                        <h3 class="type-subheading">Don't</h3>
                        <ul class="flex list-disc flex-col gap-1 pl-5 type-body">
                            @foreach ($notes['dont'] as $line)<li>{!! \Illuminate\Support\Str::inlineMarkdown($line) !!}</li>@endforeach
                        </ul>
                    </div>
                </div>
            </section>
        @endif
    </div>
</x-workbench::docs.layout>
