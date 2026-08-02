<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view any projects.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        if ($project->owner_id === $user->id) {
            return true;
        }

        if ($project->visibility === 'marketplace' || $project->visibility === 'public') {
            return true;
        }

        if ($project->members()->where('user_id', $user->id)->where('status', 'active')->exists()) {
            return true;
        }

        if ($project->team_id && $project->team) {
            return $project->team->memberships()
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create projects.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the project.
     */
    public function update(User $user, Project $project): bool
    {
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $project->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin', 'manager'])
            ->exists();
    }

    /**
     * Determine whether the user can delete the project.
     */
    public function delete(User $user, Project $project): bool
    {
        return $project->owner_id === $user->id;
    }

    /**
     * Determine whether the user can archive the project.
     */
    public function archive(User $user, Project $project): bool
    {
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $project->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Determine whether the user can restore the project.
     */
    public function restore(User $user, Project $project): bool
    {
        if ($project->owner_id === $user->id) {
            return true;
        }

        return $project->members()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }
}
