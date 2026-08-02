<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function createOnboardedUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'onboarded_at' => now(),
            'username' => 'user_' . strtolower(Str::random(8)),
            'account_type' => 'client',
        ], $attributes));
    }

    public function test_owner_can_create_project_with_enterprise_attributes(): void
    {
        $owner = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('projects.store'), [
            'team_id' => $team->id,
            'title' => 'Enterprise System Redesign',
            'description' => 'A complete architectural rebuild.',
            'category' => 'Web Development',
            'visibility' => 'private',
            'budget' => 15000.00,
            'budget_type' => 'fixed',
            'currency' => 'USD',
            'start_date' => now()->toDateString(),
            'due_date' => now()->addDays(60)->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'owner_id' => $owner->id,
            'team_id' => $team->id,
            'title' => 'Enterprise System Redesign',
            'budget' => 15000.00,
            'currency' => 'USD',
            'status' => 'draft',
        ]);

        $project = Project::where('title', 'Enterprise System Redesign')->first();
        $this->assertNotNull($project->slug);
        $this->assertTrue(Str::startsWith($project->slug, 'enterprise-system-redesign'));
        $this->assertFalse($project->isArchived());
    }

    public function test_slug_uniqueness_is_enforced_automatically(): void
    {
        $owner = $this->createOnboardedUser();

        $project1 = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Mobile Application',
            'description' => 'First project',
        ]);

        $project2 = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Mobile Application',
            'description' => 'Second project',
        ]);

        $this->assertNotEquals($project1->slug, $project2->slug);
        $this->assertTrue(Str::startsWith($project1->slug, 'mobile-application'));
        $this->assertTrue(Str::startsWith($project2->slug, 'mobile-application'));
    }

    public function test_owner_can_update_project(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'title' => 'Old Title',
            'budget' => 5000.00,
        ]);

        $response = $this->actingAs($owner)->patch(route('projects.update', $project), [
            'title' => 'Updated Project Title',
            'budget' => 7500.00,
            'description' => $project->description,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'title' => 'Updated Project Title',
            'budget' => 7500.00,
        ]);
    }

    public function test_owner_can_archive_and_restore_project(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        $this->assertFalse($project->isArchived());

        // Archive
        $archiveResponse = $this->actingAs($owner)->post(route('projects.archive', $project));
        $archiveResponse->assertRedirect();

        $project->refresh();
        $this->assertTrue($project->isArchived());

        // Restore
        $restoreResponse = $this->actingAs($owner)->post(route('projects.restore', $project));
        $restoreResponse->assertRedirect();

        $project->refresh();
        $this->assertFalse($project->isArchived());
    }

    public function test_status_transitions_update_timestamps_correctly(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'draft',
        ]);

        // Transition draft -> open
        $this->actingAs($owner)
            ->post(route('projects.change-status', $project), ['status' => 'open'])
            ->assertRedirect();

        $project->refresh();
        $this->assertEquals('open', $project->status);
        $this->assertNotNull($project->published_at);

        // Transition open -> completed
        $this->actingAs($owner)
            ->post(route('projects.change-status', $project), ['status' => 'completed'])
            ->assertRedirect();

        $project->refresh();
        $this->assertEquals('completed', $project->status);
        $this->assertNotNull($project->completed_at);
    }

    public function test_unauthorized_user_cannot_modify_archive_or_delete_project(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
        ]);

        // Stranger cannot update -> 403
        $this->actingAs($stranger)
            ->patch(route('projects.update', $project), ['title' => 'Hacked Title'])
            ->assertStatus(403);

        // Stranger cannot archive -> 403
        $this->actingAs($stranger)
            ->post(route('projects.archive', $project))
            ->assertStatus(403);

        // Stranger cannot restore -> 403
        $this->actingAs($stranger)
            ->post(route('projects.restore', $project))
            ->assertStatus(403);

        // Stranger cannot delete -> 403
        $this->actingAs($stranger)
            ->delete(route('projects.destroy', $project))
            ->assertStatus(403);
    }
}
