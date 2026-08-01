<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;

class TeamInvitationPolicy
{
    /**
     * Determine whether the user can create team invitations.
     */
    public function create(User $user, Team $team): bool
    {
        if ($team->owner_id === $user->id) {
            return true;
        }

        return $team->memberships()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn('role', ['owner', 'manager', 'admin', 'team_leader'])
            ->exists();
    }

    /**
     * Determine whether the user can accept the invitation.
     */
    public function accept(User $user, TeamInvitation $invitation): bool
    {
        return $invitation->invitee_id === $user->id
            && $invitation->isPending();
    }

    /**
     * Determine whether the user can reject the invitation.
     */
    public function reject(User $user, TeamInvitation $invitation): bool
    {
        return $invitation->invitee_id === $user->id
            && $invitation->isPending();
    }

    /**
     * Determine whether the user can cancel the invitation.
     */
    public function cancel(User $user, TeamInvitation $invitation): bool
    {
        return ($invitation->inviter_id === $user->id || $invitation->team->owner_id === $user->id)
            && $invitation->isPending();
    }
}
