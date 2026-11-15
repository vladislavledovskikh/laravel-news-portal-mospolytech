<?php

namespace App\Support;

use App\Events\NewArticleEvent;
use App\Jobs\VeryLongJob;
use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NewArticleNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Что происходит после публикации новости (лабораторные работы №10–12, №15).
 *
 * Новость можно создать двумя путями: через сайт (App\Http\Controllers\
 * ArticleController::store) и через REST API (App\Http\Controllers\API\
 * ArticleController::store). Побочные эффекты у обоих одинаковые, поэтому
 * они собраны здесь:
 *
 *  - письмо модераторам ставится в очередь (№10);
 *  - событие уходит в широковещание для тех, кто сейчас на сайте (№11);
 *  - читателям создаётся уведомление в БД (№12).
 */
class ArticleAnnouncement
{
    public static function publish(Article $article, ?User $author = null): void
    {
        // Письмо модераторам — не в запросе, а заданием в очереди.
        VeryLongJob::dispatch($article, $author?->name);

        // Онлайн-оповещение открытых вкладок по WebSocket.
        NewArticleEvent::dispatch(
            $article->id,
            $article->title,
            route('news.show', $article)
        );

        self::notifyReaders($article, $author);
    }

    /**
     * Уведомления получают читатели — все, кроме того, кто опубликовал новость.
     */
    private static function notifyReaders(Article $article, ?User $author): void
    {
        $readers = User::query()
            ->whereHas('role', fn ($query) => $query->where('code', Role::READER))
            ->when($author, fn ($query) => $query->whereKeyNot($author->id))
            ->get();

        if ($readers->isEmpty()) {
            return;
        }

        Notification::send($readers, new NewArticleNotification($article));
    }
}
