<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamInvitationTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_invitation_can_be_created_with_default_token_and_expiration(): void
    {
        $team = Team::factory()->create();
        $inviter = User::factory()->create();
        $invitee = User::factory()->create();

        $invitation = TeamInvitation::create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
            'role' => 'member',
            'message' => 'Join my team!',
        ]);

        $this->assertDatabaseHas('team_invitations', [
            'id' => $invitation->id,
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
            'role' => 'member',
            'status' => 'pending',
        ]);

        $this->assertNotEmpty($invitation->token);
        $this->assertNotNull($invitation->expires_at);
        $this->assertTrue($invitation->isPending());
        $this->assertFalse($invitation->isExpired());
    }

    public function test_duplicate_pending_invitation_for_same_team_and_user_is_prevented(): void
    {
        $team = Team::factory()->create();
        $inviter = User::factory()->create();
        $invitee = User::factory()->create();

        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
            'status' => 'pending',
        ]);

        $this->assertTrue(TeamInvitation::hasPendingInvitation($team->id, $invitee->id));

        $this->expectException(QueryException::class);

        TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
            'status' => 'pending',
        ]);
    }

    public function test_reinvitation_allowed_if_previous_invitation_was_rejected_or_cancelled(): void
    {
        $team = Team::factory()->create();
        $inviter = User::factory()->create();
        $invitee = User::factory()->create();

        TeamInvitation::factory()->rejected()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
        ]);

        $this->assertFalse(TeamInvitation::hasPendingInvitation($team->id, $invitee->id));

        $newInvitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
        ]);

        $this->assertDatabaseHas('team_invitations', [
            'id' => $newInvitation->id,
            'status' => 'pending',
        ]);
    }

    public function test_expiration_handling_and_pending_scope(): void
    {
        $team = Team::factory()->create();

        $activeInvitation = TeamInvitation::factory()->pending()->create([
            'team_id' => $team->id,
            'expires_at' => now()->addDays(3),
        ]);

        $expiredInvitation = TeamInvitation::factory()->expired()->create([
            'team_id' => $team->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($activeInvitation->isExpired());
        $this->assertTrue($activeInvitation->isPending());

        $this->assertTrue($expiredInvitation->isExpired());
        $this->assertFalse($expiredInvitation->isPending());

        $pendingInvitations = TeamInvitation::pending()->get();

        $this->assertTrue($pendingInvitations->contains($activeInvitation));
        $this->assertFalse($pendingInvitations->contains($expiredInvitation));
    }

    public function test_eloquent_relationships(): void
    {
        $team = Team::factory()->create();
        $inviter = User::factory()->create();
        $invitee = User::factory()->create();

        $invitation = TeamInvitation::factory()->create([
            'team_id' => $team->id,
            'inviter_id' => $inviter->id,
            'invitee_id' => $invitee->id,
        ]);

        // BelongsTo relationships
        $this->assertTrue($invitation->team->is($team));
        $this->assertTrue($invitation->inviter->is($inviter));
        $this->assertTrue($invitation->invitee->is($invitee));

        // HasMany relationships
        $this->assertTrue($team->invitations->contains($invitation));
        $this->assertTrue($inviter->sentTeamInvitations->contains($invitation));
        $this->assertTrue($invitee->receivedTeamInvitations->contains($invitation));
    }
}
