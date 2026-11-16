{{--
    The inside of x-ui.file-preview (internal): actions and Previous / Next, the viewer for
    the file's kind, and the details list. One small Alpine component (uiFilePreview in
    resources/js/files.js) for the image zoom and the arrow keys.
    Props: file, files, navigateAction, maxText, tz, now, heading (print the name: the
    panel does, the dialog and sheet have it as their title).
--}}
@props(['file' => [], 'files' => [], 'navigateAction' => null, 'maxText' => 100000, 'tz' => 'Africa/Lagos', 'now' => null, 'heading' => true])

@php
    use Illuminate\Support\Js;
    use TallUiKit\Support\Files;

    $name = (string) ($file['name'] ?? 'File');
    $key = (string) ($file['key'] ?? $name);
    $url = $file['url'] ?? null;
    $download = $file['download'] ?? $url;
    $type = $file['type'] ?? null;
    $kind = in_array($file['kind'] ?? null, Files::KINDS, true) ? $file['kind'] : Files::kind($name, $type);
    $label = Files::typeLabel($name, $type);
    if (! $url && $kind !== 'text') {
        $kind = 'other';
    }
    $text = isset($file['text']) ? (string) $file['text'] : null;
    if ($kind === 'text' && $text === null) {
        $kind = 'other';
    }
    $capped = $text !== null && mb_strlen($text) > $maxText;
    if ($capped) {
        $text = mb_substr($text, 0, (int) $maxText);
    }

    $set = collect($files)->map(fn ($f) => ['key' => (string) ($f['key'] ?? $f['name'] ?? ''), 'name' => (string) ($f['name'] ?? '')])->values();
    $index = $set->search(fn ($f) => $f['key'] === $key);
    $previous = $index !== false && $index > 0 ? $set[$index - 1] : null;
    $next = $index !== false && $index < $set->count() - 1 ? $set[$index + 1] : null;
    $details = array_values(array_filter([
        isset($file['size']) ? ['Size', Files::bytes($file['size']), 'numeric'] : null,
        ['Type', $type ? $label.' ('.$type.')' : $label, 'text'],
        ! empty($file['created']) ? ['Created', $file['created'], 'time'] : null,
        ! empty($file['modified']) ? ['Modified', $file['modified'], 'time'] : null,
    ]));
    foreach ((array) ($file['fields'] ?? []) as $fieldLabel => $value) {
        $details[] = [(string) $fieldLabel, (string) $value, 'text'];
    }
@endphp

<div
    x-data="uiFilePreview(@js(['action' => $navigateAction]))"
    x-on:keydown="key($event)"
    {{ $attributes->class(['flex min-w-0 flex-col gap-4']) }}
    data-file-preview="{{ $kind }}"
    data-key="{{ $key }}"
