<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_tasks(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this->get(route('projects.tasks.index', $project));

        $response->assertRedirect(route('login'));
    }

    public function test_owner_can_create_task(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Build API',
            'description' => 'Create endpoints',
            'status' => 'todo',
            'priority' => 'high',
        ]);

        $response->assertRedirect(route('projects.tasks.show', [$project, Task::latest()->first()]));
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'title' => 'Build API',
            'status' => 'todo',
            'priority' => 'high',
        ]);
    }

    public function test_invalid_task_data_is_rejected(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);

        $response = $this->actingAs($owner)->from(route('projects.tasks.create', $project))->post(route('projects.tasks.store', $project), [
            'title' => '',
            'status' => 'invalid',
        ]);

        $response->assertRedirect(route('projects.tasks.create', $project));
        $response->assertSessionHasErrors(['title', 'status']);
    }

    public function test_owner_can_update_task(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $task = $this->createTask($project, $owner);

        $response = $this->actingAs($owner)->patch(route('projects.tasks.update', [$project, $task]), [
            'title' => 'Refined API',
            'status' => 'in_progress',
            'priority' => 'urgent',
        ]);

        $response->assertRedirect(route('projects.tasks.show', [$project, $task]));
        $task->refresh();
        $this->assertSame('Refined API', $task->title);
        $this->assertSame('in_progress', $task->status);
        $this->assertSame('urgent', $task->priority);
    }

    public function test_unauthorized_user_cannot_manage_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $project = $this->createProject($owner);
        $task = $this->createTask($project, $owner);

        $response = $this->actingAs($otherUser)->patch(route('projects.tasks.update', [$project, $task]), [
            'title' => 'Hacked',
        ]);

        $response->assertForbidden();
        $task->refresh();
        $this->assertNotSame('Hacked', $task->title);
    }

    public function test_task_cannot_be_manipulated_through_wrong_project(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $otherProject = $this->createProject($owner, 'Other Project');
        $task = $this->createTask($project, $owner);

        $response = $this->actingAs($owner)->patch(route('projects.tasks.update', [$otherProject, $task]), [
            'title' => 'Wrong',
        ]);

        $response->assertNotFound();
        $task->refresh();
        $this->assertNotSame('Wrong', $task->title);
    }

    public function test_task_parent_must_belong_to_same_project(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $otherProject = $this->createProject($owner, 'Other Project');
        $otherParent = $this->createTask($otherProject, $owner, 'Other Parent');

        $response = $this->actingAs($owner)->from(route('projects.tasks.create', $project))->post(route('projects.tasks.store', $project), [
            'title' => 'Child Task',
            'parent_id' => $otherParent->id,
            'status' => 'todo',
            'priority' => 'medium',
        ]);

        $response->assertRedirect(route('projects.tasks.create', $project));
        $response->assertSessionHasErrors('parent_id');
    }

    public function test_owner_can_delete_task(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $task = $this->createTask($project, $owner);

        $response = $this->actingAs($owner)->delete(route('projects.tasks.destroy', [$project, $task]));

        $response->assertRedirect(route('projects.tasks.index', $project));
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }

    public function test_task_can_be_created_with_team_linked_to_project(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);
        $project->teams()->attach($team->id, ['is_primary' => false, 'joined_at' => now()]);

        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Team Task',
            'status' => 'todo',
            'priority' => 'medium',
            'team_id' => $team->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'team_id' => $team->id,
            'title' => 'Team Task',
        ]);
    }

    public function test_assignee_must_be_team_member_when_team_is_selected(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);
        $project->teams()->attach($team->id, ['is_primary' => false, 'joined_at' => now()]);
        $member = User::factory()->create();

        $response = $this->actingAs($owner)->from(route('projects.tasks.create', $project))->post(route('projects.tasks.store', $project), [
            'title' => 'Invalid Assignment',
            'status' => 'todo',
            'priority' => 'medium',
            'team_id' => $team->id,
            'assigned_to' => $member->id,
        ]);

        $response->assertRedirect(route('projects.tasks.create', $project));
        $response->assertSessionHasErrors('assigned_to');
    }

    public function test_team_must_belong_to_project_for_task_creation(): void
    {
        $owner = User::factory()->create();
        $project = $this->createProject($owner);
        $team = $this->createTeam($owner);

        $response = $this->actingAs($owner)->from(route('projects.tasks.create', $project))->post(route('projects.tasks.store', $project), [
            'title' => 'Wrong Team Task',
            'status' => 'todo',
            'priority' => 'medium',
            'team_id' => $team->id,
        ]);

        $response->assertRedirect(route('projects.tasks.create', $project));
        $response->assertSessionHasErrors('team_id');
    }

    public function test_task_dates_must_be_valid_and_within_project_bounds(): void
    {
        $owner = User::factory()->create(['onboarded_at' => now(), 'username' => 'owner_user', 'account_type' => 'client']);
        $project = Project::create([
            'owner_id' => $owner->id,
            'title' => 'Project With Date Bounds',
            'slug' => 'project-dates-' . uniqid(),
            'description' => 'Description',
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'deadline' => now()->addDays(10)->format('Y-m-d'),
            'visibility' => 'private',
            'status' => 'draft',
        ]);

        // Task start date before project start date should fail validation
        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Invalid Start Task',
            'start_at' => now()->addDay()->format('Y-m-d'), // Before project start (now + 2 days)
            'due_at' => now()->addDays(5)->format('Y-m-d'),
        ]);
        $response->assertSessionHasErrors(['start_at']);

        // Task due date after project deadline should fail validation
        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Invalid Due Task',
            'start_at' => now()->addDays(3)->format('Y-m-d'),
            'due_at' => now()->addDays(15)->format('Y-m-d'), // After project deadline (now + 10 days)
        ]);
        $response->assertSessionHasErrors(['due_at']);

        // Valid task within bounds should succeed
        $response = $this->actingAs($owner)->post(route('projects.tasks.store', $project), [
            'title' => 'Valid Task',
            'start_at' => now()->addDays(3)->format('Y-m-d'),
            'due_at' => now()->addDays(8)->format('Y-m-d'),
        ]);
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tasks', ['title' => 'Valid Task', 'project_id' => $project->id]);
    }

    public function test_historical_task_dates_are_preserved(): void
    {
        $owner = User::factory()->create(['onboarded_at' => now(), 'username' => 'historical_owner', 'account_type' => 'client']);
        $project = $this->createProject($owner);

        // Historical task created in the past
        $task = Task::create([
            'project_id' => $project->id,
            'created_by' => $owner->id,
            'title' => 'Historical Task',
            'start_at' => now()->subDays(10)->format('Y-m-d'),
            'due_at' => now()->subDays(2)->format('Y-m-d'),
            'status' => 'completed',
        ]);

        // Updating title without changing past dates should succeed
        $response = $this->actingAs($owner)->put(route('projects.tasks.update', [$project, $task]), [
            'title' => 'Updated Historical Task Title',
        ]);

        $response->assertSessionHasNoErrors();
        $task->refresh();
        $this->assertSame('Updated Historical Task Title', $task->title);
    }

    private function createProject(User $owner, string $title = 'Test Project'): Project
    {
        return Project::create([
            'owner_id' => $owner->id,
            'title' => $title,
            'slug' => strtolower(str_replace(' ', '-', $title)) . '-' . uniqid(),
            'description' => 'Project used for task tests.',
            'visibility' => 'private',
            'status' => 'draft',
            'currency' => 'USD',
        ]);
    }

    private function createTask(Project $project, User $user, string $title = 'Initial Task'): Task
    {
        return Task::create([
            'project_id' => $project->id,
            'created_by' => $user->id,
            'assigned_to' => $user->id,
            'title' => $title,
            'description' => 'Task used for task tests.',
            'status' => 'todo',
            'priority' => 'medium',
        ]);
    }

    private function createTeam(User $owner): Team
    {
        return Team::create([
            'owner_id' => $owner->id,
            'name' => 'Task Team',
            'slug' => 'task-team-' . uniqid(),
            'description' => 'Team used for task tests.',
            'type' => 'permanent',
            'visibility' => 'private',
        ]);
    }
}
