<?php

namespace App\Policies;

use App\Models\Attachment;
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

        if ($attachment->user_id === $user->id || $attachment->uploaded_by === $user->id) {
            return true;
        }

        return true;
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
        if ($attachment->user_id === $user->id || $attachment->uploaded_by === $user->id) {
            return true;
        }

        $subject = $attachment->attachable;

        if ($subject instanceof Project) {
            return $user->can('update', $subject);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the attachment.
     */
    public function delete(User $user, Attachment $attachment): bool
    {
        if ($attachment->user_id === $user->id || $attachment->uploaded_by === $user->id) {
            return true;
        }

        $subject = $attachment->attachable;

        if ($subject instanceof Project) {
            return $user->can('update', $subject);
        }

        return false;
    }
}
