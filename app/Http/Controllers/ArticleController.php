<?php

namespace App\Http\Controllers;

use App\Http\Requests\ArticleCreateFormSubmitRequest;
use App\Http\Requests\ArticleUpdateFormSubmitRequest;
use App\Libraries\Markdown\Markdown;
use App\Libraries\Markdown\Meta;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return view('articles.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(?Article $article)
    {
        return view('articles.create', [
            'article' => $article,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleCreateFormSubmitRequest $request, Article $article)
    {
        $parent_id = null;
        if ($article->exists) {
            $parent_id = $article->id;
        }
        $slug = $request->input('slug');
        if (Article::where('slug', $slug)->exists()) {
            return redirect()->back()->withErrors([
                'slug' => 'The slug has already been taken.',
            ]);
        }
        // Fun piece of code!
        $post = Article::create([
            'slug' => $request->input('slug'),
            'content' => '',
            'metadata' => (new Meta([
                'title' => $request->input('title'),
                'authors' => [
                    $request->user()->name,
                ],
            ]))->toArray(),
            'author_id' => $request->user()->id,
            'parent_id' => $parent_id,
        ]);
        return redirect()->route('articles.edit', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        $markdown = new Markdown($article->content);
        $content = $markdown->toHtml();
        return view('articles.show', [
            'article' => $article,
            'content' => $content
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Article $article)
    {
        return view('articles.edit', [
            'article' => $article,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleUpdateFormSubmitRequest $request, Article $article)
    {
        $validated = $request->validated();
        $article->slug = $validated['slug'];
        $article->content = $validated['content'];
        $meta = $article->meta();
        $meta->set('title', $validated['title']);
        $authors = $meta->get('authors')->getOkOrDefault([]);
        // Add the author if they don't exist
        if (!in_array($request->user()->name, $authors)) {
            $authors[] = $request->user()->name;
        }
        $meta->set('authors', $authors);
        $meta->set('visibility', $validated['visibility']);
        $meta->set('priority', $validated['priority']);
        // Save the article
        $article->meta($meta);
        $article->save();
        return redirect()->route('articles.edit', $article);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Article $post)
    {
        //
    }
}
