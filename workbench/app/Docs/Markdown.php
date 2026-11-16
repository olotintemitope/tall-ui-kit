<?php

namespace Workbench\App\Docs;

use Illuminate\Support\Str;

/**
 * Renders the repo's own Markdown docs (README, docs/*.md) into the site, so guides
 * never drift from them: links to other docs become site links, wide tables scroll
 * inside a focusable region, and headings get ids.
 */
final class Markdown
{
    private static int $tables = 0;

    private const LINKS = [
        'rules.md' => 'rules',
        'tokens.md' => 'tokens',
        'theming.md' => 'index',
        'accessibility.md' => 'audit',
        'patterns.md' => 'patterns/forms',
        'motion.md' => 'tokens',
        'performance.md' => 'audit',
    ];

    /** A whole file, or the part under one heading (up to the next heading of the same level). */
    public static function file(string $relative, ?string $section = null, bool $dropTitle = true): string
    {
        $text = (string) file_get_contents(dirname(__DIR__, 3).'/'.$relative);

        if ($section !== null) {
            $text = self::section($text, $section);
        } elseif ($dropTitle) {
            $text = preg_replace('/^# .*\R/', '', $text, 1);
        }

        return self::render($text);
    }

    public static function render(string $markdown): string
    {
        $html = Str::markdown($markdown, ['html_input' => 'escape', 'allow_unsafe_links' => false]);

        // Links to other docs files: site pages when there is one, plain text otherwise.
        $html = preg_replace_callback('#<a href="([^"]+)">(.*?)</a>#s', function (array $m): string {
            $href = html_entity_decode($m[1]);
            if (preg_match('#^(https?:|mailto:|\#)#', $href)) {
                return $m[0];
            }
            $file = basename(Str::before($href, '#'));
            $fragment = Str::contains($href, '#') ? Str::after($href, '#') : null;
            if (isset(self::LINKS[$file])) {
                return '<a href="'.e(Site::url(self::LINKS[$file], $fragment)).'">'.$m[2].'</a>';
            }
            if (str_starts_with($href, 'components/') || str_contains($href, '/components/') || $file === 'README.md') {
                return '<a href="'.e(Site::url('components/index')).'">'.$m[2].'</a>';
            }

            return $m[2];
        }, $html);

        // Headings get ids for in-page links.
        $html = preg_replace_callback('#<h([2-4])>(.*?)</h\1>#s', fn ($m) => '<h'.$m[1].' id="'.Str::slug(strip_tags($m[2])).'">'.$m[2].'</h'.$m[1].'>', $html);

        // Tables scroll inside their own focusable, named region (the kit's table rule).
        // Named after the heading above it (unique per page through a running count).
        $html = preg_replace_callback('#(?:<h[2-4][^>]*>(.*?)</h[2-4]>\s*(?:<p>.*?</p>\s*)*)?<table>#s', function (array $m) {
            self::$tables++;
            $name = trim(strip_tags($m[1] ?? '')) ?: 'Reference';

            return ($m[0] !== '<table>' ? substr($m[0], 0, -7) : '').'<div class="overflow-x-auto rounded-md focus-ring" tabindex="0" role="region" aria-label="'.e($name.', table '.self::$tables).'"><table>';
        }, $html);
        $html = str_replace('</table>', '</table></div>', $html);

        // Code blocks scroll sideways: make them focusable so keyboards can scroll them.
        return str_replace('<pre>', '<pre tabindex="0">', $html);
    }

    private static function section(string $text, string $heading): string
    {
        if (! preg_match('/^(#{1,4}) '.preg_quote($heading, '/').'\s*$/m', $text, $m, PREG_OFFSET_CAPTURE)) {
            return '';
        }
        $level = strlen($m[1][0]);
        $start = $m[0][1] + strlen($m[0][0]);
        $rest = substr($text, $start);
        if (preg_match('/^#{1,'.$level.'} /m', $rest, $n, PREG_OFFSET_CAPTURE)) {
            $rest = substr($rest, 0, $n[0][1]);
        }

        return trim($rest);
    }
}
