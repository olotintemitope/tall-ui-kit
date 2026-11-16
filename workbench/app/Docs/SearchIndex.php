<?php

namespace Workbench\App\Docs;

/**
 * The client-side search index: one entry per page and per component, with the tags,
 * prop names and summary as keywords. Written as a script that sets
 * window.docsIndex, so it loads from any static host without fetch() and no search
 * service is involved.
 */
final class SearchIndex
{
    /** @return list<array{t: string, u: string, s: string, k: string}> */
    public static function entries(): array
    {
        $entries = [];
        foreach (Catalog::GUIDES as $slug => $title) {
            $entries[] = ['t' => $title, 'u' => $slug, 's' => 'Guide', 'k' => self::headings('guides.'.$slug)];
        }
        foreach (Catalog::PATTERNS as $slug => $title) {
            $entries[] = ['t' => $title, 'u' => 'patterns/'.$slug, 's' => 'Pattern', 'k' => self::headings('patterns.'.$slug)];
        }
        foreach (Catalog::components() as $slug => $page) {
            $words = [];
            foreach ($page['components'] as $name) {
                $source = Catalog::source($name);
                $words[] = Catalog::tag($name);
                $words[] = implode(' ', array_column($source->props, 'name'));
                $words[] = $source->summary();
            }
            $entries[] = ['t' => $page['title'], 'u' => 'components/'.$slug, 's' => $page['family'], 'k' => trim(preg_replace('/\s+/', ' ', implode(' ', $words)))];
        }

        return $entries;
    }

    public static function script(): string
    {
        return 'window.docsIndex = '.json_encode(self::entries(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).";\n";
    }

    /** The h2 and h3 text of a docs view, for keywords. */
    private static function headings(string $view): string
    {
        $file = dirname(__DIR__, 2).'/resources/views/docs/'.str_replace('.', '/', $view).'.blade.php';
        if (! is_file($file)) {
            return '';
        }
        preg_match_all('/<h[23][^>]*>(.*?)<\/h[23]>|title="([^"]+)"/s', (string) file_get_contents($file), $m);

        return trim(strip_tags(implode(' ', array_filter(array_merge($m[1], $m[2])))));
    }
}
