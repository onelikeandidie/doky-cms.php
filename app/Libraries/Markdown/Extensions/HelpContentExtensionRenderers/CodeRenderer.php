<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Node\Block\Paragraph;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;
use League\CommonMark\Util\Xml;

class CodeRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!$node instanceof Code) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }

        $classes = [
            'code-block',
            'tw-inline',
            'tw-bg-neutral-200',
            'dark:tw-bg-neutral-800',
            'tw-text-neutral-800',
            'dark:tw-text-neutral-200',
            'tw-px-2',
            'tw-py-1',
            'tw-rounded-md',
            'tw-text-sm',
            'tw-font-mono'
        ];

        return new HtmlElement('span',
            ['class' => $classes],
            new HtmlElement('code',
                ['class' => 'code-content'],
                Xml::escape($node->getLiteral())
            )
        );
    }
}
