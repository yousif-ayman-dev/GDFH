<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TeamInvitationUserFlowTest extends TestCase
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

    public function test_invite_by_username_succeeds(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser(['username' => 'yousif_dev']);
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'username' => 'yousif_dev',
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
            'status' => 'pending',
        ]);
    }

    public function test_username_normalization_strips_at_symbol_trims_and_ignores_case(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser(['username' => 'yousif_dev']);
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        // Form submits with "@Yousif_Dev "
        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'username' => '  @Yousif_Dev  ',
            'role' => 'admin',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('team_invitations', [
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
            'role' => 'admin',
        ]);
    }

    public function test_invalid_username_returns_validation_error(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'username' => 'non_existent_username_9999',
            'role' => 'member',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
        $this->assertDatabaseCount('team_invitations', 0);
    }

    public function test_invitation_center_lists_received_invitations(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id, 'name' => 'Design Masters']);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
        ]);

        $response = $this->actingAs($invitee)->get(route('invitations.index'));

        $response->assertStatus(200);
        $response->assertSee('Design Masters');
        $response->assertSee($owner->name);
        $response->assertSee('قبول الدعوة');
    }

    public function test_pending_invitation_can_be_accepted_from_user_flow(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
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
            'status' => 'active',
        ]);
    }

    public function test_owner_can_view_pending_invitations_on_team_page_and_cancel(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser(['username' => 'freelancer_pro']);
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $invitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
        ]);

        // Team show page displays pending invitation
        $showResponse = $this->actingAs($owner)->get(route('teams.show', $team));
        $showResponse->assertStatus(200);
        $showResponse->assertSee('freelancer_pro');
        $showResponse->assertSee('الدعوات المعلقة');

        // Owner cancels invitation
        $cancelResponse = $this->actingAs($owner)->post(route('invitations.cancel', $invitation));
        $cancelResponse->assertRedirect();

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_duplicate_invitation_ui_flow_returns_session_error(): void
    {
        $owner = $this->createOnboardedUser();
        $invitee = $this->createOnboardedUser(['username' => 'sam_dev']);
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $owner->id,
            'invitee_id' => $invitee->id,
        ]);

        // Second invitation attempt for same username
        $response = $this->actingAs($owner)->post(route('teams.invitations.store', $team), [
            'username' => 'sam_dev',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('username');
    }
}
