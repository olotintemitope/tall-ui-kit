<?php

namespace Workbench\App\Livewire\Files;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use TallUiKit\Support\Files;
use Workbench\App\Files\DemoDrive;

/**
 * Workbench /files: x-ui.file-manager on the session drive (DemoDrive). Public state is
 * ids and scalars; every id from the browser is checked against the drive. Uploads go
 * to Livewire's temporary disk (the workbench test disk), are recorded, then deleted.
 */
class ManagerDemo extends Component
{
    use WithFileUploads;

    public ?string $folder = null;

    public string $sort = 'name';

    public string $direction = 'asc';

    public int $perPage = 24;

    public ?string $preview = null;

    /** @var array<int, TemporaryUploadedFile> */
    public array $uploads = [];

    public ?string $uploadFolder = null;

    public function openItem(?string $id): void
    {
        if ($id === null) {
            $this->folder = null;
            $this->perPage = 24;

            return;
        }
        $item = DemoDrive::get($id);
        abort_if($item === null, 404);
        if ($item['folder']) {
            $this->folder = $id;
            $this->perPage = 24;
            $this->preview = null;

            return;
        }
        $this->preview = $id;
        $this->dispatch('open-dialog', name: 'manager-preview');
    }

    public function previewFile(string $id): void
    {
        $item = DemoDrive::get($id);
        abort_if($item === null || $item['folder'], 404);
        $this->preview = $id;
    }

    public function rename(string $id, string $name): ?string
    {
        return DemoDrive::rename($id, $name);
    }

    /** @param list<string> $ids */
    public function move(array $ids, string $target): ?string
    {
        return DemoDrive::move(array_map('strval', $ids), $target === 'root' ? null : $target);
    }

    /** @param list<string> $ids */
    public function delete(array $ids): ?string
    {
        return DemoDrive::delete(array_map('strval', $ids));
    }

    public function createFolder(string $name): ?string
    {
        return DemoDrive::create($this->folder, $name);
    }

    /** @param list<string> $ids */
    public function download(array $ids): void
    {
        $names = collect($ids)->map(fn ($id) => DemoDrive::get((string) $id)['name'] ?? null)->filter();
        abort_if($names->isEmpty(), 404);
        $this->dispatch('toast', variant: 'info', title: 'A zip of '.$names->count().' items would download here.');
    }

    public function sortBy(string $field, string $direction): void
    {
        $this->sort = in_array($field, ['name', 'modified', 'size'], true) ? $field : 'name';
        $this->direction = $direction === 'desc' ? 'desc' : 'asc';
    }

    public function loadMore(): void
    {
        $this->perPage = min($this->perPage + 24, 500);
    }

    public function updatedUploads(): void
    {
        $this->validate(['uploads' => 'array|max:10', 'uploads.*' => 'file|max:10240']);
        $dropped = DemoDrive::get($this->uploadFolder);
        $target = $dropped && $dropped['folder'] ? $dropped['id'] : $this->folder;
        foreach ($this->uploads as $file) {
            DemoDrive::addFile($target, $file->getClientOriginalName(), (int) $file->getSize(), $file->getMimeType());
            $file->delete();
        }
        $this->uploads = [];
        $this->uploadFolder = null;
    }

    public function render(): View
    {
        $children = DemoDrive::children($this->folder, $this->sort, $this->direction);
        $items = array_map(fn ($i) => [
            'id' => $i['id'],
            'name' => $i['name'],
            'folder' => $i['folder'],
            'size' => $i['size'],
            'count' => $i['count'],
            'modified' => $i['modified'],
            'type' => $i['type'],
            'href' => $i['folder'] ? null : route('files-demo.file', ['id' => $i['id'], 'download' => 1], false),
            'thumb' => ! $i['folder'] && Files::kind($i['name'], $i['type']) === 'image' ? route('files-demo.file', $i['id'], false) : null,
        ], array_slice($children, 0, $this->perPage));

        $previewItem = DemoDrive::get($this->preview);
        $siblings = collect($children)->where('folder', false)->values();

        return view('livewire.files.manager-demo', [
            'items' => $items,
            'total' => count($children),
            'trail' => DemoDrive::trail($this->folder),
            'tree' => DemoDrive::tree(),
            'previewFile' => $previewItem ? PreviewDemo::fileFor($previewItem) : null,
            'previewSet' => $siblings->map(fn ($i) => ['key' => $i['id'], 'name' => $i['name']])->all(),
        ]);
    }
}
