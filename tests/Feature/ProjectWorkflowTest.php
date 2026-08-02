<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectWorkflowTest extends TestCase
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

    public function test_valid_workflow_transitions_pass_sequentially(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'draft',
        ]);

        // 1. Draft -> Open
        $this->actingAs($owner)
            ->post(route('projects.change-status', $project), ['status' => 'open'])
            ->assertRedirect();
        $this->assertEquals('open', $project->fresh()->status);
        $this->assertNotNull($project->fresh()->published_at);

        // 2. Open -> In Progress
        $this->actingAs($owner)
            ->post(route('projects.change-status', $project), ['status' => 'in_progress'])
            ->assertRedirect();
        $this->assertEquals('in_progress', $project->fresh()->status);

        // 3. In Progress -> Review
        $this->actingAs($owner)
            ->post(route('projects.change-status', $project), ['status' => 'review'])
            ->assertRedirect();
        $this->assertEquals('review', $project->fresh()->status);

        // 4. Review -> Completed
        $this->actingAs($owner)
            ->post(route('projects.change-status', $project), ['status' => 'completed'])
            ->assertRedirect();
        $this->assertEquals('completed', $project->fresh()->status);
        $this->assertNotNull($project->fresh()->completed_at);
        $this->assertEquals(100, $project->fresh()->progress());
    }

    public function test_invalid_workflow_transition_is_rejected(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'draft',
        ]);

        // Attempting Draft -> Completed is invalid
        $response = $this->actingAs($owner)->post(route('projects.change-status', $project), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('status');
        $this->assertEquals('draft', $project->fresh()->status);
    }

    public function test_archive_and_restore_workflow(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'in_progress',
        ]);

        // Archive
        $this->actingAs($owner)
            ->post(route('projects.archive', $project))
            ->assertRedirect();

        $project->refresh();
        $this->assertTrue($project->isArchived());
        $this->assertEquals('archived', $project->status);

        // Restore
        $this->actingAs($owner)
            ->post(route('projects.restore', $project))
            ->assertRedirect();

        $project->refresh();
        $this->assertFalse($project->isArchived());
        $this->assertNotEquals('archived', $project->status);
    }

    public function test_timeline_engine_calculations_and_late_status(): void
    {
        $owner = $this->createOnboardedUser();

        // Project with 10 days duration and 5 days remaining
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'in_progress',
            'start_date' => now()->subDays(5)->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
        ]);

        $this->assertFalse($project->isLate());
        $this->assertEquals(5, $project->remainingDays());
        $this->assertEquals(10, $project->durationDays());
        $this->assertEquals(50, $project->progress());

        // Past due project
        $lateProject = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'in_progress',
            'start_date' => now()->subDays(20)->toDateString(),
            'due_date' => now()->subDays(2)->toDateString(),
        ]);

        $this->assertTrue($lateProject->isLate());
        $this->assertEquals(0, $lateProject->remainingDays());
    }

    public function test_unauthorized_user_cannot_trigger_workflow_actions(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'draft',
        ]);

        // Stranger status change -> 403
        $this->actingAs($stranger)
            ->post(route('projects.change-status', $project), ['status' => 'open'])
            ->assertStatus(403);

        // Stranger archive -> 403
        $this->actingAs($stranger)
            ->post(route('projects.archive', $project))
            ->assertStatus(403);

        // Stranger restore -> 403
        $this->actingAs($stranger)
            ->post(route('projects.restore', $project))
            ->assertStatus(403);
    }
}
