<?php

namespace Workbench\App\Livewire\Files;

use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use TallUiKit\Support\Files;
use TallUiKit\Uploads\ChunkedUploads;

/**
 * Workbench /files: the upgraded x-ui.file. Documents use Livewire's own uploads (to the
 * workbench test disk) with limits, reorder and paste; the large file goes through the
 * kit's chunk endpoint and only its ids reach this component.
 */
class UploadFieldsDemo extends Component
{
    use WithFileUploads;

    /** @var array<int, TemporaryUploadedFile> */
    public array $documents = [];

    public $photo = null;

    /** @var list<string> */
    public array $large = [];

    public function updatedDocuments(): void
    {
        $this->validate(['documents' => 'array|max:4', 'documents.*' => 'file|max:5120']);
    }

    /** @param list<int> $order the indexes in their old order */
    public function reorderDocuments(array $order): void
    {
        $order = array_map('intval', $order);
        $valid = count($order) === count($this->documents) && count(array_unique($order)) === count($order)
            && collect($order)->every(fn ($i) => isset($this->documents[$i]));
        abort_unless($valid, 422);
        $this->documents = array_map(fn ($i) => $this->documents[$i], $order);
    }

    public function removeDocument(int $index): void
    {
        if (isset($this->documents[$index])) {
            $this->documents[$index]->delete();
            array_splice($this->documents, $index, 1);
        }
    }

    public function updatedLarge(): void
    {
        // Ids come from the browser: keep only uploads this session started and finished.
        $this->large = collect($this->large)->filter(fn ($id) => ChunkedUploads::find((string) $id)?->done())->map(fn ($id) => (string) $id)->values()->all();
    }

    public function removeLarge(string $id): void
    {
        ChunkedUploads::find($id)?->delete();
        $this->large = array_values(array_diff($this->large, [$id]));
    }

    public function render(): View
    {
        return view('livewire.files.upload-fields-demo', [
            'documentRows' => collect($this->documents)->map(fn (TemporaryUploadedFile $f, $i) => [
                'key' => (string) $i, 'name' => $f->getClientOriginalName(), 'size' => $f->getSize(), 'status' => 'done',
            ])->values()->all(),
            'largeRows' => collect($this->large)->map(fn ($id) => ChunkedUploads::find($id))->filter()->map(fn ($u) => [
                'key' => $u->id, 'name' => $u->name(), 'size' => $u->size(), 'status' => 'done', 'meta' => Files::bytes($u->size()).' · '.($u->mimeType() ?? 'unknown type'),
            ])->values()->all(),
        ]);
    }
}
