<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Worklog;

class WorklogPolicy
{
    /**
     * Determine whether the user can view the worklog.
     */
    public function view(User $user, Worklog $worklog): bool
    {
        if ((int) $worklog->user_id === (int) $user->id) {
            return true;
        }

        return $worklog->project && (int) $worklog->project->owner_id === (int) $user->id;
    }

    /**
     * Determine whether the user can update/manage the worklog.
     */
    public function update(User $user, Worklog $worklog): bool
    {
        if ((int) $worklog->user_id === (int) $user->id) {
            return true;
        }

        return $worklog->project && (int) $worklog->project->owner_id === (int) $user->id;
    }

    /**
     * Determine whether the user can delete the worklog.
     */
    public function delete(User $user, Worklog $worklog): bool
    {
        if ((int) $worklog->user_id === (int) $user->id) {
            return true;
        }

        return $worklog->project && (int) $worklog->project->owner_id === (int) $user->id;
    }
}
