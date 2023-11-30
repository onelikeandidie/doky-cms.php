<?php

namespace App\View\Components\Navigation;

use App\Models\Article;
use Illuminate\View\Component;

class TreeSideBar extends Component
{
    public function render()
    {
        // Get the articles from the cache
        $articles = cache()->get('articles');
        if ($articles === null) {
            $articles = Article::tree();
            // Cache the articles
            cache()->put('articles', $articles, 120);
        }
        return view('components.navigation.tree-side-bar', [
            'articles' => $articles,
        ]);
    }
}
