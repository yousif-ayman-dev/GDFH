<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;

class TeamPolicy
{
    /**
     * Determine whether the user can view the team.
     */
    public function view(User $user, Team $team): bool
    {
        return $this->getRoleRank($user, $team) >= 1;
    }

    /**
     * Determine whether the user can update the team details.
     */
    public function update(User $user, Team $team): bool
    {
        return $this->getRoleRank($user, $team) >= 4;
    }

    /**
     * Determine whether the user can delete the team.
     */
    public function delete(User $user, Team $team): bool
    {
        return $this->getRoleRank($user, $team) === 5;
    }

    /**
     * Determine whether the user can transfer ownership of the team.
     */
    public function transferOwnership(User $user, Team $team): bool
    {
        return $this->getRoleRank($user, $team) === 5;
    }

    /**
     * Determine whether the user can invite or add members to the team.
     */
    public function addMember(User $user, Team $team): bool
    {
        return $this->getRoleRank($user, $team) >= 3;
    }

    /**
     * Determine whether the user can update a member's role.
     */
    public function updateMemberRole(User $user, Team $team, TeamMember $targetMember, string $newRole): bool
    {
        $userRank = $this->getRoleRank($user, $team);
        $targetRank = $this->getMemberRank($targetMember, $team);
        $newRoleRank = $this->parseRoleRank($newRole);

        // 1. Only Owner (5) and Admin (4) can manage roles
        if ($userRank < 4) {
            return false;
        }

        // 2. Cannot modify Owner role directly
        if ($targetMember->role === 'owner' || $targetMember->user_id === $team->owner_id) {
            return false;
        }

        // 3. Cannot set role to owner via role update
        if ($newRole === 'owner') {
            return false;
        }

        // 4. Cannot modify someone with equal or higher rank
        if ($targetRank >= $userRank) {
            return false;
        }

        // 5. Cannot promote to a rank equal to or higher than your own rank
        if ($newRoleRank >= $userRank) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can remove a member from the team.
     */
    public function removeMember(User $user, Team $team, TeamMember $targetMember): bool
    {
        $userRank = $this->getRoleRank($user, $team);
        $targetRank = $this->getMemberRank($targetMember, $team);

        // 1. Manager, Member, Viewer cannot remove members
        if ($userRank < 4) {
            return false;
        }

        // 2. Cannot remove the team owner
        if ($targetMember->user_id === $team->owner_id || $targetMember->role === 'owner') {
            return false;
        }

        // 3. Cannot remove someone with equal or higher rank
        if ($targetRank >= $userRank) {
            return false;
        }

        return true;
    }

    /**
     * Get the role rank integer for a user within a team.
     */
    public function getRoleRank(User $user, Team $team): int
    {
        if ($team->owner_id === $user->id) {
            return 5;
        }

        $membership = $team->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->first();

        if (! $membership) {
            return 0;
        }

        return $this->parseRoleRank($membership->role);
    }

    /**
     * Get the role rank integer for a TeamMember model.
     */
    protected function getMemberRank(TeamMember $member, Team $team): int
    {
        if ($member->user_id === $team->owner_id) {
            return 5;
        }

        return $this->parseRoleRank($member->role);
    }

    /**
     * Parse string role to numeric rank.
     */
    protected function parseRoleRank(string $role): int
    {
        return match ($role) {
            'owner' => 5,
            'admin' => 4,
            'manager' => 3,
            'member' => 2,
            'viewer' => 1,
            default => 0,
        };
    }
}
