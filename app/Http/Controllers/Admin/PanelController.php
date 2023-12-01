<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Libraries\Result\Result;
use App\Libraries\Sync\Sync;
use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PanelController extends Controller
{
    public function index(Request $request, Sync $sync)
    {
        $images = [];
        if (auth()->check() && auth()->user()->hasPermission('sync.upload')) {
            $files = File::files($sync->getDriver()->getDirectory() . '/images');
            foreach ($files as $file) {
                $images[] = asset('/public/storage/sync/' . $sync->getDriver()->getRelativePath() . '/images/' . $file->getFilename());
            }
        }
        return view('dashboard', [
            'images' => $images
        ]);
    }

    public function syncDownload(Sync $sync)
    {
        $driver = $sync->getDriver();
        $result = $driver->download();
        if ($result->isErr()) {
            dump($result);
            return false;
        }
        $filesChanged = $result->unwrap();
        foreach ($filesChanged as $fileChanged) {
            $filePath = $sync->getSyncPath() . '/' . $fileChanged;
            // Check if the file is a markdown file
            if (!Str::endsWith($filePath, '.md')) {
                Log::debug('Skipping file ' . $filePath . ' because it is not a markdown file.');
                continue;
            }
            $fileContents = File::get($filePath);
            $article = Article::fromString($fileContents);
            if ($article->isErr()) {
                dump($article);
                return false;
            }
            $article = $article->unwrap();
            // Find the article in the database with the same slug
            $existingArticle = Article::where('slug', $article->slug)->first();
            if ($existingArticle === null) {
                // If the article doesn't exist, create it
                $article->save();
            } else {
                // If the article exists, update it
                $existingArticle->content = $article->content;
                $existingArticle->meta($article->meta());
                $existingArticle->save();
            }
        }
        return redirect()->route('dashboard');
    }

    public function syncUpload(Sync $sync)
    {
        // Before uploading, I have to serialize the articles
        $rootArticles = Article::tree();
        $driver = $sync->getDriver();
        foreach ($rootArticles as $rootArticle) {
            $this->serialize($rootArticle, $driver->getDirectory());
        }
        $result = $driver->upload();
        if ($result->isErr()) {
            dump($result);
            return false;
        }
        return redirect()->route('dashboard');
    }

    public function serialize(Article $article, $dir)
    {
        $slug = $article->slug;
        $filename = $dir . '/' . $slug . '.md';
        $markdown = $article->toString();
        $result = File::put($filename, $markdown);
        if ($result === false) {
            return Result::err('Failed to serialize article ' . $article->id . ' (' . $article->slug . ')');
        }
        if ($article->children->count() > 0) {
            // Ensure the directory exists
            if (!File::exists($dir . '/' . $slug)) {
                $result = File::makeDirectory($dir . '/' . $slug, 0755, true);
            }
            if ($result === false) {
                return Result::err('Failed to create directory for article ' . $article->id . ' (' . $article->slug . ')');
            }
            foreach ($article->children as $child) {
                $result = $this->serialize($child, $dir . '/' . $slug);
                if ($result->isErr()) {
                    return $result;
                }
            }
        }
        return Result::ok();
    }
}
