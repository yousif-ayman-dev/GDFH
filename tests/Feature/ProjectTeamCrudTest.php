<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTeamCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_attach_team_to_project(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);

        $response = $this->actingAs($owner)->post(route('projects.teams.store', $project), [
            'team_id' => $team->id,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseHas('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_duplicate_project_team_association_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);
        $project->teams()->attach($team->id, ['is_primary' => false, 'joined_at' => now()]);

        $response = $this->actingAs($owner)->from(route('projects.show', $project))->post(route('projects.teams.store', $project), [
            'team_id' => $team->id,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('team_id');
    }

    public function test_owner_can_detach_team_from_project(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);
        $project->teams()->attach($team->id, ['is_primary' => false, 'joined_at' => now()]);

        $response = $this->actingAs($owner)->delete(route('projects.teams.destroy', [$project, $team]));

        $response->assertRedirect(route('projects.show', $project));
        $this->assertDatabaseMissing('project_team', [
            'project_id' => $project->id,
            'team_id' => $team->id,
        ]);
    }

    public function test_unauthorized_user_cannot_manage_project_team(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);

        $response = $this->actingAs($otherUser)->post(route('projects.teams.store', $project), [
            'team_id' => $team->id,
        ]);

        $response->assertForbidden();
    }

    public function test_unrelated_team_cannot_be_manipulated_through_project(): void
    {
        $owner = User::factory()->create();
        $otherOwner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($otherOwner);

        $response = $this->actingAs($owner)->post(route('projects.teams.store', $project), [
            'team_id' => $team->id,
        ]);

        $response->assertRedirect(route('projects.show', $project));
        $response->assertSessionHasErrors('team_id');
    }

    private function createProject(User $owner): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => 'Project Team Project',
            'slug' => 'project-team-project-' . uniqid(),
            'description' => 'Project used for team tests.',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);
    }

    private function createTeam(User $owner): Team
    {
        return Team::create([
            'owner_id' => $owner->id,
            'name' => 'Project Team',
            'slug' => 'project-team-' . uniqid(),
            'description' => 'Team used for project tests.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);
    }
}
