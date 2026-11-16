{{-- Workbench gallery shell: theme switch (?theme=), mode toggle (x-ui.theme-toggle; ?scheme= sets it from the server), page tabs, the kit's toaster. --}}
@php
    if (in_array(request('theme'), ['neutral', 'newbanger'], true)) {
        session(['gallery-theme' => request('theme')]);
    }
    $theme = session('gallery-theme', 'neutral');
    $pages = [
        'index' => ['label' => 'Basics', 'href' => route('gallery.index')],
        'forms' => ['label' => 'Forms', 'href' => route('gallery.forms')],
        'overlays' => ['label' => 'Overlays', 'href' => route('gallery.overlays')],
        'data' => ['label' => 'Data display', 'href' => route('gallery.data')],
        'charts' => ['label' => 'Charts', 'href' => route('gallery.charts')],
        'tables' => ['label' => 'Data table', 'href' => route('gallery.tables')],
        'workflow' => ['label' => 'Workflow', 'href' => route('gallery.workflow')],
        'checklist' => ['label' => 'Checklist', 'href' => route('gallery.checklist')],
        'chat' => ['label' => 'Chat', 'href' => route('gallery.chat')],
        'community' => ['label' => 'Community', 'href' => route('gallery.community')],
        'dates' => ['label' => 'Dates', 'href' => route('gallery.dates')],
        'sidebar' => ['label' => 'Sidebar', 'href' => route('gallery.sidebar')],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Gallery' }} · TALL UI kit</title>
    <x-ui.head :scheme="in_array(request('scheme'), ['light', 'dark', 'system'], true) ? request('scheme') : null" />
    @if ($theme === 'newbanger')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Space+Grotesk:wght@500;600&display=swap">
    @endif
    <link rel="stylesheet" href="{{ route('kit-assets', $theme.'.css') }}">
    <script type="module" src="{{ route('kit-assets', 'gallery.js') }}"></script>
    @livewireStyles
</head>
<body class="min-h-screen bg-surface font-body text-ink" data-theme="{{ $theme }}">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-md focus:bg-surface focus:px-3 focus:py-2 focus-ring">Skip to content</a>
    <x-ui.progress-bar />
    <header class="border-b border-line">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-4 pt-4 sm:px-6">
            <p class="type-heading">TALL UI kit</p>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
                <nav aria-label="Theme" class="flex items-center gap-2">
                    <span class="type-meta">Theme</span>
                    <x-ui.button size="sm" :variant="$theme === 'neutral' ? 'outline' : 'ghost'" :href="request()->url().'?theme=neutral'" :aria-current="$theme === 'neutral' ? 'true' : null">Neutral</x-ui.button>
                    <x-ui.button size="sm" :variant="$theme === 'newbanger' ? 'outline' : 'ghost'" :href="request()->url().'?theme=newbanger'" :aria-current="$theme === 'newbanger' ? 'true' : null">NewBanger</x-ui.button>
                </nav>
                <x-ui.theme-toggle size="sm" label="Mode" />
            </div>
        </div>
        <div class="mx-auto max-w-6xl px-4 sm:px-6">
            <x-ui.tabs :tabs="$pages" label="Gallery pages" />
        </div>
    </header>
    <main id="main" tabindex="-1" class="mx-auto max-w-6xl px-4 py-6 sm:px-6">
        {{ $slot }}
    </main>
    <x-ui.toaster />
    @livewireScripts
</body>
</html>
