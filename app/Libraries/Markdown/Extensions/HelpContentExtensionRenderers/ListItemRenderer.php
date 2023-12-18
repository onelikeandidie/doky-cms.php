<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class ListItemRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        $classes = ['tw-break-words'];
        return new HtmlElement(
            'li',
            ['class' => implode(' ', $classes)],
            $childRenderer->renderNodes($node->children())
        );
    }
}