>
    <div class="flex min-w-0 flex-wrap items-start justify-between gap-x-4 gap-y-2">
        <div class="min-w-0 flex-1">
            @if ($heading)
                <h3 class="type-subheading wrap-anywhere text-ink" data-file-preview-name>{{ $name }}</h3>
            @endif
            @if ($set->count() > 1 && $index !== false)
                <p class="type-meta" data-file-preview-position>{{ $index + 1 }} of {{ $set->count() }}</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if ($set->count() > 1 && $index !== false)
                <x-ui.button variant="outline" size="sm" :disabled="! $previous" :aria-label="$previous ? 'Previous file: '.$previous['name'] : 'Previous file'" x-on:click="go({{ Js::from($previous['key'] ?? null) }})" data-file-preview-prev>
                    <x-ui.icon name="chevron-left" />Previous
                </x-ui.button>
                <x-ui.button variant="outline" size="sm" :disabled="! $next" :aria-label="$next ? 'Next file: '.$next['name'] : 'Next file'" x-on:click="go({{ Js::from($next['key'] ?? null) }})" data-file-preview-next>
                    Next<x-ui.icon name="chevron-right" />
                </x-ui.button>
            @endif
            @if ($url)
                <x-ui.button variant="outline" size="sm" :href="$url" target="_blank" rel="noopener" data-file-preview-open>Open in new tab<span class="sr-only">: {{ $name }}</span></x-ui.button>
            @endif
            @if ($download)
                <x-ui.button size="sm" :href="$download" download data-file-preview-download>Download<span class="sr-only"> {{ $name }}</span></x-ui.button>
            @endif
        </div>
    </div>

    {{-- Keyed by file: a new file replaces the viewer whole, so media players start fresh. --}}
    <div class="min-w-0" data-file-preview-viewer wire:key="file-preview-viewer-{{ md5($kind.'|'.$key) }}">
        @switch ($kind)
            @case ('image')
                <div class="flex flex-col gap-2">
                    <div class="flex flex-wrap items-center gap-1" role="group" aria-label="Zoom">
                        <x-ui.button variant="outline" size="sm" x-on:click="zoomBy(-1)" x-bind:aria-disabled="level <= 0 ? 'true' : null" data-zoom="out" aria-label="Zoom out"><x-ui.icon name="zoom-out" /></x-ui.button>
                        <x-ui.button variant="outline" size="sm" x-on:click="zoomBy(1)" x-bind:aria-disabled="level >= steps.length - 1 ? 'true' : null" data-zoom="in" aria-label="Zoom in"><x-ui.icon name="zoom-in" /></x-ui.button>
                        <x-ui.button variant="outline" size="sm" x-on:click="fit()" x-bind:aria-pressed="level === 0 ? 'true' : 'false'" aria-pressed="true" data-zoom="fit">Fit</x-ui.button>
                        <span class="type-meta tabular-nums" aria-live="polite" x-text="level === 0 ? 'Fitted' : Math.round(steps[level] * 100) + '%'" data-zoom-level>Fitted</span>
                    </div>
                    <div class="ui-file-zoom" tabindex="0" role="region" aria-label="Image: {{ $name }}. Press plus or minus to zoom, 0 to fit." x-ref="zoom" x-bind:data-zoomed="level > 0 ? '' : null" x-bind:style="level > 0 && '--zoom: ' + steps[level]" data-file-zoom>
                        <img src="{{ $url }}" alt="{{ $file['alt'] ?? $name }}" decoding="async" class="ui-file-zoom-image">
                    </div>
                </div>
                @break
            @case ('pdf')
                <object data="{{ $url }}" type="application/pdf" class="block h-[70vh] min-h-80 w-full rounded-md border border-line bg-surface-subtle" aria-label="PDF: {{ $name }}" data-file-pdf>
                    <div class="flex flex-col items-start gap-2 p-4">
                        <p class="type-body text-ink-body">This browser cannot show the PDF here.</p>
                        <x-ui.link :href="$url" target="_blank" rel="noopener">Open {{ $name }} in a new tab</x-ui.link>
                    </div>
                </object>
                @break
            @case ('audio')
                <x-ui.audio :src="$url" :title="$name" :type="$type ?? 'audio/mpeg'" :duration="(int) ($file['duration'] ?? 0)" />
                @break
            @case ('video')
                <x-ui.video :src="$url" :poster="$file['poster'] ?? null" :type="$type ?? 'video/mp4'" :title="$name" />
                @break
            @case ('text')
                <x-ui.code-block :label="$name" max-height="60vh">{{ $text }}</x-ui.code-block>
                @if ($capped)
                    <p class="type-caption mt-2" data-file-preview-capped>Showing the first {{ number_format((int) $maxText) }} characters. Download the file to see all of it.</p>
                @endif
                @break
            @default
                <div class="rounded-md border border-line bg-surface-subtle px-4 py-6" data-file-preview-fallback>
                    <p class="type-body text-ink-body">No preview for this type of file ({{ $label }}).</p>
                    @if ($download)
                        <p class="type-meta mt-1">Download it to open it on your device.</p>
                    @endif
                </div>
        @endswitch
    </div>

    <dl class="grid grid-cols-1 gap-x-6 gap-y-3 sm:grid-cols-2" data-file-preview-details>
        @foreach ($details as [$term, $value, $as])
            <div class="min-w-0">
                <dt class="type-meta">{{ $term }}</dt>
                <dd @class(['wrap-anywhere text-ink', 'type-numeric' => $as === 'numeric', 'type-body' => $as !== 'numeric'])>
                    @if ($as === 'time')
                        <x-ui.time :value="$value" with-time :tz="$tz" :now="$now" />
                    @else
                        {{ $value }}
                    @endif
                </dd>
            </div>
        @endforeach
    </dl>
</div>
