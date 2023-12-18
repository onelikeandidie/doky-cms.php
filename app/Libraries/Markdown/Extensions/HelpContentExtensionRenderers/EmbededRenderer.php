<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use App\Libraries\Sync\Sync;
use App\Libraries\Youtube\Youtube;
use Illuminate\Support\Str;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Node\Inline\Text;
use League\CommonMark\Node\Node;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use League\CommonMark\Renderer\NodeRendererInterface;
use League\CommonMark\Util\HtmlElement;

/**
 * Adds underline to <a> tags
 */
class EmbededRenderer implements NodeRendererInterface
{
    public function render(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        if (!($node instanceof Image)) {
            throw new \InvalidArgumentException('Incompatible node type: ' . \get_class($node));
        }
        $url = $node->getUrl();
        $inner = null;
        // If it is a youtube video, then embed it
        if (str_starts_with($url, 'https://www.youtube.com/watch?v=')) {
            $result = $this->renderYoutubeEmbedded($node, $childRenderer);
        } else {
            $result = $this->renderImage($node, $childRenderer);
        }
        $bigger_container_attrs = $node->data->get('bigger_container_attributes', []);
        // The embedded shouldn't be the width of the page
        $bigger_container_attrs['class'] = 'tw-w-full md:tw-w-4/5 xl:tw-w-3/5 tw-mx-auto tw-mb-4';
        $container_attrs = $node->data->get('container_attributes', []);
        $container_attrs['class'] = 'tw-aspect-w-16 tw-aspect-h-9';
        return new HtmlElement('div', $bigger_container_attrs, [
            new HtmlElement('div', $container_attrs,
                $result
            )
        ]);
    }

    public function renderYoutubeEmbedded(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        // <iframe width="560" height="315" src="https://www.youtube.com/embed/ZmYAoQL9jjo?si=Z5xAaWzOoCBOdnHD" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
        $attrs = $node->data->get('attributes', []);
        $url = $node->getUrl();
        $src = Youtube::fromConfig()->getEmbed($url);
        $attrs['src'] = $src;
        return new HtmlElement('iframe', $attrs);
    }

    public function renderImage(Node $node, ChildNodeRendererInterface $childRenderer)
    {
        $url = $node->getUrl();
        if (Str::contains($url, '://')) {
            // This is an absolute URL
            $attrs = $node->data->get('attributes', []);
            $attrs['src'] = $url;
            return new HtmlElement('img', $attrs);
        }
        if (str_starts_with($url, 'public/')) {
            // This is a relative URL
            $url = asset($url);
        }
        if (Str::startsWith($url, '/' . Sync::getInstance()->getDriver()->getRelativePath())) {
            // This is a relative URL to the sync directory
            $url = asset('/public/storage/sync' . $url);
        }
        $attrs = $node->data->get('attributes', []);
        $attrs['class'] = 'tw-object-contain tw-object-center tw-w-full tw-h-full tw-rounded-lg';
        $attrs['target'] = '_blank';
        $attrs['rel'] = 'noopener noreferrer';
        $attrs['data-src'] = $url;
        // Get the alt text from a child text node
        $alt = null;
        foreach ($node->children() as $child) {
            if ($child instanceof Text) {
                $alt = $child->getLiteral();
                break;
            }
        }
        if ($alt !== null) {
            $attrs['alt'] = $alt;
        }
        return new HtmlElement('img', $attrs);
    }
}
