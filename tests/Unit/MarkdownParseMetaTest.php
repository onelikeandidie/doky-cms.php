<?php

namespace Tests\Unit;

use App\Libraries\Markdown\Exceptions\EmptyMetaException;
use App\Libraries\Markdown\Exceptions\MetaKeyNotSet;
use App\Libraries\Markdown\Exceptions\NoMetaClosingTagException;
use App\Libraries\Markdown\Exceptions\NoMetaStartTagException;
use App\Libraries\Markdown\Markdown;
use PHPUnit\Framework\TestCase;

class MarkdownParseMetaTest extends TestCase
{
    public function test_extract_meta_data_from_markdown_empty_meta()
    {
        $content = <<<'EOT'
---
---
# Hello World
EOT;
        $meta = Markdown::extractMeta($content);

        $this->assertTrue($meta->isErr());
        $this->assertEquals(EmptyMetaException::class, get_class($meta->getErr()));
    }

    public function test_extract_meta_data_from_markdown_no_meta_start_tag()
    {
        $content = <<<'EOT'
# Hello World
EOT;
        $meta = Markdown::extractMeta($content);

        $this->assertTrue($meta->isErr());
        $this->assertEquals(NoMetaStartTagException::class, get_class($meta->getErr()));
    }

    public function test_extract_meta_data_from_markdown_no_meta_end_tag()
    {
        $content = <<<'EOT'
---
# Hello World
EOT;
        $meta = Markdown::extractMeta($content);

        $this->assertTrue($meta->isErr());
        $this->assertEquals(NoMetaClosingTagException::class, get_class($meta->getErr()));
    }

    public function test_extract_meta_data_from_markdown()
    {
        $content = <<<'EOT'
---
title: Hello World
authors: John Doe, Jane Doe
date: 2021-01-01
tags: hello, world
---
# Hello World
EOT;
        $meta = Markdown::extractMeta($content);

        $this->assertTrue($meta->isOk());

        $meta = $meta->getOk();
        $this->assertEquals('Hello World', $meta->get('title')->unwrap());
        $this->assertEquals(['John Doe', 'Jane Doe'], $meta->get('authors')->unwrap());
        $this->assertEquals('2021-01-01', $meta->get('date')->unwrap());
        $this->assertEquals(['hello', 'world'], $meta->get('tags')->unwrap());
    }

    public function test_extract_meta_data_from_markdown_with_mixed_data()
    {
        $content = <<<'EOT'
---
title: Hello World
authors: John Doe, Jane Doe
date: 2021-01-01
tags: hello, world
# This is a comment
description: This is a description
beans: 123
---
EOT;
        $meta = Markdown::extractMeta($content);

        $this->assertTrue($meta->isOk());

        $meta = $meta->getOk();
        $this->assertEquals('Hello World', $meta->get('title')->unwrap());
        $this->assertEquals(['John Doe', 'Jane Doe'], $meta->get('authors')->unwrap());
        $this->assertEquals('2021-01-01', $meta->get('date')->unwrap());
        $this->assertEquals('This is a description', $meta->get('description')->unwrap());
        $this->assertEquals(['hello', 'world'], $meta->get('tags')->unwrap());
        $this->assertEquals(123, $meta->get('beans')->unwrap());
        // Check if getting an undefined key returns an error.
        $this->assertEquals(MetaKeyNotSet::class, get_class($meta->get('undefined')->getErr()));
    }
}
