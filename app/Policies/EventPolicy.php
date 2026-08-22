<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event): bool
    {
        if ($event->user_id === $user->id) {
            return true;
        }

        if ($event->project_id && $event->project) {
            return $user->can('view', $event->project);
        }

        return false;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event): bool
    {
        if ($event->user_id === $user->id) {
            return true;
        }

        if ($event->project_id && $event->project) {
            return $event->project->owner_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event): bool
    {
        if ($event->user_id === $user->id) {
            return true;
        }

        if ($event->project_id && $event->project) {
            return $event->project->owner_id === $user->id;
        }

        return false;
    }
}
