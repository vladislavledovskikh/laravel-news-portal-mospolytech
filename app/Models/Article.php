<?php

namespace App\Models;

use Database\Factories\ArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    /** @use HasFactory<ArticleFactory> */
    use HasFactory;

    /**
     * Поля, которые можно заполнять массово
     * (Article::create([...]) и Article::factory()).
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'short_description',
        'content',
        'published_at',
        'preview_image',
        'full_image',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'date',
        ];
    }

    /**
     * Комментарии к новости.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
