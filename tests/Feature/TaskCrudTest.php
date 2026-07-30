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
