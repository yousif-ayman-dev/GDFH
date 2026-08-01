<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamRolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'freelancer',
        ], $attributes));
    }

    protected function addMemberToTeam(Team $team, User $user, string $role = 'member'): TeamMember
    {
        return TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => $role,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_owner_has_full_permissions(): void
    {
        $owner = $this->createOnboardedUser();
        $admin = $this->createOnboardedUser();
        $member = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $this->addMemberToTeam($team, $owner, 'owner');
        $adminMembership = $this->addMemberToTeam($team, $admin, 'admin');
        $memberMembership = $this->addMemberToTeam($team, $member, 'member');

        // Owner can update member role
        $this->actingAs($owner)
            ->post(route('teams.members.update-role', [$team, $memberMembership]), ['role' => 'manager'])
            ->assertRedirect();

        $this->assertDatabaseHas('team_members', [
            'id' => $memberMembership->id,
            'role' => 'manager',
        ]);

        // Owner can remove member
        $this->actingAs($owner)
            ->delete(route('teams.members.destroy', [$team, $adminMembership]))
            ->assertRedirect();

        $this->assertDatabaseMissing('team_members', [
            'id' => $adminMembership->id,
        ]);
    }

    public function test_owner_can_transfer_ownership(): void
    {
        $owner = $this->createOnboardedUser();
        $newOwner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $oldOwnerMember = $this->addMemberToTeam($team, $owner, 'owner');
        $newOwnerMember = $this->addMemberToTeam($team, $newOwner, 'member');

        $response = $this->actingAs($owner)->post(route('teams.transfer-ownership', $team), [
            'new_owner_id' => $newOwner->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Check database state
        $this->assertDatabaseHas('teams', [
            'id' => $team->id,
            'owner_id' => $newOwner->id,
        ]);

        $this->assertDatabaseHas('team_members', [
            'id' => $oldOwnerMember->id,
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('team_members', [
            'id' => $newOwnerMember->id,
            'role' => 'owner',
        ]);
    }

    public function test_admin_permissions_and_limitations(): void
    {
        $owner = $this->createOnboardedUser();
        $admin = $this->createOnboardedUser();
        $manager = $this->createOnboardedUser();
        $member = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $ownerMember = $this->addMemberToTeam($team, $owner, 'owner');
        $adminMember = $this->addMemberToTeam($team, $admin, 'admin');
        $managerMember = $this->addMemberToTeam($team, $manager, 'manager');
        $memberMember = $this->addMemberToTeam($team, $member, 'member');

        // Admin can change Manager/Member roles to viewer or member
        $this->actingAs($admin)
            ->post(route('teams.members.update-role', [$team, $memberMember]), ['role' => 'viewer'])
            ->assertRedirect();

        $this->assertDatabaseHas('team_members', [
            'id' => $memberMember->id,
            'role' => 'viewer',
        ]);

        // Admin cannot modify Owner role -> 403
        $this->actingAs($admin)
            ->post(route('teams.members.update-role', [$team, $ownerMember]), ['role' => 'admin'])
            ->assertStatus(403);

        // Admin cannot promote someone to Admin or Owner -> 403
        $this->actingAs($admin)
            ->post(route('teams.members.update-role', [$team, $managerMember]), ['role' => 'admin'])
            ->assertStatus(403);

        // Admin cannot transfer ownership -> 403
        $this->actingAs($admin)
            ->post(route('teams.transfer-ownership', $team), ['new_owner_id' => $admin->id])
            ->assertStatus(403);

        // Admin cannot delete team -> 403
        $this->actingAs($admin)
            ->delete(route('teams.destroy', $team))
            ->assertStatus(403);
    }

    public function test_manager_permissions_and_limitations(): void
    {
        $owner = $this->createOnboardedUser();
        $manager = $this->createOnboardedUser();
        $member = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser(['username' => 'invitee_one']);
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $this->addMemberToTeam($team, $owner, 'owner');
        $this->addMemberToTeam($team, $manager, 'manager');
        $memberMembership = $this->addMemberToTeam($team, $member, 'member');

        // Manager CAN invite new members
        $this->actingAs($manager)
            ->post(route('teams.invitations.store', $team), [
                'username' => 'invitee_one',
                'role' => 'member',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'invitee_id' => $invitee->id,
            'status' => 'pending',
        ]);

        // Manager CANNOT change member roles -> 403
        $this->actingAs($manager)
            ->post(route('teams.members.update-role', [$team, $memberMembership]), ['role' => 'viewer'])
            ->assertStatus(403);

        // Manager CANNOT remove members -> 403
        $this->actingAs($manager)
            ->delete(route('teams.members.destroy', [$team, $memberMembership]))
            ->assertStatus(403);
    }

    public function test_member_and_viewer_have_read_only_management_access(): void
    {
        $owner = $this->createOnboardedUser();
        $member = $this->createOnboardedUser();
        $viewer = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $this->addMemberToTeam($team, $owner, 'owner');
        $memberMembership = $this->addMemberToTeam($team, $member, 'member');
        $this->addMemberToTeam($team, $viewer, 'viewer');

        // Member cannot invite -> 403
        $this->actingAs($member)
            ->post(route('teams.invitations.store', $team), ['username' => 'test_user'])
            ->assertStatus(403);

        // Viewer cannot invite -> 403
        $this->actingAs($viewer)
            ->post(route('teams.invitations.store', $team), ['username' => 'test_user'])
            ->assertStatus(403);

        // Member cannot remove member -> 403
        $this->actingAs($member)
            ->delete(route('teams.members.destroy', [$team, $memberMembership]))
            ->assertStatus(403);
    }

    public function test_last_owner_cannot_be_removed(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $ownerMembership = $this->addMemberToTeam($team, $owner, 'owner');

        // Attempting to remove the last owner returns 403
        $this->actingAs($owner)
            ->delete(route('teams.members.destroy', [$team, $ownerMembership]))
            ->assertStatus(403);

        $this->assertDatabaseHas('team_members', [
            'id' => $ownerMembership->id,
        ]);
    }
}
