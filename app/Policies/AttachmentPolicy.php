<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class AttachmentPolicy
{
    /**
     * Determine whether the user can view the attachment.
     */
    public function view(User $user, Attachment $attachment): bool
    {
        $subject = $attachment->attachable;

        if ($subject instanceof Project) {
            return $user->can('view', $subject);
        }

        if ($subject instanceof Task) {
            return $user->can('view', $subject);
        }

        if ($subject instanceof Comment) {
            $parent = $subject->commentable ?: $subject->task;
            while ($parent instanceof Comment) {
                $parent = $parent->commentable ?: $parent->task;
            }

            if ($parent instanceof Project || $parent instanceof Task) {
                return $user->can('view', $parent);
            }
        }

        // Standalone or unattached attachments fallback to uploader-only access
        if ($attachment->user_id === $user->id || $attachment->uploaded_by === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can download the attachment.
     */
    public function download(User $user, Attachment $attachment): bool
    {
        return $this->view($user, $attachment);
    }

    /**
     * Determine whether the user can update/replace the attachment.
     */
    public function update(User $user, Attachment $attachment): bool
    {
        if (! $this->view($user, $attachment)) {
            return false;
        }

        if ($attachment->user_id === $user->id || $attachment->uploaded_by === $user->id) {
            return true;
        }

        $subject = $attachment->attachable;

        if ($subject instanceof Project) {
            return $user->can('update', $subject);
        }

        if ($subject instanceof Task) {
            return $user->can('update', $subject);
        }

        if ($subject instanceof Comment) {
            return (int) $subject->user_id === (int) $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        if (! $this->view($user, $attachment)) {
            return false;
        }

        if ($attachment->user_id === $user->id || $attachment->uploaded_by === $user->id) {
            return true;
        }

        $subject = $attachment->attachable;

        if ($subject instanceof Project) {
            return $user->can('update', $subject);
        }

        if ($subject instanceof Task) {
            return $user->can('update', $subject);
        }

        if ($subject instanceof Comment) {
            return (int) $subject->user_id === (int) $user->id;
        }

        return false;
    }
}
