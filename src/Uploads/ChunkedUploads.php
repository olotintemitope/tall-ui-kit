<?php

namespace TallUiKit\Uploads;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Chunked uploads for x-ui.file `chunked` (docs/components/forms.md, "Chunked uploads").
 * Livewire 4 uploads each file in one request, so very large files go to this small
 * endpoint instead, in pieces, and resume after a network drop.
 *
 * Contract (JSON; the app registers the routes behind its own middleware):
 *   POST   {prefix}            {name, size, type}  201 {id, offset: 0, chunk_size}
 *   GET    {prefix}/{id}                           200 {id, offset, size, done}
 *   PATCH  {prefix}/{id}       raw bytes, header Upload-Offset (the offset they start at)
 *                                                  200 {id, offset, done} or 409 {offset}
 *   DELETE {prefix}/{id}                           204
 *
 * Storage: one folder per upload on a local disk (config tall-ui-kit.chunked_uploads),
 * holding meta.json and the bytes so far. A piece is appended only at the current end
 * (the offset must match), so the pieces assemble in order as they arrive; the last one
 * turns `data.part` into `data`. Every upload belongs to the user who started it (the
 * auth id, else the session); another user gets 404. ChunkedUploads::find($id) gives the
 * app the finished file; `tall-ui-kit:prune-uploads` (schedule it) deletes old ones.
 */
final class ChunkedUploads
{
    /** Registers the four routes, named tall-ui-kit.uploads.{store,show,update,destroy}. */
    public static function routes(string $prefix = 'ui/uploads', array $middleware = ['web', 'auth']): void
    {
        Route::middleware($middleware)->prefix(trim($prefix, '/'))->name('tall-ui-kit.uploads.')->group(function () {
            Route::post('/', [ChunkedUploadController::class, 'store'])->name('store');
            Route::get('/{upload}', [ChunkedUploadController::class, 'show'])->whereUuid('upload')->name('show');
            Route::patch('/{upload}', [ChunkedUploadController::class, 'update'])->whereUuid('upload')->name('update');
            Route::delete('/{upload}', [ChunkedUploadController::class, 'destroy'])->whereUuid('upload')->name('destroy');
        });
    }

    public static function disk(): Filesystem
    {
        return Storage::disk(config('tall-ui-kit.chunked_uploads.disk', 'local'));
    }

    public static function directory(): string
    {
        return trim((string) config('tall-ui-kit.chunked_uploads.directory', 'tall-ui-kit-chunks'), '/');
    }

    public static function chunkSize(): int
    {
        return max(64 * 1024, (int) config('tall-ui-kit.chunked_uploads.chunk_size', 2 * 1024 * 1024));
    }

    public static function maxSize(): int
    {
        return (int) config('tall-ui-kit.chunked_uploads.max_size', 2 * 1024 * 1024 * 1024);
    }

    /** Who owns uploads started in this request: the user, else the session. */
    public static function owner(?Request $request = null): string
    {
        $request ??= request();
        $user = $request->user();
        if ($user) {
            return 'user:'.$user->getAuthIdentifier();
        }

        return 'session:'.hash('sha256', $request->hasSession() ? $request->session()->getId() : '');
    }

    public static function create(string $name, int $size, ?string $type, ?Request $request = null): ChunkedUpload
    {
        $id = (string) Str::uuid();
        $upload = new ChunkedUpload($id, [
            'owner' => self::owner($request),
            'name' => self::cleanName($name),
            'size' => $size,
            'type' => $type,
            'chunk_size' => self::chunkSize(),
            'created_at' => now()->getTimestamp(),
            'done' => false,
        ]);
        self::disk()->put($upload->folder().'/data.part', '');
        $upload->save();

        return $upload;
    }

    /**
     * The upload with this id if the current user (or session) started it, else null.
     * Use it in the Livewire component with the ids x-ui.file set on the property.
     */
    public static function find(?string $id, ?Request $request = null): ?ChunkedUpload
    {
        if (! is_string($id) || ! Str::isUuid($id)) {
            return null;
        }
        $meta = self::disk()->get(self::directory().'/'.$id.'/meta.json');
        $data = $meta ? json_decode($meta, true) : null;
        if (! is_array($data) || ! hash_equals((string) ($data['owner'] ?? ''), self::owner($request))) {
            return null;
        }

        return new ChunkedUpload($id, $data);
    }

    /** Deletes uploads (finished or not) older than `hours`. Returns how many. */
    public static function prune(?int $hours = null): int
    {
        $hours ??= (int) config('tall-ui-kit.chunked_uploads.expire_after', 24);
        $cutoff = now()->subHours($hours)->getTimestamp();
        $disk = self::disk();
        $count = 0;
        foreach ($disk->directories(self::directory()) as $folder) {
            $meta = json_decode((string) $disk->get($folder.'/meta.json'), true);
            $created = (int) ($meta['created_at'] ?? 0);
            if ($created < $cutoff) {
                $disk->deleteDirectory($folder);
                $count++;
            }
        }

        return $count;
    }

    /** A file name safe to keep: no path, no control characters, at most 255 bytes. */
    public static function cleanName(string $name): string
    {
        $name = basename(str_replace('\\', '/', $name));
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? '';
        $name = trim($name, " .\t");

        return $name === '' ? 'upload' : mb_strcut($name, 0, 255);
    }
}
