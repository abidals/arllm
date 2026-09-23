<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'editor', 'author']);
    }

    public function update(User $user, Post $post): bool
    {
        if ($user->hasAnyRole(['admin', 'editor'])) {
            return true;
        }

        return $post->user_id === $user->id;
    }

    public function delete(User $user, Post $post): bool
    {
        if ($user->hasAnyRole(['admin', 'editor'])) {
            return true;
        }

        return $post->user_id === $user->id && $post->status !== 'published';
    }

    public function publish(User $user, Post $post = null): bool
    {
        return $user->hasAnyRole(['admin', 'editor']);
    }
}