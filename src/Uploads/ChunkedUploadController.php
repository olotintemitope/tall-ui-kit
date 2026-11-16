<?php

namespace TallUiKit\Uploads;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * The chunk endpoint behind x-ui.file `chunked` (see ChunkedUploads for the contract).
 * Register it with ChunkedUploads::routes() behind the app's auth and throttle
 * middleware. Every id is checked against the caller (404 for anyone else's upload),
 * bodies are capped at the chunk size, and a piece is accepted only at the current end.
 */
final class ChunkedUploadController
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'size' => ['required', 'integer', 'min:1', 'max:'.ChunkedUploads::maxSize()],
            'type' => ['nullable', 'string', 'max:255'],
        ]);
        $upload = ChunkedUploads::create($data['name'], (int) $data['size'], $data['type'] ?? null, $request);

        return response()->json(['id' => $upload->id, 'offset' => 0, 'chunk_size' => ChunkedUploads::chunkSize()], 201);
    }

    public function show(Request $request, string $upload): JsonResponse
    {
        return response()->json($this->upload($request, $upload)->toArray());
    }

    public function update(Request $request, string $upload): JsonResponse
    {
        $file = $this->upload($request, $upload);
        if ($file->done()) {
            return response()->json(['id' => $file->id, 'offset' => $file->size(), 'done' => true]);
        }
        $offset = $request->header('Upload-Offset');
        if (! is_string($offset) || ! ctype_digit($offset)) {
            return response()->json(['message' => 'The Upload-Offset header is missing.'], 400);
        }
        $current = $file->offset();
        if ((int) $offset !== $current) {
            return response()->json(['message' => 'The offset does not match.', 'offset' => $current], 409);
        }
        $length = (int) $request->header('Content-Length', '0');
        $room = min(ChunkedUploads::chunkSize(), $file->size() - $current);
        if ($length > $room) {
            return response()->json(['message' => 'The chunk is too large.', 'offset' => $current], 413);
        }
        $stream = $request->getContent(true);
        $offset = $file->append($stream, $room);

        return response()->json(['id' => $file->id, 'offset' => $offset, 'done' => $offset >= $file->size()]);
    }

    public function destroy(Request $request, string $upload): Response
    {
        $this->upload($request, $upload)->delete();

        return response()->noContent();
    }

    private function upload(Request $request, string $id): ChunkedUpload
    {
        $upload = ChunkedUploads::find($id, $request);
        abort_if($upload === null, 404);

        return $upload;
    }
}
