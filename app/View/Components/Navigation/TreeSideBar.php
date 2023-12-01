<?php

namespace App\View\Components\Navigation;

use App\Models\Article;
use Illuminate\View\Component;

class TreeSideBar extends Component
{
    public function render()
    {
        // Get the articles from the cache if the user is not logged in
        $articles = null;
        if (!auth()->check()) {
            $articles = cache()->get('articles');
        }
        if ($articles === null) {
            // Get the articles
            $articles = Article::tree();
            // If the user is not logged in
            // Cache the articles
            if (!auth()->check()) {
                cache()->put('articles', $articles, 120);
            }
        }
        return view('components.navigation.tree-side-bar', [
            'articles' => $articles,
        ]);
    }
}
