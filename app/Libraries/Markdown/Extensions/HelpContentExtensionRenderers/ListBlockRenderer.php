<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Custom renderer for CommonMark
 *
 * - Converts Lists into ul with tailwind (with tw prefix) class
 */
class ListBlockRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof ListBlock)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }
        // Check if the list is ordered
        $classes = ['tw-break-words', 'tw-pl-4', 'tw-m-0', 'tw-list-outside'];
        if ($node->getListData()->type === ListBlock::TYPE_ORDERED) {
            $classes[] = 'tw-list-decimal';
            return new HtmlElement(
                'ol',
                ['class' => implode(' ', $classes)],
                $childRenderer->renderNodes($node->children())
            );
        } else {
            $classes[] = 'tw-list-disc';
            return new HtmlElement(
                'ul',
                ['class' => implode(' ', $classes)],
                $childRenderer->renderNodes($node->children())
            );
        }
    }
}
