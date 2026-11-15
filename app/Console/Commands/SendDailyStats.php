<?php

namespace App\Console\Commands;

use App\Mail\DailySiteStats;
use App\Models\ArticleView;
use App\Models\Comment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Ежедневная статистика сайта для модераторов (лабораторная работа №14).
 *
 * Команда запускается планировщиком (routes/console.php) раз в минуту,
 * считает просмотры новостей и новые комментарии за сегодня и отправляет
 * сводку письмом всем модераторам.
 */
class SendDailyStats extends Command
{
    /**
     * @var string
     */
    protected $signature = 'stats:daily';

    /**
     * @var string
     */
    protected $description = 'Отправляет модераторам статистику сайта за сегодня: просмотры новостей и новые комментарии';

    public function handle(): int
    {
        /*
         | 1. Просмотры новостей: запрос к таблице с сохранёнными url.
         |    Группируем по адресу страницы, чтобы видеть самые читаемые новости.
         */
        $views = ArticleView::query()->today();

        $viewsTotal = (clone $views)->count();

        $topArticles = $views
            ->join('articles', 'articles.id', '=', 'article_views.article_id')
            ->selectRaw('article_views.url as url, articles.title as title, count(*) as views')
            ->groupBy('article_views.url', 'articles.title')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(fn ($row) => [
                'url' => $row->url,
                'title' => $row->title,
                'views' => (int) $row->views,
            ])
            ->all();

        /*
         | 2. Комментарии, добавленные сегодня, и сколько из них ещё ждёт модерации.
         */
        $commentsToday = Comment::query()->whereDate('created_at', today());
        $commentsTotal = (clone $commentsToday)->count();
        $commentsPending = (clone $commentsToday)->pending()->count();

        $date = today();

        $this->info('Просмотры новостей за '.$date->format('d.m.Y').': '.$viewsTotal);
        $this->info('Новые комментарии: '.$commentsTotal.' (без модерации: '.$commentsPending.')');

        /*
         | 3. Письмо получают все пользователи с ролью «модератор».
         */
        $recipients = User::query()
            ->whereHas('role', fn ($query) => $query->where('code', Role::MODERATOR))
            ->pluck('email');

        if ($recipients->isEmpty()) {
            $this->warn('Модераторов нет — письмо отправлять некому.');

            Log::info('Статистика сайта не отправлена: нет модераторов', [
                'views' => $viewsTotal,
                'comments' => $commentsTotal,
            ]);

            return self::SUCCESS;
        }

        try {
            Mail::to($recipients)->send(
                new DailySiteStats(
                    date: $date,
                    views: $viewsTotal,
                    comments: $commentsTotal,
                    commentsPending: $commentsPending,
                    topArticles: $topArticles,
                )
            );
        } catch (Throwable $e) {
            // Почтовый сервер недоступен — пишем в лог, но планировщик не роняем.
            $this->error('Не удалось отправить статистику: '.$e->getMessage());

            Log::warning('Не удалось отправить статистику сайта', [
                'error' => $e->getMessage(),
            ]);

            return self::FAILURE;
        }

        $this->info('Статистика отправлена: '.$recipients->implode(', '));

        return self::SUCCESS;
    }
}
