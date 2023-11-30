<?php

namespace Tests\Unit;

use App\Models\Article;
use Tests\TestCase;

class ArticleSerializationTest extends TestCase
{
    public function test_serialize()
    {
        $article = new Article([
            'slug' => 'test',
            'content' => "# Test\n\nThis is a test",
            'metadata' => [
                'title' => 'Test',
                'visibility' => 'public',
                'priority' => 1,
                'tags' => ['test'],
                'allowed_users' => [],
                'allowed_roles' => [],
                'date' => '2021-01-01',
                'authors' => ['test']
            ],
            'parent_id' => null,
        ]);
        $markdown = $article->toString();
        // The order of the metadata is alphabetical
        $expected = <<<EOT
---
allowed_roles: []
allowed_users: []
authors: [test]
date: 2021-01-01
priority: 1
tags: [test]
title: Test
visibility: public

slug: test
---
# Test

This is a test
EOT;
        $this->assertEquals($expected, $markdown);
    }

    public function test_deserialize()
    {
        $markdown = <<<EOT
---
allowed_roles: []
allowed_users: []
authors: [test]
date: 2021-01-01
priority: 1
tags: [test]
title: Test
visibility: public

slug: test
---
# Test

This is a test
EOT;
        $article = Article::fromString($markdown);
        $article = $article->unwrap();

        $this->assertEquals('test', $article->slug);
        $this->assertEquals("# Test\n\nThis is a test", $article->content);
        $this->assertEquals('Test', $article->meta()->get('title')->unwrap());
        $this->assertEquals('public', $article->meta()->get('visibility')->unwrap());
        $this->assertEquals(1, $article->meta()->get('priority')->unwrap());
        $this->assertEquals(['test'], $article->meta()->get('tags')->unwrap());
        $this->assertEquals([], $article->meta()->get('allowed_users')->unwrap());
        $this->assertEquals([], $article->meta()->get('allowed_roles')->unwrap());
        $this->assertEquals('2021-01-01', $article->meta()->get('date')->unwrap());
        $this->assertEquals(['test'], $article->meta()->get('authors')->unwrap());
    }
}
