<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_teams(): void
    {
        $response = $this->get(route('teams.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_list_their_teams(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $ownedTeam = $this->createTeam($owner, ['name' => 'Alpha Team']);
        $otherTeam = $this->createTeam($otherUser, ['name' => 'Beta Team']);

        $response = $this->actingAs($owner)->get(route('teams.index'));

        $response->assertOk();
        $response->assertSee($ownedTeam->name);
        $response->assertDontSee($otherTeam->name);
    }

    public function test_user_can_create_valid_team(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->post(route('teams.store'), [
            'name' => 'Launch Team',
            'description' => 'Handles releases.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('teams', [
            'owner_id' => $owner->id,
            'name' => 'Launch Team',
            'description' => 'Handles releases.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);
    }

    public function test_invalid_team_data_is_rejected(): void
    {
        $owner = User::factory()->create();

        $response = $this->actingAs($owner)->from(route('teams.create'))->post(route('teams.store'), [
            'name' => '',
            'type' => 'invalid',
            'visibility' => 'private',
        ]);

        $response->assertRedirect(route('teams.create'));
        $response->assertSessionHasErrors(['name', 'type']);
        $this->assertDatabaseMissing('teams', [
            'owner_id' => $owner->id,
        ]);
    }

    public function test_owner_can_view_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Ops Team']);

        $response = $this->actingAs($owner)->get(route('teams.show', $team));

        $response->assertOk();
        $response->assertSee($team->name);
    }

    public function test_non_owner_cannot_view_private_team_page(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Ops Team']);

        $response = $this->actingAs($otherUser)->get(route('teams.show', $team));

        $response->assertForbidden();
    }

    public function test_owner_can_edit_and_update_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Old Team']);

        $editResponse = $this->actingAs($owner)->get(route('teams.edit', $team));
        $editResponse->assertOk();

        $updateResponse = $this->actingAs($owner)->patch(route('teams.update', $team), [
            'name' => 'Updated Team',
            'description' => 'Refined.',
            'type' => 'project_based',
            'visibility' => 'public',
        ]);

        $updateResponse->assertRedirect(route('teams.show', $team));
        $team->refresh();

        $this->assertSame('Updated Team', $team->name);
        $this->assertSame('Refined.', $team->description);
        $this->assertSame('project_based', $team->type);
        $this->assertSame('public', $team->visibility);
        $this->assertNotSame('old-team', $team->slug);
    }

    public function test_non_owner_cannot_update_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Visible Team']);

        $response = $this->actingAs($otherUser)->patch(route('teams.update', $team), [
            'name' => 'Changed Name',
        ]);

        $response->assertForbidden();
        $team->refresh();
        $this->assertSame('Visible Team', $team->name);
    }

    public function test_owner_can_delete_team(): void
    {
        $owner = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Delete Me']);

        $response = $this->actingAs($owner)->delete(route('teams.destroy', $team));

        $response->assertRedirect(route('teams.index'));
        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_non_owner_cannot_delete_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $team = $this->createTeam($owner, ['name' => 'Protected Team']);

        $response = $this->actingAs($otherUser)->delete(route('teams.destroy', $team));

        $response->assertForbidden();
        $this->assertDatabaseHas('teams', ['id' => $team->id]);
    }

    public function test_slug_is_generated_and_changes_when_name_changes(): void
    {
        $owner = User::factory()->create();

        $team = $this->createTeam($owner, ['name' => 'Finance Team']);
        $this->assertMatchesRegularExpression('/^finance-team-[a-z]{6}$/', $team->slug);

        $team->update(['name' => 'Operations Team']);
        $team->refresh();
        $this->assertMatchesRegularExpression('/^operations-team-[a-z]{6}$/', $team->slug);
    }

    private function createTeam(User $owner, array $attributes = []): Team
    {
        $data = [
            'owner_id' => $owner->id,
            'name' => $attributes['name'] ?? 'Sample Team',
            'description' => $attributes['description'] ?? null,
            'type' => $attributes['type'] ?? 'permanent',
            'visibility' => $attributes['visibility'] ?? 'private',
        ];

        if (array_key_exists('slug', $attributes)) {
            $data['slug'] = $attributes['slug'];
        }

        return Team::create($data);
    }
}
