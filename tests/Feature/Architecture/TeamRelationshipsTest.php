<?php

namespace Tests\Feature\Architecture;

use App\Models\Project;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_own_a_team(): void
    {
        $owner = User::factory()->create();

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Development Team',
            'slug' => 'development-team',
            'description' => 'Main development team',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);

        $this->assertTrue(
            $team->owner->is($owner)
        );

        $this->assertTrue(
            $owner->ownedTeams->contains($team)
        );
    }

    public function test_user_can_be_a_team_member(): void
    {
        $owner = User::factory()->create();
        $member = User::factory()->create();

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Backend Team',
            'slug' => 'backend-team',
            'description' => 'Backend development team',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);

        $membership = TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->assertTrue(
            $team->members->contains($member)
        );

        $this->assertTrue(
            $member->teams->contains($team)
        );

        $this->assertTrue(
            $membership->team->is($team)
        );

        $this->assertTrue(
            $membership->user->is($member)
        );
    }

    public function test_team_can_be_attached_to_a_project(): void
    {
        $owner = User::factory()->create();

        $project = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Platform Project',
            'slug' => 'platform-project',
            'description' => 'Test project',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);

        $team = Team::create([
            'owner_id' => $owner->id,
            'name' => 'Project Team',
            'slug' => 'project-team',
            'description' => 'Team assigned to project',
            'type' => 'project_based',
            'visibility' => 'private',
        ]);

        $project->teams()->attach($team->id, [
            'is_primary' => true,
            'joined_at' => now(),
        ]);

        $this->assertTrue(
            $project->teams->contains($team)
        );

        $this->assertTrue(
            $team->projects->contains($project)
        );

        $this->assertTrue(
            (bool) $project->teams->first()->pivot->is_primary
        );
    }
}
