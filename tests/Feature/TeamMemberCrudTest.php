<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamMemberCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_add_member_to_team(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->createTeam($owner);

        $response = $this->actingAs($owner)->post(route('teams.members.store', $team), [
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('teams.show', $team));

        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
            'invited_by' => $owner->id,
        ]);
    }

    public function test_duplicate_member_cannot_be_added(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->createTeam($owner);
        $this->createMembership($team, $member, $owner);

        $response = $this->actingAs($owner)->from(route('teams.show', $team))->post(route('teams.members.store', $team), [
            'user_id' => $member->id,
            'role' => 'manager',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('teams.show', $team));
        $response->assertSessionHasErrors('user_id');
    }

    public function test_owner_cannot_be_added_as_conflicting_member(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner);

        $response = $this->actingAs($owner)->from(route('teams.show', $team))->post(route('teams.members.store', $team), [
            'user_id' => $owner->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('teams.show', $team));
        $response->assertSessionHasErrors('user_id');
    }

    public function test_member_role_and_status_can_be_updated(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->createTeam($owner);
        $membership = $this->createMembership($team, $member, $owner);

        $response = $this->actingAs($owner)->patch(route('teams.members.update', [$team, $membership]), [
            'role' => 'manager',
            'status' => 'suspended',
        ]);

        $response->assertRedirect(route('teams.show', $team));
        $membership->refresh();
        $this->assertSame('manager', $membership->role);
        $this->assertSame('suspended', $membership->status);
    }

    public function test_membership_from_another_team_cannot_be_modified_through_wrong_team(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->createTeam($owner);
        $otherTeam = $this->createTeam($otherOwner);
        $membership = $this->createMembership($otherTeam, $member, $otherOwner);

        $response = $this->actingAs($owner)->patch(route('teams.members.update', [$team, $membership]), [
            'role' => 'manager',
        ]);

        $response->assertNotFound();
        $membership->refresh();
        $this->assertSame('member', $membership->role);
    }

    public function test_member_can_be_removed(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->createTeam($owner);
        $membership = $this->createMembership($team, $member, $owner);

        $response = $this->actingAs($owner)->delete(route('teams.members.destroy', [$team, $membership]));

        $response->assertRedirect(route('teams.show', $team));
        $this->assertDatabaseMissing('team_members', ['id' => $membership->id]);
    }

    public function test_non_owner_cannot_manage_team_members(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $member = User::factory()->create();
        $team = $this->createTeam($owner);
        $membership = $this->createMembership($team, $member, $owner);

        $response = $this->actingAs($otherUser)->post(route('teams.members.store', $team), [
            'user_id' => User::factory()->create()->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $response->assertForbidden();

        $updateResponse = $this->actingAs($otherUser)->patch(route('teams.members.update', [$team, $membership]), [
            'role' => 'manager',
        ]);

        $updateResponse->assertForbidden();

        $deleteResponse = $this->actingAs($otherUser)->delete(route('teams.members.destroy', [$team, $membership]));
        $deleteResponse->assertForbidden();
    }

    private function createTeam(User $owner): Team
    {
        return Team::create([
            'owner_id' => $owner->id,
            'name' => 'Sample Team',
            'slug' => 'sample-team-' . uniqid(),
            'description' => 'Team for testing.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);
    }

    private function createMembership(Team $team, User $user, User $inviter): TeamMember
    {
        return TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
            'invited_by' => $inviter->id,
        ]);
    }
}
