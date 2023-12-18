<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

class ParagraphRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!$node instanceof Paragraph) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        $tag = 'p';
        $classes = ['tw-break-words', 'tw-my-4'];

        // If the paragraph is inside a list item, make it inline
        $parent = $node->parent();
        if ($parent instanceof ListItem) {
            $tag = 'span';
            $classes[] = 'tw-inline';
        }

        return new HtmlElement(
            $tag,
            ['class' => implode(' ', $classes)],
            $childRenderer->renderNodes($node->children())
        );
    }
}
