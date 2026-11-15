<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Support\ArticleAnnouncement;
use App\Support\ArticleCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ArticleController extends Controller
{
    /** Сколько новостей показывать на одной странице списка. */
    private const PER_PAGE = 6;

    /** Файлы картинок, из которых можно выбрать превью и полное изображение. */
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
     * Правила валидации и сообщения об ошибках — одни и те же
     * при создании и при редактировании статьи.
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
     * Публичный список новостей с пагинацией (главная страница).
     */
    public function home()
    {
        // Лабораторная работа №13: главная кэшируется постранично (remember).
        $articles = ArticleCache::rememberList('home', $this->currentPage(), function () {
            return Article::query()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(self::PER_PAGE);
        });

        return view('pages.articles.index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Список новостей для управления: таблица с кнопками «изменить» и «удалить».
     */
    public function index()
    {
        // Список управления — только для модератора (политика ArticlePolicy).
        Gate::authorize('viewAny', Article::class);

        // Таблица управления тоже с пагинацией, поэтому кэшируем теми же ключами.
        $articles = ArticleCache::rememberList('admin', $this->currentPage(), function () {
            return Article::query()
                ->orderByDesc('published_at')
                ->orderByDesc('id')
                ->paginate(10);
        });

        return view('articles.index', [
            'articles' => $articles,
        ]);
    }

    /**
     * Одна новость для посетителя сайта.
     */
    public function show(Article $article)
    {
        /*
         | На странице новости показываем только комментарии, прошедшие
         | модерацию (лабораторная работа №9). Остальные ждут в очереди.
         |
         | Лабораторная работа №13: статья вместе с комментариями кэшируется
         | навсегда (rememberForever). Кэш этой страницы сбрасывается при
         | изменении или удалении новости (Cache::flush), а также при
         | модерации комментария (ArticleCache::forgetShow).
         */
        $cached = ArticleCache::rememberShow($article);

        // Модератору подсказываем, сколько комментариев ещё ждёт проверки.
        // Это зависит от того, кто смотрит страницу, поэтому не кэшируется.
        $pendingComments = auth()->user()?->can('manage-comments')
            ? $cached->comments()->pending()->count()
            : 0;

        return view('pages.articles.show', [
            'article' => $cached,
            'pendingComments' => $pendingComments,
        ]);
    }

    /**
     * Форма создания новости.
     */
    public function create()
    {
        Gate::authorize('create', Article::class);

        $article = new Article;
        $article->published_at = now();

        return view('articles.create', [
            'article' => $article,
            'images' => $this->availableImages(),
        ]);
    }

    /**
     * Сохранение новой новости (Create).
     */
    public function store(Request $request)
    {
        Gate::authorize('create', Article::class);

        $article = Article::create($this->validated($request));

        /*
         | Лабораторные работы №8, №10, №11 и №12.
         | Письмо модераторам (через очередь), событие широковещания и
         | уведомления читателям — всё это делает ArticleAnnouncement:
         | тот же набор действий нужен и REST API (лабораторная работа №15).
         */
        ArticleAnnouncement::publish($article, auth()->user());

        // Лабораторная работа №13: список новостей изменился — сбрасываем
        // кэш всех его страниц (forget).
        ArticleCache::forgetLists();

        return redirect()
            ->route('articles.index')
            ->with('status', 'Новость создана, письмо модератору поставлено в очередь.');
    }

    /**
     * Форма редактирования (Read для управления).
     */
    public function edit(Article $article)
    {
        Gate::authorize('update', $article);

        return view('articles.edit', [
            'article' => $article,
            'images' => $this->availableImages(),
        ]);
    }

    /**
     * Обновление новости (Update).
     */
    public function update(Request $request, Article $article)
    {
        Gate::authorize('update', $article);

        $article->update($this->validated($request));

        // Лабораторная работа №13: чистим кэш целиком (flush) — статья
        // могла попасть и в список, и в свою страницу просмотра.
        Cache::flush();

        return redirect()
            ->route('articles.index')
            ->with('status', 'Новость обновлена.');
    }

    /**
     * Удаление новости (Delete).
     */
    public function destroy(Article $article)
    {
        Gate::authorize('delete', $article);

        $article->delete();

        // Лабораторная работа №13: после удаления кэш тоже чистим полностью.
        Cache::flush();

        return redirect()
            ->route('articles.index')
            ->with('status', 'Новость удалена.');
    }

    /**
     * Номер текущей страницы из запроса (?page=2). Нужен для ключа кэша:
     * каждая страница списка кэшируется отдельно.
     */
    private function currentPage(): int
    {
        return max((int) request()->query('page', 1), 1);
    }
}
