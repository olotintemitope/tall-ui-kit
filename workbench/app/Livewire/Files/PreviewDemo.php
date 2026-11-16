<?php

namespace Workbench\App\Livewire\Files;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use TallUiKit\Support\Files;
use Workbench\App\Files\DemoDrive;
use Workbench\App\Files\DemoFileController;

/**
 * Workbench /files: x-ui.file-preview as a panel and in a dialog, Previous / Next across
 * the root folder's files. The key from the browser must be one of the set.
 */
class PreviewDemo extends Component
{
    public string $current = 'cover';

    /** The preview's `file` data for a drive item: URLs the app owns, text it reads itself. */
    public static function fileFor(array $item): array
    {
        $kind = Files::kind($item['name'], $item['type']);

        return [
            'key' => $item['id'],
            'name' => $item['name'],
            'url' => route('files-demo.file', $item['id']),
            'download' => route('files-demo.file', ['id' => $item['id'], 'download' => 1]),
            'type' => $kind === 'image' ? 'image/svg+xml' : $item['type'],
            'kind' => $kind,
            'size' => $item['size'],
            'created' => $item['created'],
            'modified' => $item['modified'],
            'text' => $kind === 'text' ? DemoFileController::text($item['name']) : null,
            'fields' => ['Owner' => 'Ada Obi', 'Shared with' => '3 people'],
        ];
    }

    /** @return list<array<string, mixed>> */
    private function set(): array
    {
        return collect(DemoDrive::children(null))->where('folder', false)->values()->all();
    }

    public function show(string $key): void
    {
        abort_unless(collect($this->set())->contains('id', $key), 404);
        $this->current = $key;
    }

    public function render(): View
    {
        $set = $this->set();
        $item = collect($set)->firstWhere('id', $this->current) ?? $set[0] ?? null;

        return view('livewire.files.preview-demo', [
            'file' => $item ? self::fileFor($item) : [],
            'files' => array_map(fn ($i) => ['key' => $i['id'], 'name' => $i['name']], $set),
        ]);
    }
}
