<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Support\ArticleAnnouncement;
use App\Support\ArticleCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Новости через REST API (лабораторная работа №15).
 *
 * Копия веб-контроллера App\Http\Controllers\ArticleController для работы
 * без Blade: вместо `return view(...)` и `return redirect(...)` каждый метод
 * возвращает `response()->json(...)`, а права по-прежнему проверяют
 * политики ArticlePolicy (Gate::authorize).
 *
 *   GET    /api/articles          список с пагинацией — доступен всем
 *   GET    /api/articles/{id}     одна новость с принятыми комментариями
 *   POST   /api/articles          создать (только модератор)
 *   PUT    /api/articles/{id}     обновить (только модератор)
 *   DELETE /api/articles/{id}     удалить (только модератор)
 */
class ArticleController extends Controller
{
    /** Сколько новостей отдавать на страницу по умолчанию. */
    private const PER_PAGE = 6;

    /** Верхняя граница параметра ?per_page=, чтобы клиент не выкачал всю базу. */
    private const MAX_PER_PAGE = 50;

    /**
     * Файлы картинок, из которых можно выбрать превью и полное изображение.
     *
     * @return list<string>
     */
    private function availableImages(): array
    {
        $path = public_path('images');

        if (! is_dir($path)) {
            return [];
        }

        $files = scandir($path) ?: [];

        return array_values(array_filter(
            $files,
            fn (string $file) => (bool) preg_match('/\.(jpe?g|png|webp)$/i', $file)
        ));
    }

    /**
     * Правила валидации и сообщения об ошибках — те же, что на сайте.
     */
    private function validated(Request $request): array
    {
        $images = $this->availableImages();

        return $request->validate(
            [
                'title' => ['required', 'string', 'min:3', 'max:255'],
                'short_description' => ['nullable', 'string', 'max:255'],
                'content' => ['required', 'string', 'min:20'],
                'published_at' => ['required', 'date'],
                'preview_image' => ['required', Rule::in($images)],
                'full_image' => ['required', Rule::in($images)],
            ],
            [
                'title.required' => 'Укажите заголовок новости.',
                'title.min' => 'Заголовок должен быть не короче 3 символов.',
                'title.max' => 'Заголовок не должен превышать 255 символов.',
                'short_description.max' => 'Краткое описание не должно превышать 255 символов.',
                'content.required' => 'Добавьте текст новости.',
                'content.min' => 'Текст новости должен быть не короче 20 символов.',
                'published_at.required' => 'Укажите дату публикации.',
                'published_at.date' => 'Дата публикации должна быть корректной датой.',
                'preview_image.required' => 'Выберите изображение для превью.',
                'preview_image.in' => 'Выберите превью из списка доступных файлов.',
                'full_image.required' => 'Выберите полное изображение.',
                'full_image.in' => 'Выберите полное изображение из списка доступных файлов.',
            ]
        );
    }

    /**
     * Список новостей с пагинацией (публичный).
     *
     * Вместо контроллера главной страницы с Blade — пагинатор в JSON:
     * data, links, meta (current_page, last_page, total).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', self::PER_PAGE);
        $perPage = min(max($perPage, 1), self::MAX_PER_PAGE);

        $articles = Article::query()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage);

        return response()->json($articles);
    }

    /**
     * Одна новость с комментариями, прошедшими модерацию.
     */
    public function show(Article $article): JsonResponse
    {
        $article->load([
            'comments' => fn ($query) => $query->approved()->with('user:id,name'),
        ]);

        return response()->json($article);
    }

    /**
     * Создание новости. Доступно только модератору (ArticlePolicy).
     */
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', Article::class);

        $article = Article::create($this->validated($request));

        // Те же действия, что и на сайте: письмо, вещание, уведомления читателям.
        ArticleAnnouncement::publish($article, $request->user());

        // Лабораторная работа №13: список изменился — сбрасываем его кэш.
        ArticleCache::forgetLists();

        return response()->json([
            'status' => 'ok',
            'message' => 'Новость создана, письмо модератору поставлено в очередь.',
            'article' => $article,
        ], 201);
    }

    /**
     * Обновление новости. Доступно только модератору.
     */
    public function update(Request $request, Article $article): JsonResponse
    {
        Gate::authorize('update', $article);

        $article->update($this->validated($request));

        // Сбрасываем кэш целиком: новость могла попасть и в список, и в страницу.
        Cache::flush();

        return response()->json([
            'status' => 'ok',
            'message' => 'Новость обновлена.',
            'article' => $article->fresh(),
        ]);
    }

    /**
     * Удаление новости. Доступно только модератору.
     */
    public function destroy(Article $article): JsonResponse
    {
        Gate::authorize('delete', $article);

        $article->delete();

        Cache::flush();

        return response()->json([
            'status' => 'ok',
            'message' => 'Новость удалена.',
        ]);
    }
}
