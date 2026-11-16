{{-- One live example: the rendered Blade file, then its source with the kit's copy button. --}}
@props(['example', 'level' => 3])
@php($id = \Illuminate\Support\Str::slug($example['title']))
<section aria-labelledby="ex-{{ $id }}" class="flex min-w-0 flex-col gap-2" data-docs-example>
    <x-ui.heading :level="$level" size="subheading" id="ex-{{ $id }}">{{ $example['title'] }}</x-ui.heading>
    <div class="min-w-0 rounded-lg border border-line bg-surface px-4 py-4 sm:p-6 lg:px-8" data-docs-preview>
        @include($example['view'])
    </div>
    <x-ui.code-block :label="'Blade: '.$example['title']" copy>{{ $example['source'] }}</x-ui.code-block>
</section>
