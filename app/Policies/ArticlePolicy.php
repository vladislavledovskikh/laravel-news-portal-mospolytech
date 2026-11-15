<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Политика для статей: любые изменения — только модератор.
 *
 * Читатель может только смотреть новости (публичные страницы вообще
 * не требуют прав), поэтому все методы управления возвращают отказ
 * со своим текстом.
 */
class ArticlePolicy
{
    /**
     * Список управления новостями.
     */
    public function viewAny(User $user): Response
    {
        return $this->moderatorOnly($user);
    }

    /**
     * Просмотр новости разрешён всем.
     */
    public function view(User $user, Article $article): Response
    {
        return Response::allow();
    }

    public function create(User $user): Response
    {
        return $this->moderatorOnly($user);
    }

    public function update(User $user, Article $article): Response
    {
        return $this->moderatorOnly($user);
    }

    public function delete(User $user, Article $article): Response
    {
        return $this->moderatorOnly($user);
    }

    /**
     * Свой ответ на отказ: текст увидит пользователь.
     */
    private function moderatorOnly(User $user): Response
    {
        return $user->isModerator()
            ? Response::allow()
            : Response::deny('Управлять новостями может только модератор.');
    }
}
