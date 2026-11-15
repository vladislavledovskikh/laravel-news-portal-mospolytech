<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Политика для комментариев: добавлять может любой авторизованный
 * (читатель тоже), удалять — только модератор.
 */
class CommentPolicy
{
    /**
     * Добавление комментария. Доступно и читателю, и модератору.
     */
    public function create(User $user): Response
    {
        return Response::allow();
    }

    /**
     * Удаление комментария — только модератор.
     */
    public function delete(User $user, Comment $comment): Response
    {
        return $user->isModerator()
            ? Response::allow()
            : Response::deny('Удалять комментарии может только модератор.');
    }
}
