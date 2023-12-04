<?php

namespace App\Libraries\Sync;

use App\Jobs\SyncInit;
use App\Libraries\Result\Result;
use App\Libraries\Sync\Drivers\Git;
use App\Libraries\Sync\Exceptions\MissingDriverException;
use App\Models\Article;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Sync {
    public const DRIVERS = [
        'git' => Git::class
    ];

    protected ISyncDriver $driver;

    protected static ?self $instance = null;

    /**
     * @throws MissingDriverException
     */
    public static function getInstance($initialize_driver = true): self
    {
        if (self::$instance !== null) {
            return self::$instance;
        }
        $config_sync_path = config('sync.path');
        if (empty($config_sync_path)) {
            throw new \InvalidArgumentException('Missing config value: SYNC_PATH');
        }
        $config_driver = config('sync.driver');
        $driver = self::DRIVERS[$config_driver] ?? null;
        if ($driver === null) {
            throw new MissingDriverException("Driver $config_driver not found");
        }
        $self = new self();
        $self->driver = new $driver($config_sync_path);
        if ($initialize_driver && !$self->driver->isInitialized()) {
            Log::debug('Sync driver not initialized, dispatching SyncInit job');
            SyncInit::dispatch($self);
        }
        self::$instance = $self;
        return $self;
    }

    public function getDriver(): ISyncDriver
    {
        return $this->driver;
    }

    public function getSyncPath(): string
    {
        return config('sync.path');
    }

    public function downloadAndSync(): Result {
        $driver = $this->getDriver();
        $result = $driver->download();
        if ($result->isErr()) {
            return $result;
        }
        $filesChanged = $result->unwrap();
        foreach ($filesChanged as $fileChanged) {
            $filePath = $this->getSyncPath() . '/' . $fileChanged;
            // Check if the file is a markdown file
            if (!Str::endsWith($filePath, '.md')) {
                Log::debug('Skipping file ' . $filePath . ' because it is not a markdown file.');
                continue;
            }
            // Check if the change is a deletion
            if (!File::exists($filePath)) {
                // If the file doesn't exist, delete the article
                $article = Article::where('slug', basename($filePath, '.md'))->first();
                $article?->delete();
                continue;
            }
            $fileContents = File::get($filePath);
            $article = Article::fromString($fileContents);
            if ($article->isErr()) {
                return $article;
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
        return Result::ok();
    }
}
