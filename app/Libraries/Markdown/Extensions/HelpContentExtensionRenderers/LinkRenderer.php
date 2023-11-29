<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Adds underline to <a> tags
 */
class LinkRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof Link)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }
        $url = $node->getUrl();
        if (Str::startsWith($url, '/articles/')) {
            $path = Str::replace('/articles/', '', $url);
            $url = route('articles.show', $path);
        }
        $attrs = $node->data->get('attributes', []);
        $attrs['class'] = 'tw-text-primary-500 tw-underline';
        $attrs['target'] = '_blank';
        $attrs['rel'] = 'noopener noreferrer';
        $attrs['href'] = $url;
        return new HtmlElement('a', $attrs, $childRenderer->renderNodes($node->children()));
    }
}
