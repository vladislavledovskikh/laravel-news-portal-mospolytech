<?php

namespace App\Notifications;

use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Уведомление читателям о новой новости (лабораторная работа №12).
 *
 * Доставляется только в базу: строка в таблице notifications со ссылкой
 * на новость. В шапке сайта читатель видит счётчик непрочитанных
 * и список заголовков.
 */
class NewArticleNotification extends Notification
{
    use Queueable;

    /**
     * @param  Article  $article  новая новость
     */
    public function __construct(
        public Article $article,
    ) {}

    /**
     * Каналы доставки: только база (database).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Данные, которые сохраняются в строке уведомления.
     *
     * Laravel ищет метод toDatabase() и, если он есть, использует его
     * вместо toArray().
     *
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'article_id' => $this->article->id,
            'title' => $this->article->title,
            'url' => route('news.show', $this->article),
            'published_at' => $this->article->published_at?->format('d.m.Y'),
        ];
    }
}
