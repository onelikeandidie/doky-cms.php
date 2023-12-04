<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Custom renderer for CommonMark
 *
 * - Converts headings into paragraphs with tailwind (with tw prefix) class
 */
class HeadingRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof Heading)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }
        $heading_level = $node->getLevel();
        $classes = ["tw-font-semibold", "tw-mb-4", 'tw-mt-2'];
        $class = 'tw-text-';
        $tag = 'p';
        switch ($heading_level) {
            case 1:
                $class .= '4xl';
                $tag = 'h2';
                break;
            case 2:
                $class .= '2xl';
                $tag = 'h2';
                break;
            case 3:
                $class .= 'xl';
                $tag = 'h3';
                break;
            case 4:
                $class .= 'lg';
                $tag = 'h4';
                break;
            default:
                $class .= 'md';
                $tag = 'h5';
                break;
        }
        $classes[] = $class;
        $slug = "";
        if ($node->hasChildren()) {
            // Get the first child, if the first child is a text node, get the content
            // If not continue until we find a text node
            /** @var Text $child */
            $child = $node->firstChild();
            $tries = 0;
            while (!($child instanceof Text)) {
                if ($child === null) {
                    break;
                }
                $child = $child->next();
                $tries++;
                if ($tries > 10) {
                    break;
                }
            }
            if ($child instanceof Text) {
                $txt = $child->getLiteral();
                $slug = Str::slug($txt);
                $a = new HtmlElement(
                    'a',
                    [
                        'href' => '#' . $slug,
                        'class' => ''
                    ],
                    $childRenderer->renderNodes($node->children())
                );
                return new HtmlElement(
                    $tag,
                    [
                        'class' => implode(' ', $classes),
                        'id' => $slug,
                    ],
                    $a
                );
            }
        }
        return new HtmlElement(
            $tag,
            [
                'class' => implode(' ', $classes),
                'id' => $slug,
            ],
            $childRenderer->renderNodes($node->children())
        );
    }
}
