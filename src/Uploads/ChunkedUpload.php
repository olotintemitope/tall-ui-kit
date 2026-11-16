<?php

namespace TallUiKit\Uploads;

use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * One chunked upload (see ChunkedUploads): its name, declared size and type, the bytes
 * received so far, and, once done, the assembled file. The name and declared type came
 * from the browser: validate the content (mimeType() reads it) before keeping the file.
 */
final class ChunkedUpload
{
    /** @param array<string, mixed> $meta */
    public function __construct(public readonly string $id, private array $meta) {}

    public function folder(): string
    {
        return ChunkedUploads::directory().'/'.$this->id;
    }

    public function name(): string
    {
        return (string) $this->meta['name'];
    }

    public function size(): int
    {
        return (int) $this->meta['size'];
    }

    /** The type the browser declared (untrusted). */
    public function declaredType(): ?string
    {
        return $this->meta['type'] ?? null;
    }

    public function owner(): string
    {
        return (string) $this->meta['owner'];
    }

    public function done(): bool
    {
        return (bool) ($this->meta['done'] ?? false);
    }

    /** Bytes received so far. */
    public function offset(): int
    {
        $disk = ChunkedUploads::disk();
        $file = $this->folder().'/'.($this->done() ? 'data' : 'data.part');

        return $disk->exists($file) ? (int) $disk->size($file) : 0;
    }

    /** The local path of the bytes (the part file until done). */
    public function path(): string
    {
        return ChunkedUploads::disk()->path($this->folder().'/'.($this->done() ? 'data' : 'data.part'));
    }

    /** The type read from the content (finfo), not the one the browser sent. */
    public function mimeType(): ?string
    {
        return $this->done() ? ((new File($this->path()))->getMimeType() ?: null) : null;
    }

    /** The finished file as an UploadedFile, for the usual validation rules. */
    public function toUploadedFile(): ?UploadedFile
    {
        return $this->done() ? new UploadedFile($this->path(), $this->name(), $this->mimeType(), null, true) : null;
    }

    /**
     * Copies the finished file to `directory` on `disk` (the default disk if null) under
     * a random name with the original extension, deletes the upload, and returns the path.
     */
    public function store(string $directory, ?string $disk = null): string|false
    {
        if (! $this->done()) {
            return false;
        }
        $ext = pathinfo($this->name(), PATHINFO_EXTENSION);
        $path = Storage::disk($disk)->putFileAs($directory, new File($this->path()), bin2hex(random_bytes(16)).($ext !== '' ? '.'.strtolower($ext) : ''));
        if ($path !== false) {
            $this->delete();
        }

        return $path;
    }

    /** Appends bytes from a stream; returns the new offset. Completes the upload at its size. */
    public function append($stream, int $max): int
    {
        $disk = ChunkedUploads::disk();
        $part = $disk->path($this->folder().'/data.part');
        $out = fopen($part, 'ab');
        if ($out === false) {
            throw new \RuntimeException('The upload could not be written.');
        }
        try {
            if (! flock($out, LOCK_EX)) {
                throw new \RuntimeException('The upload is busy.');
            }
            stream_copy_to_stream($stream, $out, $max);
            fflush($out);
            flock($out, LOCK_UN);
        } finally {
            fclose($out);
        }
        clearstatcache(true, $part);
        $offset = (int) filesize($part);
        if ($offset >= $this->size()) {
            $disk->move($this->folder().'/data.part', $this->folder().'/data');
            $this->meta['done'] = true;
            $this->save();
        }

        return $offset;
    }

    public function save(): void
    {
        ChunkedUploads::disk()->put($this->folder().'/meta.json', json_encode($this->meta, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    public function delete(): void
    {
        ChunkedUploads::disk()->deleteDirectory($this->folder());
    }

    /** @return array{id: string, offset: int, size: int, done: bool} */
    public function toArray(): array
    {
        return ['id' => $this->id, 'offset' => $this->offset(), 'size' => $this->size(), 'done' => $this->done()];
    }
}
