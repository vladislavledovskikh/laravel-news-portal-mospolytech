<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\CommentPolicy;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        /*
         | Хук Gate::before выполняется РАНЬШЕ любых политик и шлюзов.
         | Модератору разрешено всё: вернув true, мы не даём отработать
         | остальным проверкам. Для остальных возвращаем null — пусть
         | решают политики и обычные шлюзы.
         */
        Gate::before(function (User $user, string $ability) {
            if ($user->isModerator()) {
                return true;
            }

            return null;
        });

        /*
         | Обычный шлюз со своим ответом на отказ.
         */
        Gate::define('manage-comments', function (User $user) {
            return $user->isModerator()
                ? Response::allow()
                : Response::deny('Модерация комментариев доступна только модератору.');
        });

        /*
         | Регистрируем политики явно (Laravel нашёл бы их и по соглашению
         | об именах, но так видно, что именно подключено).
         */
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
    }
}
