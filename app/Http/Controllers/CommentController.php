<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Support\ArticleCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    /**
     * Добавление комментария к новости.
     *
     * Право проверяет CommentPolicy: комментарии может писать любой
     * авторизованный пользователь, в том числе читатель.
     */
    public function store(Request $request, Article $article)
    {
        Gate::authorize('create', Comment::class);

        $data = $request->validate(
            [
                'body' => ['required', 'string', 'min:2', 'max:1000'],
            ],
            [
                'body.required' => 'Напишите текст комментария.',
                'body.min' => 'Комментарий должен быть не короче 2 символов.',
                'body.max' => 'Комментарий не должен превышать 1000 символов.',
            ]
        );

        $article->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            // Новый комментарий появится на сайте только после модерации.
            'is_approved' => false,
        ]);

        return back()->with(
            'status',
            'Спасибо! Комментарий отправлен на проверку — он появится на странице после модерации.'
        );
    }

    /**
     * Удаление комментария — только модератор.
     */
    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $articleId = $comment->article_id;

        $comment->delete();

        // Страница новости закэширована — список комментариев изменился.
        ArticleCache::forgetShow($articleId);

        return back()->with('status', 'Комментарий удалён.');
    }

    /**
     * Очередь модерации: только комментарии, которые ещё не проверены.
     *
     * Доступ — через шлюз manage-comments (AuthServiceProvider): он разрешён
     * только модератору, остальные получают страницу 403.
     */
    public function moderation()
    {
        Gate::authorize('manage-comments');

        $comments = Comment::query()
            ->pending()
            ->with(['user', 'article'])
            ->oldest()
            ->paginate(10);

        return view('comments.moderation', [
            'comments' => $comments,
        ]);
    }

    /**
     * Принять комментарий: он становится виден на странице новости.
     */
    public function approve(Comment $comment)
    {
        Gate::authorize('manage-comments');

        $comment->update([
            'is_approved' => true,
            'approved_at' => now(),
        ]);

        // Принятый комментарий появляется на странице новости — сбрасываем её кэш.
        ArticleCache::forgetShow($comment->article_id);

        return back()->with('status', 'Комментарий принят и опубликован.');
    }

    /**
     * Отклонить комментарий: удаляем его из базы.
     */
    public function reject(Comment $comment)
    {
        Gate::authorize('manage-comments');

        $articleId = $comment->article_id;

        $comment->delete();

        // Кэш страницы новости пересоберётся при следующем открытии.
        ArticleCache::forgetShow($articleId);

        return back()->with('status', 'Комментарий отклонён и удалён.');
    }
}
