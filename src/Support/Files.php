<?php

namespace TallUiKit\Support;

/**
 * Shared helpers for the file components (x-ui.file, x-ui.file-card, x-ui.attachment-list,
 * x-ui.file-manager, x-ui.file-preview, x-ui.upload-progress): the short type label
 * ("PDF", "JPG", "MP3"), byte sizes in the kit's style, the preview kind of a file, and
 * the hint x-ui.file writes from `accept` and its limits. resources/js/file-core.js
 * mirrors typeLabel(), bytes() and the size parsing.
 *
 * Names and types come from users: these helpers only read them; the views escape them.
 */
final class Files
{
    /** Kinds x-ui.file-preview can show. */
    public const KINDS = ['image', 'pdf', 'audio', 'video', 'text', 'other'];

    private const TEXT_EXTENSIONS = ['txt', 'md', 'csv', 'tsv', 'json', 'xml', 'yml', 'yaml', 'log', 'ini', 'env', 'php', 'js', 'mjs', 'ts', 'tsx', 'jsx', 'css', 'scss', 'html', 'htm', 'vue', 'blade', 'sql', 'sh', 'py', 'rb', 'go', 'rs', 'java', 'kt', 'swift', 'c', 'h', 'cpp', 'cs', 'toml', 'srt', 'vtt'];

    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg', 'bmp', 'ico'];

    private const AUDIO_EXTENSIONS = ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac', 'flac', 'opus', 'weba'];

    private const VIDEO_EXTENSIONS = ['mp4', 'm4v', 'webm', 'ogv', 'mov'];

    /** Mime subtypes whose usual extension is not the subtype itself. */
    private const MIME_LABELS = [
        'image/jpeg' => 'JPG', 'image/svg+xml' => 'SVG', 'audio/mpeg' => 'MP3', 'audio/mp4' => 'M4A',
        'video/quicktime' => 'MOV', 'text/plain' => 'TXT', 'text/markdown' => 'MD', 'application/msword' => 'DOC',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'DOCX',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'XLSX',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'PPTX',
        'application/vnd.ms-excel' => 'XLS', 'application/x-zip-compressed' => 'ZIP', 'application/gzip' => 'GZ',
    ];

    /** The file's extension, lower case, or null. */
    public static function extension(?string $name): ?string
    {
        $name = (string) $name;
        $dot = strrpos($name, '.');
        if ($dot === false || $dot === 0 || $dot === strlen($name) - 1) {
            return null;
        }
        $ext = strtolower(substr($name, $dot + 1));

        return preg_match('/^[a-z0-9]{1,8}$/', $ext) ? $ext : null;
    }

    /**
     * The short type label: the extension in capitals, at most 4 characters ("PDF",
     * "JPEG" is shown as "JPG", "MP3"), else one from the mime type, else "FILE".
     * A folder is "DIR" only if asked: folders show the folder icon instead.
     */
    public static function typeLabel(?string $name, ?string $mime = null): string
    {
        $ext = self::extension($name);
        if ($ext === 'jpeg') {
            return 'JPG';
        }
        if ($ext !== null && strlen($ext) <= 4) {
            return strtoupper($ext);
        }
        $mime = strtolower(trim((string) $mime));
        if (isset(self::MIME_LABELS[$mime])) {
            return self::MIME_LABELS[$mime];
        }
        if (preg_match('#^[a-z]+/(?:x-)?([a-z0-9]{1,4})$#', $mime, $m)) {
            return strtoupper($m[1]);
        }

        return 'FILE';
    }

    /** What x-ui.file-preview shows: image, pdf, audio, video, text or other. */
    public static function kind(?string $name, ?string $mime = null): string
    {
        $mime = strtolower(trim((string) $mime));
        $ext = self::extension($name);

        return match (true) {
            $mime === 'application/pdf' || $ext === 'pdf' => 'pdf',
            str_starts_with($mime, 'image/') || in_array($ext, self::IMAGE_EXTENSIONS, true) => 'image',
            str_starts_with($mime, 'audio/') || in_array($ext, self::AUDIO_EXTENSIONS, true) => 'audio',
            str_starts_with($mime, 'video/') || in_array($ext, self::VIDEO_EXTENSIONS, true) => 'video',
            str_starts_with($mime, 'text/') || in_array($mime, ['application/json', 'application/xml', 'application/x-yaml'], true) || in_array($ext, self::TEXT_EXTENSIONS, true) => 'text',
            default => 'other',
        };
    }

