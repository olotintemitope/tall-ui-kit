<?php

namespace TallUiKit\Support;

use Illuminate\Support\HtmlString;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\UrlAutolinkParser;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\Mention\Mention;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use League\CommonMark\Extension\TaskList\TaskListExtension;
use League\CommonMark\MarkdownConverter;

/**
 * User-written text (chat messages, posts, comments) as safe, rendered Markdown.
 *
 * Raw HTML is escaped, unsafe protocols are refused, and generated links carry a
 * nofollow relationship. CommonMark plus safe GitHub-flavored extensions support
 * emphasis, lists, quotes, links, tables, task lists, code and strikethrough.
 */
final class RichText
{
    /** @var array<string, MarkdownConverter> */
    private static array $converters = [];

    public static function render(?string $text, ?string $mentionUrl = null, ?string $hashtagUrl = null, bool $mentions = true, bool $hashtags = true): HtmlString
    {
        $text = (string) $text;
        if ($text === '') {
            return new HtmlString('');
        }

        $key = hash('sha256', serialize([$mentionUrl, $hashtagUrl, $mentions, $hashtags]));
        $converter = self::$converters[$key] ??= self::makeConverter($mentionUrl, $hashtagUrl, $mentions, $hashtags);

        $html = trim((string) $converter->convert($text));
        if (preg_match('/^<p>(.*)<\/p>$/sD', $html, $match)) {
            $html = $match[1];
        }

        return new HtmlString($html);
    }

    private static function makeConverter(?string $mentionUrl, ?string $hashtagUrl, bool $mentions, bool $hashtags): MarkdownConverter
    {
        $environment = new Environment([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
            'renderer' => ['soft_break' => "<br />\n"],
        ]);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addInlineParser(new UrlAutolinkParser(['http', 'https'], 'https'));
        $environment->addExtension(new StrikethroughExtension());
        $environment->addExtension(new TableExtension());
        $environment->addExtension(new TaskListExtension());
        $environment->addRenderer(Link::class, new RichTextLinkRenderer(), 10);

        $rules = [];
        if ($mentions) {
            $rules['user'] = [
                'prefix' => '@',
                'pattern' => '[A-Za-z0-9_][A-Za-z0-9_.-]{0,38}[A-Za-z0-9_]|[A-Za-z0-9_]',
                'generator' => static function (Mention $mention) use ($mentionUrl): Mention {
                    $name = $mention->getIdentifier();
                    $mention->setLabel('@'.$name);
                    $mention->data->set('attributes', ['class' => 'ui-rich-mention']);
                    $mention->setUrl($mentionUrl !== null ? str_replace(':name', rawurlencode($name), $mentionUrl) : '#');

                    return $mention;
                },
            ];
        }
        if ($hashtags) {
            $rules['tag'] = [
                'prefix' => '#',
                'pattern' => '[\p{L}\p{N}_]{1,64}',
                'generator' => static function (Mention $mention) use ($hashtagUrl): Mention {
                    $tag = $mention->getIdentifier();
                    $mention->setLabel('#'.$tag);
                    $mention->data->set('attributes', ['class' => 'ui-rich-tag']);
                    $mention->setUrl($hashtagUrl !== null ? str_replace(':tag', rawurlencode($tag), $hashtagUrl) : '#');

                    return $mention;
                },
            ];
        }
        if ($rules !== []) {
            $environment->addExtension(new MentionExtension());
            $environment->mergeConfig(['mentions' => $rules]);
        }

        return new MarkdownConverter($environment);
    }

    /** Plain text, shortened at a word to about `$limit` characters (for previews and announcements). */
    public static function plain(?string $text, int $limit = 140): string
    {
        $flat = trim((string) preg_replace('/\s+/u', ' ', (string) $text));

        return mb_strlen($flat) <= $limit ? $flat : rtrim(mb_substr($flat, 0, mb_strrpos(mb_substr($flat, 0, $limit), ' ') ?: $limit), ' ,.;:').'…';
    }
}
