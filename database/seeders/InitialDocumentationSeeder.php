<?php

namespace Database\Seeders;

use App\Libraries\Markdown\Meta;
use App\Models\Article;
use Illuminate\Database\Seeder;

class InitialDocumentationSeeder extends Seeder
{
    public function run(): void
    {
        $getting_started_content = <<<EOT
# Getting Started

The panel on your left should show the tree of documentation you have to work
with. If you're on a small screen, you can click the hamburger menu in the top
left to show the tree.

To navigate the articles, just click on them. You can also use the search bar
at the top of the tree to search for articles or use the search hotkey
(<kbd>/</kbd>).

Click on the first article in the tree to get started.
EOT;

        $first_article_content = <<<EOT
# First Article

This is the first article in the tree. You can edit it by clicking the edit
button in the top right of the article.

To create a new article, click the "+ New Article" button under each tree
section. This will create an article under that section. Articles have
parents, so you can nest them as deep as you want (although you probably
shouldn't make endless nested articles).

# Markdown

This documentation is written in Markdown. Markdown is a simple markup
language that allows you to write rich text using plain text. You can
learn more about Markdown [here](https://www.markdownguide.org/).

# Slugs

Each article has a slug. The slug is the URL-friendly version of the title.
Slugs must be unique between articles.
EOT;

        $getting_started = Article::create([
            'slug' => 'getting-started',
            'content' => $getting_started_content,
            'metadata' => (new Meta([
                'title' => 'Getting Started',
                'authors' => [
                    'Doky',
                ],
            ]))->toArray(),
            'author_id' => 1,
        ]);

        $first_article = Article::create([
            'slug' => 'first-article',
            'content' => $first_article_content,
            'metadata' => (new Meta([
                'title' => 'First Article',
                'authors' => [
                    'Doky',
                ],
            ]))->toArray(),
            'author_id' => 1,
            'parent_id' => $getting_started->id,
        ]);
    }
}
