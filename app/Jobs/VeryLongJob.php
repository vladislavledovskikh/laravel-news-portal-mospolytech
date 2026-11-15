<?php

namespace App\Jobs;

use App\Mail\NewArticlePublished;
use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Задание очереди: рассылка модераторам о новой новости (лабораторная работа №10).
 *
 * До этой лабораторной письмо отправлялось прямо в ArticleController::store(),
 * и запрос ждал ответа почтового сервера. Теперь отправка вынесена в задание:
 * контроллер лишь кладёт его в таблицу jobs (драйвер очереди — database),
 * а письмо отправляет отдельный процесс `php artisan queue:work`.
 *
 * Модель Article сериализуется вместе с заданием (трейт Queueable включает
 * SerializesModels): в очередь уходит идентификатор статьи, а при выполнении
 * задание само загружает её из базы.
 */
class VeryLongJob implements ShouldQueue
{
    use Queueable;

    /** Сколько раз пытаться отправить письмо, прежде чем задание попадёт в failed_jobs. */
    public int $tries = 3;

    /** Пауза в секундах перед повтором попытки. */
    public int $backoff = 5;

    /**
     * @param  Article  $article  новость, о которой нужно сообщить
     * @param  string|null  $author  имя автора новости для текста письма
     */
    public function __construct(
        public Article $article,
        public ?string $author = null,
    ) {}

    /**
     * Отправляет письмо всем пользователям с ролью «модератор».
     */
    public function handle(): void
    {
        $recipients = User::query()
            ->whereHas('role', fn ($query) => $query->where('code', Role::MODERATOR))
            ->pluck('email');

        // Отправлять некому — это не ошибка, задание просто завершается.
        if ($recipients->isEmpty()) {
            Log::info('Письмо о новой новости не отправлено: нет модераторов', [
                'article_id' => $this->article->id,
            ]);

            return;
        }

        try {
            Mail::to($recipients)->send(
                new NewArticlePublished($this->article, $this->author)
            );
        } catch (Throwable $e) {
            Log::warning('Не удалось отправить письмо о новой новости', [
                'article_id' => $this->article->id,
                'error' => $e->getMessage(),
            ]);

            // Пробрасываем исключение дальше: очередь повторит задание,
            // а после $tries попыток оно окажется в таблице failed_jobs.
            throw $e;
        }
    }
}
