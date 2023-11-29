<?php

namespace App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers;

use App\Libraries\Youtube\Youtube;
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
        $container_attrs = $node->data->get('container_attributes', []);
        $container_attrs['class'] = 'tw-aspect-w-16 tw-aspect-h-9';
        $caption_container_attr = $node->data->get('caption_container_attributes', []);
        $caption_container_attr['class'] = 'tw-text-center tw-m-2';
        return new HtmlElement('div', $bigger_container_attrs, [
            new HtmlElement('div', $container_attrs,
                $result
            ),
            new HtmlElement('div', $caption_container_attr,
                $childRenderer->renderNodes($node->children())
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
        if (str_starts_with($url, '/resources/')) {
            $url = str_replace('/resources/', 'public/', $url);
        }
        if (str_starts_with($url, 'public/image')) {
            $url = image_url($url);
        }
        $attrs = $node->data->get('attributes', []);
        $attrs['class'] = 'tw-object-contain tw-object-center tw-w-full tw-h-full tw-rounded-lg';
        $attrs['target'] = '_blank';
        $attrs['rel'] = 'noopener noreferrer';
        $attrs['data-src'] = $url;
        return new HtmlElement('img', $attrs);
    }
}
