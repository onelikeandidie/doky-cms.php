<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
