<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    /**
     * Determine whether the user can view any tasks.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the task.
     */
    public function view(User $user, Task $task): bool
    {
        return $user->can('view', $task->project);
    }

    /**
     * Determine whether the user can create tasks within a project.
     */
    public function create(User $user, ?Project $project = null): bool
    {
        if (! $project) {
            return true;
        }

        return $user->can('view', $project);
    }

    /**
     * Determine whether the user can update the task.
     */
    public function update(User $user, Task $task): bool
    {
        if ($user->id === $task->created_by || $user->id === $task->assigned_to) {
            return true;
        }

        return $user->can('update', $task->project);
    }

    /**
     * Determine whether the user can delete the task.
     */
    public function delete(User $user, Task $task): bool
    {
        if ($user->id === $task->created_by) {
            return true;
        }

        return $user->can('update', $task->project);
    }
}
