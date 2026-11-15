<?php

namespace App\Support;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Кэш новостей и комментариев (лабораторная работа №13).
 *
 * Ключи собраны в одном месте, чтобы контроллеры статей и комментариев
 * сбрасывали одно и то же. Драйвер кэша — database (таблица `cache`).
 *
 *  - списки новостей кэшируются постранично: articles.list.{scope}.page.{n};
 *    remembered-ключи складываются в реестр, и по нему одной пачкой
 *    удаляются все страницы (Cache::forget);
 *  - страница новости кэшируется навсегда: articles.show.{id}.
 */
class ArticleCache
{
    /** Сколько секунд живёт страница списка. */
    public const LIST_TTL = 600;

    /** Ключ реестра: какие страницы списков сейчас лежат в кэше. */
    private const LIST_REGISTRY = 'articles.list.keys';

    /**
     * Ключ страницы списка. scope — 'home' (главная) или 'admin' (управление).
     */
    public static function listKey(string $scope, int $page): string
    {
        return "articles.list.{$scope}.page.{$page}";
    }

    /**
     * Запоминаем ключ страницы в реестре.
     *
     * Нужен, чтобы при создании новости удалить кэш не только первой страницы,
     * но и всех остальных: ключей может быть сколько угодно.
     */
    public static function trackListKey(string $key): void
    {
        $keys = Cache::get(self::LIST_REGISTRY, []);

        if (! in_array($key, $keys, true)) {
            $keys[] = $key;
            Cache::forever(self::LIST_REGISTRY, $keys);
        }
    }

    /**
     * Страница списка новостей из кэша (или из базы при первом обращении).
     *
     * В кэш уходит массив: коллекция моделей и сам пагинатор — объекты,
     * а объекты из database-кэша при чтении превращаются в
     * __PHP_Incomplete_Class. Поэтому храним только данные (строки таблицы
     * и параметры пагинации), а пагинатор собираем заново на каждый запрос.
     */
    public static function rememberList(string $scope, int $page, Closure $query): LengthAwarePaginator
    {
        $key = self::listKey($scope, $page);

        $payload = Cache::remember($key, self::LIST_TTL, function () use ($query) {
            /** @var LengthAwarePaginator $paginator */
            $paginator = $query();

            return [
                'items' => $paginator->getCollection()
                    ->map(fn ($article) => $article->toArray())
                    ->all(),
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'path' => $paginator->path(),
            ];
        });

        self::trackListKey($key);

        return new Paginator(
            collect($payload['items'])->map(fn (array $row) => self::hydrateArticle($row)),
            $payload['total'],
            $payload['per_page'],
            $payload['current_page'],
            ['path' => $payload['path'], 'query' => request()->query()],
        );
    }

    /**
     * Удаляет из кэша все страницы списков новостей.
     */
    public static function forgetLists(): void
    {
        foreach (Cache::get(self::LIST_REGISTRY, []) as $key) {
            Cache::forget($key);
        }

        Cache::forget(self::LIST_REGISTRY);
    }

    /**
     * Ключ страницы просмотра новости (со списком комментариев).
     */
    public static function showKey(int $articleId): string
    {
        return "articles.show.{$articleId}";
    }

    /**
     * Страница новости вместе с принятыми комментариями: берём из кэша
     * или загружаем из базы и кладём в кэш навсегда.
     *
     * В кэш уходит массив (toArray()), а не модель: модель в database-кэше
     * при чтении восстанавливается как incomplete object, поэтому данные
     * сериализуем в простой массив, а на выходе собираем модели обратно —
     * для шаблона и политик это обычный Article с комментариями.
     */
    public static function rememberShow(Article $article): Article
    {
        $payload = Cache::rememberForever(
            self::showKey($article->id),
            function () use ($article) {
                $article->load([
                    'comments' => fn ($query) => $query->approved()->latest(),
                    'comments.user',
                ]);

                return $article->toArray();
            }
        );

        return self::hydrateShow($payload);
    }

    /**
     * Сбрасывает кэш одной новости — например, после модерации комментария.
     */
    public static function forgetShow(int $articleId): void
    {
        Cache::forget(self::showKey($articleId));
    }

    /**
     * Собирает Article обратно из массива, полученного из кэша.
     *
     * @param  array<string, mixed>  $data
     */
    private static function hydrateShow(array $data): Article
    {
        $comments = collect($data['comments'] ?? [])
            ->map(function (array $row) {
                $comment = (new Comment)->newFromBuilder(Arr::except($row, ['user']));
                $comment->setRelation('user', (new User)->newFromBuilder($row['user'] ?? []));

                return $comment;
            });

        $article = self::hydrateArticle(Arr::except($data, ['comments']));
        $article->setRelation('comments', $comments);

        return $article;
    }

    /**
     * Собирает Article из массива атрибутов.
     *
     * @param  array<string, mixed>  $row
     */
    private static function hydrateArticle(array $row): Article
    {
        return (new Article)->newFromBuilder($row);
    }
}
