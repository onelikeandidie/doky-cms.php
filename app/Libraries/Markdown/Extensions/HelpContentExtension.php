<?php

namespace App\Libraries\Markdown\Extensions;

use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\BlockquoteRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\CodeRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\EmbededRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\HeadingRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\LinkRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\ListBlockRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\ListItemRenderer;
use App\Libraries\Markdown\Extensions\HelpContentExtensionRenderers\ParagraphRenderer;
use League\CommonMark\Environment\EnvironmentBuilderInterface;
use League\CommonMark\Extension\CommonMark\Node\Block\BlockQuote;
use League\CommonMark\Extension\CommonMark\Node\Block\Heading;
use League\CommonMark\Extension\CommonMark\Node\Block\ListBlock;
use League\CommonMark\Extension\CommonMark\Node\Block\ListItem;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use League\CommonMark\Extension\CommonMark\Node\Inline\Image;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Node\Block\Paragraph;

class HelpContentExtension implements ExtensionInterface
{
    public function register(EnvironmentBuilderInterface $environment): void
    {
        // Renderer to make headings not h* tags
        $environment->addRenderer(Heading::class, new HeadingRenderer());
        // Renderer to style blockquotes
        $environment->addRenderer(BlockQuote::class, new BlockquoteRenderer());
        // Renderer to style lists
        $environment->addRenderer(ListBlock::class, new ListBlockRenderer());
        $environment->addRenderer(ListItem::class, new ListItemRenderer());
        // Renderer to style links
        $environment->addRenderer(Link::class, new LinkRenderer());
        // Renderer to style paragraphs
        $environment->addRenderer(Paragraph::class, new ParagraphRenderer());
        // Renderer for ![]() stuff
        $environment->addRenderer(Image::class, new EmbededRenderer());
        // Renderer for code blocks
        $environment->addRenderer(Code::class, new CodeRenderer());
    }
}
