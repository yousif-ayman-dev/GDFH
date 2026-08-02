<?php

namespace App\Services;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TeamMemberService
{
    public function __construct(
        protected ActivityService $activityService
    ) {}

    /**
     * Add a new member to the team.
     */
    public function addMember(Team $team, User $user, string $role = 'member', string $status = 'active', ?int $invitedBy = null): TeamMember
    {
        if ($team->owner_id === $user->id) {
            throw ValidationException::withMessages([
                'user_id' => 'مالك الفريق موجود بالفعل في الفريق.',
            ]);
        }

        $existingMember = TeamMember::query()
            ->where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingMember && $existingMember->status === 'active') {
            throw ValidationException::withMessages([
                'user_id' => 'هذا المستخدم عضو بالفعل في الفريق.',
            ]);
        }

        if ($existingMember) {
            $existingMember->update([
                'role' => $role,
                'status' => $status,
                'joined_at' => $status === 'active' ? now() : $existingMember->joined_at,
                'invited_by' => $invitedBy ?? $existingMember->invited_by,
            ]);

            if ($status === 'active') {
                $inviter = $invitedBy ? User::find($invitedBy) : null;
                $this->activityService->logMemberJoined($inviter, $team, $user);
            }

            return $existingMember;
        }

        $member = TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => $status,
            'invited_by' => $invitedBy,
            'joined_at' => $status === 'active' ? now() : null,
        ]);

        if ($status === 'active') {
            $inviter = $invitedBy ? User::find($invitedBy) : null;
            $this->activityService->logMemberJoined($inviter, $team, $user);
        }

        return $member;
    }

    /**
     * Update the role of a team member.
     */
    public function updateRole(Team $team, TeamMember $member, string $newRole): void
    {
        if ($member->team_id !== $team->id) {
            throw ValidationException::withMessages([
                'member' => 'العضو غير موجود في هذا الفريق.',
            ]);
        }

        if ($member->role === 'owner' || $member->user_id === $team->owner_id) {
            throw ValidationException::withMessages([
                'role' => 'لا يمكن تغيير دور مالك الفريق بشكل مباشر. استخدم إجراء نقل الملكية بدلاً من ذلك.',
            ]);
        }

        if ($newRole === 'owner') {
            throw ValidationException::withMessages([
                'role' => 'لا يمكن الترقية إلى مالك عبر تغيير الدور. يرجى استخدام إجراء نقل ملكية الفريق.',
            ]);
        }

        $member->update([
            'role' => $newRole,
        ]);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, TeamMember $member): void
    {
        if ($member->team_id !== $team->id) {
            throw ValidationException::withMessages([
                'member' => 'العضو غير موجود في هذا الفريق.',
            ]);
        }

        if ($member->user_id === $team->owner_id || $member->role === 'owner') {
            $ownerCount = TeamMember::query()
                ->where('team_id', $team->id)
                ->where('role', 'owner')
                ->count();

            if ($ownerCount <= 1 || $team->owner_id === $member->user_id) {
                throw ValidationException::withMessages([
                    'member' => 'لا يمكن إزالة المالك الوحيد للفريق.',
                ]);
            }
        }

        $member->delete();
    }

    /**
     * Transfer team ownership to another user.
     */
    public function transferOwnership(Team $team, User $newOwner): void
    {
        if ($team->owner_id === $newOwner->id) {
            throw ValidationException::withMessages([
                'new_owner_id' => 'هذا المستخدم هو بالفعل مالك الفريق.',
            ]);
        }

        DB::transaction(function () use ($team, $newOwner) {
            $oldOwnerId = $team->owner_id;

            // 1. Update old owner role to admin
            $oldOwnerMember = TeamMember::query()
                ->where('team_id', $team->id)
                ->where('user_id', $oldOwnerId)
                ->first();

            if ($oldOwnerMember) {
                $oldOwnerMember->update(['role' => 'admin']);
            } else {
                TeamMember::create([
                    'team_id' => $team->id,
                    'user_id' => $oldOwnerId,
                    'role' => 'admin',
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }

            // 2. Update new owner role to owner
            $newOwnerMember = TeamMember::query()
                ->where('team_id', $team->id)
                ->where('user_id', $newOwner->id)
                ->first();

            if ($newOwnerMember) {
                $newOwnerMember->update([
                    'role' => 'owner',
                    'status' => 'active',
                ]);
            } else {
                TeamMember::create([
                    'team_id' => $team->id,
                    'user_id' => $newOwner->id,
                    'role' => 'owner',
                    'status' => 'active',
                    'joined_at' => now(),
                ]);
            }

            // 3. Update team owner_id
            $team->update([
                'owner_id' => $newOwner->id,
            ]);
        });
    }
}
