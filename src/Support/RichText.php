<?php

namespace TallUiKit\Support;

use Illuminate\Support\HtmlString;

/**
 * User-written text (chat messages, posts, comments) as safe HTML: every character the
 * user typed is escaped, then URLs, @mentions and #hashtags become links. The text is
 * split into tokens first and each piece is escaped on its own, so no markup the user
 * wrote can survive and no link can be built from half-escaped text. Line breaks are
 * kept by the caller's `whitespace-pre-wrap`.
 *
 * Only http(s) links are made (a bare www. gets https://); trailing punctuation and an
 * unbalanced closing bracket stay outside the link. Mentions and hashtags link only when
 * a URL pattern is given (":name" and ":tag" are replaced, URL-encoded); otherwise they
 * are styled text. Links carry rel="nofollow ugc noopener".
 */
final class RichText
{
    private const PATTERN = '~(?<url>\bhttps?://[^\s<>"\']+|\bwww\.[^\s<>"\']+)'
        .'|(?<=^|[\s(\[{,;])@(?<mention>[A-Za-z0-9_][A-Za-z0-9_.-]{0,38}[A-Za-z0-9_]|[A-Za-z0-9_])'
        .'|(?<=^|[\s(\[{,;])#(?<tag>[\p{L}\p{N}_]{1,64})~u';

    public static function render(?string $text, ?string $mentionUrl = null, ?string $hashtagUrl = null, bool $mentions = true, bool $hashtags = true): HtmlString
    {
        $text = (string) $text;
        if ($text === '') {
            return new HtmlString('');
        }

        $out = '';
        $offset = 0;
        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL);

        foreach ($matches as $m) {
            [$whole, $at] = $m[0];
            $out .= e(substr($text, $offset, $at - $offset));
            $offset = $at + strlen($whole);

            if (($m['url'][0] ?? null) !== null) {
                [$url, $tail] = self::trimUrl($whole);
                $offset -= strlen($tail);
                $href = str_starts_with($url, 'www.') ? 'https://'.$url : $url;
                $out .= '<a href="'.e($href).'" class="ui-rich-link" rel="nofollow ugc noopener">'.e($url).'</a>';
            } elseif (($m['mention'][0] ?? null) !== null && $mentions) {
                $name = $m['mention'][0];
                $out .= $mentionUrl
                    ? '<a href="'.e(str_replace(':name', rawurlencode($name), $mentionUrl)).'" class="ui-rich-mention">@'.e($name).'</a>'
                    : '<span class="ui-rich-mention">@'.e($name).'</span>';
            } elseif (($m['tag'][0] ?? null) !== null && $hashtags) {
                $tag = $m['tag'][0];
                $out .= $hashtagUrl
                    ? '<a href="'.e(str_replace(':tag', rawurlencode($tag), $hashtagUrl)).'" class="ui-rich-tag">#'.e($tag).'</a>'
                    : '<span class="ui-rich-tag">#'.e($tag).'</span>';
            } else {
                $out .= e($whole);
            }
        }

        return new HtmlString($out.e(substr($text, $offset)));
    }

    /** Plain text, shortened at a word to about `$limit` characters (for previews and announcements). */
    public static function plain(?string $text, int $limit = 140): string
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', (string) $text));

        return mb_strlen($flat) <= $limit ? $flat : rtrim(mb_substr($flat, 0, mb_strrpos(mb_substr($flat, 0, $limit), ' ') ?: $limit), ' ,.;:').'…';
    }

    /** @return array{0: string, 1: string} the link and the punctuation left after it */
    private static function trimUrl(string $url): array
    {
        $tail = '';
        while ($url !== '') {
            $last = substr($url, -1);
            $unbalanced = ($last === ')' && substr_count($url, '(') < substr_count($url, ')'))
                || ($last === ']' && substr_count($url, '[') < substr_count($url, ']'));
            if (! str_contains('.,!?;:\'"', $last) && ! $unbalanced) {
                break;
            }
            $tail = $last.$tail;
            $url = substr($url, 0, -1);
        }

        return [$url, $tail];
    }
}
