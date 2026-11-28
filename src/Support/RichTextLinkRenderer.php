<?php

namespace TallUiKit\Support;

use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\RegexHelper;

final class RichTextLinkRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer): ?HtmlElement
    {
        Link::assertInstanceOf($node);
        $url = $node->getUrl();
        $attributes = $node->data->get('attributes', []);
        $attributes['class'] ??= 'ui-rich-link';
        $contents = $childRenderer->renderNodes($node->children());

        if ($url === '#' || $url === '' || RegexHelper::isLinkPotentiallyUnsafe($url)) {
            return new HtmlElement('span', $attributes, $contents);
        }

        $attributes['href'] = $url;
        $attributes['rel'] ??= 'nofollow ugc noopener';
        if (($title = $node->getTitle()) !== null) {
            $attributes['title'] = $title;
        }

        return new HtmlElement('a', $attributes, $contents);
    }
}
