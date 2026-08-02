<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeamInvitationService
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Create and send a new team invitation.
     */
    public function sendInvitation(Team $team, User $inviter, User $invitee, array $data): TeamInvitation
    {
        return DB::transaction(function () use ($team, $inviter, $invitee, $data) {
            return TeamInvitation::create([
                'team_id' => $team->id,
                'inviter_id' => $inviter->id,
                'invitee_id' => $invitee->id,
                'role' => $data['role'] ?? 'member',
                'message' => $data['message'] ?? null,
                'status' => 'pending',
            ]);
        });
    }

    /**
     * Accept a pending team invitation and add user to team members.
     */
    public function acceptInvitation(TeamInvitation $invitation): void
    {
        DB::transaction(function () use ($invitation) {
            $invitation->update([
                'status' => 'accepted',
                'responded_at' => now(),
            ]);

            $this->activityService->logInvitationAccepted($invitation->invitee, $invitation);

            $memberRole = match ($invitation->role) {
                'owner' => 'owner',
                'admin' => 'manager',
                default => 'member',
            };

            $existingMember = TeamMember::query()
                ->where('team_id', $invitation->team_id)
                ->where('user_id', $invitation->invitee_id)
                ->first();

            if (! $existingMember) {
                TeamMember::create([
                    'team_id' => $invitation->team_id,
                    'user_id' => $invitation->invitee_id,
                    'role' => $memberRole,
                    'status' => 'active',
                    'joined_at' => now(),
                    'invited_by' => $invitation->inviter_id,
                ]);
            } else {
                $existingMember->update([
                    'status' => 'active',
                    'role' => $memberRole,
                    'joined_at' => $existingMember->joined_at ?? now(),
                ]);
            }
        });
    }

    /**
     * Reject a pending team invitation.
     */
    public function rejectInvitation(TeamInvitation $invitation): void
    {
        $invitation->update([
            'status' => 'rejected',
            'responded_at' => now(),
        ]);
    }

    /**
     * Cancel a pending invitation.
     */
    public function cancelInvitation(TeamInvitation $invitation): void
    {
        $invitation->update([
            'status' => 'cancelled',
        ]);
    }
}
