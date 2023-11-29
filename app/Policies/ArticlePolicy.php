<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class ArticlePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Article $post): bool
    {
        if ($user->id === $post->author_id) {
            return true;
        }
        $visibility = $post->setting('visibility');
        if ($visibility === null) {
            return false;
        }
        switch ($visibility->value) {
            case 'public':
                return true;
            case 'private':
                return $user->id === $post->author_id;
            case 'restricted':
                $allowedUsers = $post->setting('allowed_users');
                if ($allowedUsers === null) {
                    return false;
                }
                return $allowedUsers->value->contains($user->id);
            case 'unlisted':
                return false;
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        $role_with_permission = $user->roles()->get()
            ->first(fn(Role $role) => $role->hasPermission('article.create'));
        return $role_with_permission !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Article $article): bool
    {
        $role_with_permission = $user->roles()->get()
            ->first(fn(Role $role) => $role->hasPermission('article.update'));
        return $role_with_permission !== null;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Article $post): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Article $post): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Article $post): bool
    {
        //
    }
}
