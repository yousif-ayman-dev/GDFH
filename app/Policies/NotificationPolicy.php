<?php

namespace App\Policies;

use App\Models\AppNotification;
use App\Models\User;

class NotificationPolicy
{
    /**
     * Determine whether the user can view the notification.
     */
    public function view(User $user, AppNotification $notification): bool
    {
        return (int) $notification->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can update/mark as read the notification.
     */
    public function update(User $user, AppNotification $notification): bool
    {
        return (int) $notification->user_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the notification.
     */
    public function delete(User $user, AppNotification $notification): bool
    {
        return (int) $notification->user_id === (int) $user->id;
    }
}
