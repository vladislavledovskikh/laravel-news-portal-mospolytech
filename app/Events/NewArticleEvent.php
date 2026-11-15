<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Онлайн-оповещение о новой новости (лабораторная работа №11).
 *
 * Событие реализует ShouldBroadcast — значит Laravel ставит в очередь задание
 * BroadcastEvent, которое отправляет данные в Pusher (или в совместимый с ним
 * сервер вещания). Браузер получает их по WebSocket через Laravel Echo
 * и показывает всплывающее окно (Vue-компонент App.vue).
 *
 * Канал публичный (Channel, не PrivateChannel): оповещение видят все, кто
 * сейчас на сайте, авторизация не нужна.
 */
class NewArticleEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /** Имя канала вещания: на него подписан Vue-компонент в браузере. */
    public const CHANNEL = 'articles';

    /**
     * @param  int  $id  идентификатор новости — для ссылки в уведомлении
     * @param  string  $title  заголовок новости
     * @param  string  $url  ссылка на страницу новости
     */
    public function __construct(
        public int $id,
        public string $title,
        public string $url,
    ) {}

    /**
     * Канал, на котором транслируется событие.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel(self::CHANNEL),
        ];
    }

    /**
     * Имя события для Echo: в компоненте — listen('NewArticleEvent', ...).
     *
     * Без этого метода Laravel отправил бы полное имя класса
     * (App\Events\NewArticleEvent), и слушатель в браузере его бы не поймал.
     */
    public function broadcastAs(): string
    {
        return 'NewArticleEvent';
    }

    /**
     * Данные, которые уходят в браузер.
     *
     * Форма специально такая: в компоненте данные читаются как
     * `article.article.name` — так в задании.
     *
     * @return array<string, array<string, int|string>>
     */
    public function broadcastWith(): array
    {
        return [
            'article' => [
                'id' => $this->id,
                'name' => $this->title,
                'url' => $this->url,
            ],
        ];
    }
}