    /** "240 KB", "2.4 MB", "980 B" (powers of 1000, one decimal, trailing zero dropped). */
    public static function bytes(int|float|null $n): ?string
    {
        if ($n === null || $n < 0) {
            return null;
        }
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($n >= 1000 && $i < count($units) - 1) {
            $n /= 1000;
            $i++;
        }

        return ($i === 0 ? number_format($n) : rtrim(rtrim(number_format($n, 1), '0'), '.')).' '.$units[$i];
    }

    /** Bytes from 10485760, "10MB", "10 MB", "500kb", "1.5G" (powers of 1024 for units, as Laravel's max rule uses KB = 1024). */
    public static function parseSize(int|float|string|null $size): ?int
    {
        if ($size === null || $size === '') {
            return null;
        }
        if (is_int($size) || is_float($size) || ctype_digit((string) $size)) {
            return (int) $size;
        }
        if (! preg_match('/^\s*([0-9]+(?:\.[0-9]+)?)\s*([kmgt]?)i?b?\s*$/i', (string) $size, $m)) {
            return null;
        }
        $power = ['' => 0, 'k' => 1, 'm' => 2, 'g' => 3, 't' => 4][strtolower($m[2])];

        return (int) round((float) $m[1] * (1024 ** $power));
    }

    /** "10 MB" for a byte limit (powers of 1024, so it matches what the user typed). */
    public static function limit(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        $n = (float) $bytes;
        while ($n >= 1024 && $i < count($units) - 1) {
            $n /= 1024;
            $i++;
        }

        return rtrim(rtrim(number_format($n, 1), '0'), '.').' '.$units[$i];
    }

    /**
     * Human words for an `accept` list: ".pdf,image/*" => "PDF or images". Unknown
     * patterns pass through in capitals.
     */
    public static function acceptWords(?string $accept): ?string
    {
        $parts = collect(explode(',', (string) $accept))->map(fn ($p) => strtolower(trim($p)))->filter()->values();
        if ($parts->isEmpty()) {
            return null;
        }
        $words = $parts->map(fn (string $p) => match (true) {
            $p === 'image/*' => 'images',
            $p === 'audio/*' => 'audio',
            $p === 'video/*' => 'video',
            $p === 'text/*' => 'text files',
            str_starts_with($p, '.') => strtoupper(substr($p, 1)) === 'JPEG' ? 'JPG' : strtoupper(substr($p, 1)),
            default => self::typeLabel(null, $p),
        })->unique()->values();

        return $words->count() === 1 ? $words[0] : $words->slice(0, -1)->implode(', ').' or '.$words->last();
    }

    /**
     * The hint x-ui.file writes when none is given and it has limits:
     * "PDF or images, up to 10 MB each. At most 5 files, 25 MB in total."
     */
    public static function hint(?string $accept, ?int $maxSize, ?int $maxFiles, ?int $maxTotal, bool $multiple): ?string
    {
        $types = self::acceptWords($accept);
        $first = $types ?? ($multiple ? 'Any files' : 'Any file');
        if ($maxSize) {
            $first .= ', up to '.self::limit($maxSize).($multiple ? ' each' : '');
        }
        $second = [];
        if ($multiple && $maxFiles) {
            $second[] = 'At most '.$maxFiles.' '.($maxFiles === 1 ? 'file' : 'files');
        }
        if ($multiple && $maxTotal) {
            $second[] = ($second ? '' : 'Up to ').self::limit($maxTotal).' in total';
        }

        if (! $types && ! $maxSize && ! $second) {
            return null;
        }

        return $first.'.'.($second ? ' '.ucfirst(implode(', ', $second)).'.' : '');
    }
}
