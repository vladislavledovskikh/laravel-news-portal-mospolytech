<?php

namespace App\Http\Middleware;

use App\Models\Article;
use App\Models\ArticleView;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * Сохраняет просмотр новости (лабораторная работа №14).
 *
 * Посредник навешивается на маршрут /news/{article}. После того как
 * контроллер отдал страницу, он пишет в таблицу article_views строку:
 * адрес запроса, id статьи, ip и браузер посетителя.
 */
class TrackArticleView
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Считаем только успешно открытые страницы новости.
        if (! $response->isSuccessful()) {
            return $response;
        }

        // В маршруте /news/{article} лежит уже загруженная модель (route model binding).
        $article = $request->route('article');

        if (! $article instanceof Article) {
            return $response;
        }

        try {
            ArticleView::create([
                'article_id' => $article->id,
                'url' => '/'.$request->path(),
                'ip' => $request->ip(),
                'user_agent' => mb_substr((string) $request->userAgent(), 0, 255),
            ]);
        } catch (Throwable $e) {
            /*
             | Ошибка записи статистики не должна ломать страницу новости:
             | сообщаем о ней в лог и отдаём ответ как обычно.
             */
            report($e);
        }

        return $response;
    }
}
