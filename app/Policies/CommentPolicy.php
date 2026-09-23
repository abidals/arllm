<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy
{
    public function moderate(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }

    public function delete(User $user, Comment $comment): bool
    {
        if ($user->hasAnyRole(['admin', 'editor'])) {
            return true;
        }

        return $comment->user_id === $user->id;
    }
}