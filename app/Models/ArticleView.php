<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Просмотр новости (лабораторная работа №14).
 *
 * Запись создаёт посредник TrackArticleView при каждом открытии
 * страницы /news/{article}, а читает — команда stats:daily.
 */
class ArticleView extends Model
{
    /** Просмотру нечего обновлять: в таблице есть только created_at. */
    public const UPDATED_AT = null;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'article_id',
        'url',
        'ip',
        'user_agent',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Новость, которую открыли.
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Только просмотры за сегодняшний день.
     *
     * Колонку указываем вместе с таблицей (article_views.created_at),
     * потому что статистика соединяет её с таблицей articles — иначе
     * SQLite не поймёт, из какой таблицы брать created_at.
     */
    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate($query->qualifyColumn('created_at'), today());
    }
}
