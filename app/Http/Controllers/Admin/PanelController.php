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
        if (auth()->check() && auth()->user()->hasPermission('article.update')) {
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
        $result = $sync->downloadAndSync();
        if ($result->isErr()) {
            Log::error($result->getErr());
            dump($result);
            return false;
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
