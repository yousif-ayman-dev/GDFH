<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\Project;
use App\Models\User;

class CommentPolicy
{
    /**
     * Determine whether the user can view any comments.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the comment.
     */
    public function view(User $user, Comment $comment): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create comments.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the comment.
     */
    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete/moderate the comment.
     */
    public function delete(User $user, Comment $comment): bool
    {
        // 1. Comment author can delete own comment
        if ($comment->user_id === $user->id) {
            return true;
        }

        // 2. Project or resource owner/admin can moderate comments
        $subject = $comment->commentable;

        if ($subject instanceof Project) {
            return $user->can('update', $subject);
        }

        if (isset($subject->owner_id) && $subject->owner_id === $user->id) {
            return true;
        }

        return false;
    }
}
