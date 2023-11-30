<?php

namespace App\Models;

use App\Libraries\Markdown\Meta;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    private Meta $meta;

    protected $fillable = [
        'slug',
        'content',
        'metadata',
        'author_id',
        'parent_id',
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    // Override the save so that we can update the metadata
    public function save(array $options = [])
    {
        $metadata = $this->meta()->toArray();
        $this->metadata = $metadata;
        return parent::save($options);
    }

    public function meta($newMeta = null): Meta
    {
        if ($newMeta !== null) {
            $this->meta = $newMeta;
        }
        if (!isset($this->meta)) {
            $metadata = $this->metadata;
            $this->meta = new Meta($metadata);
        }
        return $this->meta;
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ArticleSettings::class, 'article_id');
    }

    /**
     * @param string $key
     * @return ArticleSettings|null
     */
    public function setting(string $key): ?ArticleSettings
    {
        return $this->settings()->firstWhere('key', $key);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Article::class, 'parent_id');
    }

    public function scopeTree($query)
    {
        return $query->whereNull('parent_id')->with('children');
    }

    // Static functions

    /**
     * @param Collection<self> $articles
     */
    public static function sortTree(Collection &$articles)
    {
        // Sort by priority then title
        $articles = $articles->sort(function (Article $a, Article $b) {
            $aPriority = $a->meta()->get('priority')->unwrapOrDefault(0);
            $bPriority = $b->meta()->get('priority')->unwrapOrDefault(0);
            if ($aPriority === $bPriority) {
                return $a->meta()->get('title')->getOk() <=> $b->meta()->get('title')->getOk();
            }
            return $bPriority <=> $aPriority;
        });
        foreach ($articles as $article) {
            if ($article->children->count() > 0) {
                $article->children = self::sortTree($article->children);
            }
        }
        return $articles;
    }
}
