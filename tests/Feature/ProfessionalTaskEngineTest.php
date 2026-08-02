<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfessionalTaskEngineTest extends TestCase
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

    public function test_owner_can_create_task_assigned_to_project_member(): void
    {
        $owner = $this->createOnboardedUser();
        $member = $this->createOnboardedUser();
        $team = Team::factory()->create(['owner_id' => $owner->id]);

        TeamMember::create([
            'team_id' => $team->id,
            'user_id' => $member->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'team_id' => $team->id,
        ]);

        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Implement API Endpoint',
            'description' => 'Build REST endpoint for tasks.',
            'assigned_to' => $member->id,
            'priority' => 'high',
            'due_at' => now()->addDays(5)->toDateTimeString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'assigned_to' => $member->id,
            'title' => 'Implement API Endpoint',
            'priority' => 'high',
            'status' => 'todo',
        ]);
    }

    public function test_assigning_user_outside_project_team_fails_validation(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);

        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Task for Stranger',
            'assigned_to' => $stranger->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('assigned_to');
        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_task_workflow_transitions_and_invalid_transition_rejection(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'status' => 'todo',
        ]);

        // 1. Valid: todo -> in_progress
        $this->actingAs($owner)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'in_progress'])
            ->assertRedirect();
        $this->assertEquals('in_progress', $task->fresh()->status);

        // 2. Valid: in_progress -> review
        $this->actingAs($owner)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'review'])
            ->assertRedirect();
        $this->assertEquals('review', $task->fresh()->status);

        // 3. Valid: review -> completed
        $this->actingAs($owner)
            ->patch(route('projects.tasks.update', [$project, $task]), ['status' => 'completed'])
            ->assertRedirect();
        $this->assertEquals('completed', $task->fresh()->status);
        $this->assertNotNull($task->fresh()->completed_at);
    }

    public function test_invalid_task_transition_returns_session_error(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'status' => 'todo',
        ]);

        // Invalid: todo -> completed direct jump
        $response = $this->actingAs($owner)->patch(route('projects.tasks.update', [$project, $task]), [
            'status' => 'completed',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('status');
        $this->assertEquals('todo', $task->fresh()->status);
    }

    public function test_project_progress_calculates_automatically_from_tasks(): void
    {
        $owner = $this->createOnboardedUser();
        $project = Project::factory()->create([
            'owner_id' => $owner->id,
            'status' => 'in_progress', // Status would give 50%, but task progress overrides it
        ]);

        // Create 4 tasks: 2 completed, 2 in progress
        Task::factory()->count(2)->completed()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
        ]);
        Task::factory()->count(2)->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'status' => 'in_progress',
        ]);

        $project->refresh();
        // 2 out of 4 completed tasks = 50%
        $this->assertEquals(50, $project->progress());
    }

    public function test_unauthorized_user_cannot_manage_tasks(): void
    {
        $owner = $this->createOnboardedUser();
        $stranger = $this->createOnboardedUser();
        $project = Project::factory()->create(['owner_id' => $owner->id]);
        $task = Task::factory()->create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
        ]);

        // Stranger cannot update task -> 403
        $this->actingAs($stranger)
            ->patch(route('projects.tasks.update', [$project, $task]), ['title' => 'Hacked Task'])
            ->assertStatus(403);

        // Stranger cannot delete task -> 403
        $this->actingAs($stranger)
            ->delete(route('projects.tasks.destroy', [$project, $task]))
            ->assertStatus(403);
    }
}
