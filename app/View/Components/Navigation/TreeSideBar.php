<?php

namespace App\View\Components\Navigation;

use App\Models\Article;
use Illuminate\View\Component;

class TreeSideBar extends Component
{
    public function render()
    {
        $articles = Article::query()
            ->tree()
            ->with('children')
            ->get();
        $articles = $articles->filter(function (Article $article) {
            $visibility = $article->meta()->get('visibility')->getOkOrDefault('private');
            // If the article is private, check if the user can view it
            if ($visibility === 'private') {
                return auth()->check() && auth()->user()->can('view', $article);
            }
            return $visibility === 'public';
        });
        $articles = Article::sortTree($articles);
        return view('components.navigation.tree-side-bar', [
            'articles' => $articles,
        ]);
    }
}
