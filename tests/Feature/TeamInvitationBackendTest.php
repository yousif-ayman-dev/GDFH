<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamInvitationBackendTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(\Illuminate\Support\Str::random(8)),
            'account_type' => 'freelancer',
        ], $attributes));
    }

    public function test_team_owner_can_create_team_invitation(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'invitee_id' => $invitee->id,
            'role' => 'member',
            'message' => 'Please join our team!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
            'role' => 'member',
            'status' => 'pending',
            'message' => 'Please join our team!',
        ]);
    }

    public function test_inviter_cannot_invite_self(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'invitee_id' => $owner->id,
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('invitee_id');
        $this->assertDatabaseCount('team_invitations', 0);
    }

    public function test_cannot_invite_existing_team_member(): void
    {
        $owner = $this->createOnboardedUser();
        $existingMemberUser = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $existingMemberUser->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'invitee_id' => $existingMemberUser->id,
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('invitee_id');
        $this->assertDatabaseCount('team_invitations', 0);
    }

    public function test_cannot_create_duplicate_pending_invitation(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
        ]);

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'invitee_id' => $invitee->id,
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('invitee_id');
        $this->assertDatabaseCount('team_invitations', 1);
    }

    public function test_invitee_can_accept_invitation(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
            'role' => 'admin',
        ]);

        $response = $this->actingAs($invitee)->post(route('invitations.accept', $invitation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => 'accepted',
        ]);

        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'role' => 'manager',
            'status' => 'active',
            'invited_by' => $owner->id,
        ]);
    }

    public function test_accept_invitation_prevents_duplicate_team_member_record(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        // Inactive existing membership
        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'role' => 'member',
            'status' => 'suspended',
            'joined_at' => now(),
        ]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
            'role' => 'member',
        ]);

        $response = $this->actingAs($invitee)->post(route('invitations.accept', $invitation));

        $response->assertRedirect();

        // Must still be 1 single team_members record, now active
        $this->assertDatabaseCount('team_members', 1);
        $this->assertDatabaseHas('team_members', [
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'role' => 'member',
            'status' => 'active',
        ]);
    }

    public function test_invitee_can_reject_invitation(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
        ]);

        $response = $this->actingAs($invitee)->post(route('invitations.reject', $invitation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseCount('team_members', 0);
    }

    public function test_inviter_or_team_owner_can_cancel_invitation(): void
    {
        $owner = $this->createOnboardedUser();
        $inviter = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
        ]);

        // Inviter can cancel
        $response = $this->actingAs($inviter)->post(route('invitations.cancel', $invitation));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_unauthorized_user_cannot_manage_invitations(): void
    {
        $owner = $this->createOnboardedUser();
        $inviter = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
        ]);

        // Stranger cannot create invitation for team
        $this->actingAs($stranger)
            ->post(route('teams.invitations.store', $team), [
                'invitee_id' => $invitee->id,
            ])
            ->assertStatus(403);

        // Stranger cannot accept invitation
        $this->actingAs($stranger)
            ->post(route('invitations.accept', $invitation))
            ->assertStatus(403);

        // Stranger cannot reject invitation
        $this->actingAs($stranger)
            ->post(route('invitations.reject', $invitation))
            ->assertStatus(403);

        // Stranger cannot cancel invitation
        $this->actingAs($stranger)
            ->post(route('invitations.cancel', $invitation))
            ->assertStatus(403);
    }

    public function test_cannot_accept_reject_or_cancel_expired_invitation(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $expiredInvitation = TeamInvitation::factory()->expired()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
        ]);

        $this->actingAs($invitee)
            ->post(route('invitations.accept', $expiredInvitation))
            ->assertStatus(403);

        $this->actingAs($invitee)
            ->post(route('invitations.reject', $expiredInvitation))
            ->assertStatus(403);

        $this->actingAs($owner)
            ->post(route('invitations.cancel', $expiredInvitation))
            ->assertStatus(403);
    }
}
