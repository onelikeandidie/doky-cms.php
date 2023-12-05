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
        $this->authorize('create', $article);
        $parentSlug = null;
        if ($article->exists) {
            $breadcrumb = $article->breadcrumb();
            $parentSlug = $breadcrumb->last()->slug;
        }
        return view('articles.create', [
            'article' => $article,
            'parentSlug' => $parentSlug,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticleCreateFormSubmitRequest $request, Article $article)
    {
        $this->authorize('create', $article);
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
            'content' => '# ' . $request->input('title') . "\n\n",
            'metadata' => (new Meta([
                'title' => $request->input('title'),
                'authors' => [
                    $request->user()->name,
                ],
                'visibility' => $request->input('visibility', 'private'),
            ]))->toArray(),
            'parent_id' => $parent_id,
        ]);
        return redirect()->route('articles.edit', $post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Article $article)
    {
        // If the user is not logged in, they can only view public articles
        if (!auth()->check()) {
            $visibility = $article->meta()->get('visibility')->unwrapOrDefault('private');
            if ($visibility !== 'public') {
                abort(403, 'You are not allowed to view this article.');
            }
        } else {
            $this->authorize('view', $article);
        }
        // Cache it boys
        if (cache()->has('article.' . $article->id)) {
            $content = cache()->get('article.' . $article->id);
        } else {
            $markdown = new Markdown($article->content);
            $content = $markdown->toHtml();
            cache()->put('article.' . $article->id, $content, 120);
        }
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
        $this->authorize('update', $article);
        // Get the parent slug
        $parentSlug = null;
        if ($article->parent) {
            $breadcrumb = $article->breadcrumb();
            $breadcrumb->pop();
            $parentSlug = $breadcrumb->last()->slug;
        }
        return view('articles.edit', [
            'article' => $article,
            'parentSlug' => $parentSlug,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticleUpdateFormSubmitRequest $request, Article $article)
    {
        $validated = $request->validated();
        $slug = $validated['slug'];
        if ($slug !== $article->slug) {
            if (Article::where('slug', $slug)->exists()) {
                return redirect()->back()->withErrors([
                    'slug' => 'The slug has already been taken.',
                ]);
            }
        }
        $article->slug = $validated['slug'];
        $article->content = $validated['content'];
        $meta = $article->meta();
        $meta->set('title', $validated['title']);
        $authors = $meta->get('authors')->unwrapOrDefault([]);
        // Add the author if they don't exist
        if (!in_array($request->user()->name, $authors)) {
            $authors[] = $request->user()->name;
        }
        $meta->set('authors', $authors);
        $meta->set('visibility', $validated['visibility']);
        $meta->set('priority', $validated['priority']);
        $tags = $validated['tags'];
        if ($tags !== null) {
            $tags = explode(',', $tags);
            $tags = array_map(fn(string $tag) => trim($tag), $tags);
            $tags = array_filter($tags, fn(string $tag) => $tag !== '');
            $meta->set('tags', $tags);
        }
        $allowed_users = $validated['allowed_users'];
        if ($allowed_users !== null) {
            $allowed_users = explode(',', $allowed_users);
            $allowed_users = array_map(fn(string $user) => trim($user), $allowed_users);
            $allowed_users = array_filter($allowed_users, fn(string $user) => $user !== '');
            $meta->set('allowed_users', $allowed_users);
        }
        $allowed_roles = $validated['allowed_roles'];
        if ($allowed_roles !== null) {
            $allowed_roles = explode(',', $allowed_roles);
            $allowed_roles = array_map(fn(string $role) => trim($role), $allowed_roles);
            $allowed_roles = array_filter($allowed_roles, fn(string $role) => $role !== '');
            $meta->set('allowed_roles', $allowed_roles);
        }
        // Save the article
        $article->meta($meta);
        $article->save();
        // Clear the cache
        cache()->forget('article.' . $article->id); // Clears the html
        cache()->forget('articles'); // Clears the navigation tree
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
