<?php

namespace App\Models;

use App\Libraries\Markdown\Markdown;
use App\Libraries\Markdown\Meta;
use App\Libraries\Result\Result;
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

    public function toString(): string
    {
        $markdown = $this->content;
        $meta = $this->meta();
        $string = "---\n" . $meta->toString() . "\n";
        $parent = $this->parent()->first();
        if ($parent !== null) {
            $string .= "parent: " . $parent->slug . "\n";
        }
        $string .= "\n";
        $string .= "slug: " . $this->slug . "\n";
        $string .= "---\n" . $markdown;
        return $string;
    }

    public function breadcrumb(): \Illuminate\Support\Collection
    {
        $breadcrumb = collect([$this]);
        $parent = $this->parent()->first();
        if ($parent !== null) {
            $breadcrumb = $parent->breadcrumb()->merge($breadcrumb);
        }
        return $breadcrumb;
    }

    // Static functions

    public static function fromString($content): Result
    {
        $markdown = new Markdown($content);
        $meta = $markdown->meta();
        if ($meta->isErr()) {
            return $meta;
        }
        $meta = $meta->unwrap();
        if (!$meta->get('slug')->isOk()) {
            return $meta->get('slug');
        }
        $article = new self([
            'slug' => $meta->get('slug')->unwrap(),
            'content' => $markdown->extractContent(),
            'metadata' => $meta->toArray(),
        ]);
        return Result::ok($article);
    }

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

    public static function tree(): Collection
    {
        $articles = Article::query()
            ->tree()
            ->with('children')
            ->get();
        $articles = $articles->filter(function (Article $article) {
            $visibility = $article->meta()->get('visibility')->unwrapOrDefault('private');
            // If the article is private, check if the user can view it
            if ($visibility === 'private' || $visibility === 'restricted') {
                return auth()->check() && auth()->user()->can('view', $article);
            }
            return $visibility === 'public';
        });
        return Article::sortTree($articles);
    }
}
