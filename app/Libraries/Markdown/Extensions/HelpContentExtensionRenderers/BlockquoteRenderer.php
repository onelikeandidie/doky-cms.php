<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Custom renderer for CommonMark
 *
 * - Converts Quotes into blockquotes with tailwind (with tw prefix) class
 */
class BlockquoteRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof BlockQuote)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }
        $classes = ['tw-break-words', "tw-italic", "tw-border-l-4", "tw-border-neutral-500", "tw-pl-4", "tw-py-2", "tw-mb-4"];
        return new HtmlElement(
            'blockquote',
            ['class' => implode(' ', $classes)],
            $childRenderer->renderNodes($node->children())
        );
    }
}
