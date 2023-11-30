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
    public function view(User $user, Article $article): bool
    {
        $authors = $article->meta()->get('authors')->unwrapOrDefault([]);
        $isAuthor = in_array($user->name, $authors);
        // The author can always view the article
        if ($isAuthor) {
            return true;
        }
        $visibility = $article->meta()->get('visibility')->unwrapOrDefault('private');
        if ($visibility === null) {
            return false;
        }
        switch ($visibility) {
            case 'public':
                return true;
            case 'private':
                // If the user is not the author, then they can't view the article
                return false;
            case 'restricted':
                $allowedUsers = $article->meta()->get('allowed_users')->unwrapOrDefault([]);
                $isAllowed = in_array($user->name, $allowedUsers);
                if (!$isAllowed) {
                    // Maybe roles?
                    $allowedRoles = $article->meta()->get('allowed_roles')->unwrapOrDefault([]);
                    $isAllowed = $user->roles()->get()
                        ->first(fn(Role $role) => in_array($role->name, $allowedRoles)) !== null;
                }
                return $isAllowed;
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
        return $user->hasPermission('article.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Article $article): bool
    {
        return $user->hasPermission('article.update') || $user->id === $article->author_id;
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
